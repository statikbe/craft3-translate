<?php
/**
 * Translate plugin for Craft CMS 3.x
 *
 * Translation management plugin for Craft CMS
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2017 Statik.be
 */

namespace statikbe\translate\services;

use Craft;
use craft\base\Component;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ElementHelper;
use craft\helpers\FileHelper;
use Exception;
use statikbe\translate\elements\db\TranslateQuery;
use statikbe\translate\elements\Translate as TranslateElement;
use Throwable;

class Translate extends Component
{
    /**
     * Translate REGEX.
     * @credits to boboldehampsink
     * @var []
     */
    public array $_expressions = array(
        // Regex for Craft::t('category', '..')
        'php' => array(
            // Single quotes
            '/Craft::(t|translate)\(.*?\'(.*?)\'.*?\,.*?\'(.*?)\'.*?\)/',
            // Double quotes
            '/Craft::(t|translate)\(.*?"(.*?)".*?\,.*?"(.*?)".*?\)/',
        ),

        // Regex for |t('category')
        'twig' => array(
            // Single quotes
            "/'([^']+)'\ *\|\ *(t|translate)/mu",
            // Double quotes
            '/"([^"]+)"\ *\|\ *(t|translate)/mu',
        ),

        // Regex for Craft.t('category', '..')
        'js' => array(
            // Single quotes
            '/Craft\.(t|translate)\(.*?\'(.*?)\'.*?\,.*?\'(.*?)\'.*?\)/',
            // Double quotes
            '/Craft\.(t|translate)\(.*?"(.*?)".*?\,.*?"(.*?)".*?\)/',
        )
    );


    /**
     * Initialize service.
     *
     * @codeCoverageIgnore
     */
    public function init(): void
    {
        parent::init();

        $this->_expressions['html'] = $this->_expressions['twig'];
        $this->_expressions['json'] = $this->_expressions['twig'];
        $this->_expressions['atom'] = $this->_expressions['twig'];
        $this->_expressions['rss'] = $this->_expressions['twig'];
    }

    /**
     * Set translations.
     *
     * @param string $locale
     * @param array $translations
     * @param string|null $translationPath
     *
     * @return bool
     * @throws \Exception if unable to write to file
     */
    public function set(string $locale, array $translations, string $translationPath = null): bool
    {
        // Determine locale's translation destination file
        $file = $translationPath ?? $this->getSitePath($locale);

        // Get current translation
        if ($current = @include($file)) {
            $translations = array_merge($current, $translations);
        }

        // Prepare php file
        $php = "<?php\r\n\r\nreturn ";

        // Get translations as php
        $php .= var_export($translations, true);

        // End php file
        $php .= ';';

        // Convert double space to tab (as in Craft's own translation files)
        $php = str_replace("  '", "\t'", $php);

        // Save code to file
        try {
            FileHelper::writeToFile($file, $php);

        } catch (Throwable $e) {
            throw new Exception(Craft::t('translate', 'Something went wrong while saving your translations: ' . $e->getMessage()));
        }

        return true;
    }

    /**
     * Get translations by Element Query.
     *
     * @param ElementQueryInterface $query
     *
     * @param string $category
     *
     * @return array
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function get(TranslateQuery $query, string $category = 'site'): array
    {
        sleep(2);

        if (!is_array($query->source)) {
            $query->source = [$query->source];
        }

        $translations = [];

        // Loop through paths

        foreach ($query->source as $path) {
            if($query->pluginHandle) {
                $category = $query->pluginHandle;
            }
            // Check if this is a folder or a file
            $isDir = is_dir($path);

            if ($isDir) {
                $options = [
                    'recursive' => true,
                    'only' => ['*.php', '*.html', '*.twig', '*.js', '*.json', '*.atom', '*.rss'],
                    'except' => ['vendor/', 'node_modules/']
                ];

                $files = FileHelper::findFiles($path, $options);

                // Loop through files and find translate occurences
                foreach ($files as $file) {

                    // Parse file
                    $elements = $this->_processFile($path, $file, $query, $category);

                    // Collect in array
                    $translations = array_merge($translations, $elements);
                }
            } elseif (file_exists($path)) {

                // Parse file
                $elements = $this->_processFile($path, $path, $query, $category);

                // Collect in array
                $translations = array_merge($translations, $elements);
            }
        }
        return $translations;
    }

    /**
     * Apply regex search into file
     *
     * @param string $path
     * @param string $file
     * @param ElementQueryInterface $query
     * @param string $category
     *
     * @return array
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function _processFile(string $path, string $file, ElementQueryInterface $query, string $category): array
    {
        $translations = array();
        $contents = file_get_contents($file);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        // Process the file
        foreach ($this->_expressions[$extension] as $regex) {
            // Do it!
            $matches = $this->parseString($regex, $contents);
            if ($matches) {
                $pos = 1;
                // Js and php files goes to 3
                if ($extension === 'js' || $extension === 'php') {
                    $pos = 3;
                }
                foreach ($matches[$pos] as $original) {
                    // Apply the Craft Translate
                    $site = Craft::$app->getSites()->getSiteById($query->siteId);
                    //changed $site->language to site handle
                    $translation = Craft::t($category, $original, [], $site->language);

                    $view = Craft::$app->getView();
                    $slug = ElementHelper::generateSlug($original);

                    $field = $view->renderTemplate('_includes/forms/text', [
                        'id' => $slug,
                        'name' => 'translation[' . $original . ']',
                        'value' => $translation,
                        'placeholder' => $translation,
                    ]);

                    // Let's create our translate element with all the info
                    $element = new TranslateElement([
                        'id' => $slug,
                        'original' => $original,
                        'translation' => $translation,
                        'source' => $path,
                        'file' => $file,
                        'siteId' => $query->siteId,
                        'field' => $field,
                    ]);


                    // Continue when Searching
                    if ($query->search && !stristr($element->original, $query->search) && !stristr($element->translation, $query->search)) {
                        continue;
                    }
                    // Continue when filter by status
                    if ($query->status && $query->status != $element->getStatus()) {
                        continue;
                    }
                    // add actions occurrences
                    if ($query->id) {
                        foreach ($query->id as $id) {
                            if ($element->id == $id) {
                                $translations[$element->original] = $element;
                            }
                        }
                    } else {
                        $translations[$element->original] = $element;
                    }
                }
            }
        }

        return $translations;
    }

    public function parseString($expression, $string)
    {
        $string = preg_replace("/\r?\n|\r|\n/", " ", $string);
        $string = preg_replace('!\s+!', ' ', $string);
        preg_match_all($expression, $string, $matches);
        return $matches;
    }

    /**
     * @param $locale
     *
     * @return string
     * @throws \yii\base\Exception
     */
    public function getSitePath($locale): string
    {
        $sitePath = Craft::$app->getPath()->getSiteTranslationsPath();
        return $sitePath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'site.php';
    }

}
