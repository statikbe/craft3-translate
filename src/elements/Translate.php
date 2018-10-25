<?php
/**
 * Translate plugin for Craft CMS 3.x
 *
 * Translation management plugin for Craft CMS
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2017 Statik.be
 */

namespace statikbe\translate\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\web\ErrorHandler;
use statikbe\translate\elements\actions\GoogleCloudTranslate;
use statikbe\translate\elements\actions\GoogleTranslate;
use statikbe\translate\elements\actions\Yandex;
use statikbe\translate\Translate as TranslatePlugin;
use statikbe\translate\elements\db\TranslateQuery;
use craft\helpers\FileHelper;

class Translate extends Element
{
    /**
     * Status constants.
     */
    const ALL = 'all';
    const TRANSLATED = 'live';
    const PENDING = 'pending';

    public $original;
    public $translation;
    public $source;
    public $file;
    public $locale = 'en_us';
    public $field;
    public $translateStatus;

    /**
     * Return element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('translate','Translations');
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function __toString()
    {
        try{
            return $this->original;
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
        }
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * Define statuses.
     *
     * @return array
     */
    public static function statuses(): array
    {
        return [
            self::TRANSLATED => Craft::t('translate','Translated'),
            self::PENDING => Craft::t('translate','Pending'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        if ($this->original != $this->translation) {
            return static::TRANSLATED;
        }

        return static::PENDING;
    }

    public static function find(): ElementQueryInterface
    {
        return new TranslateQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $primary = Craft::$app->getSites()->getPrimarySite();
        $locale = Craft::$app->getI18n()->getLocaleById($primary->language);
        $attributes['original'] = ['label' => Craft::t('translate','Source: {region} ({language})', [
            'language'=>$primary->language,
            'region' => $locale->displayName
        ])];
        $attributes['field']     = ['label' => Craft::t('app','Translation')];

        return $attributes;
    }

    /**
     * Returns the default table attributes.
     *
     * @param string $source
     *
     * @return array
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['original', 'field'];
    }

    /**
     * Don't encode the attribute html.
     *
     * @param string           $attribute
     *
     * @return string
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        return $this->$attribute;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return [
            'original',
            'translation',
            'source',
            'file',
            'status',
            'locale'
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [];

        $sources[] = ['heading' => Craft::t('translate','Template Status')];

        $key = 'status:' . self::ALL;
        $sources[] = [
            'status'   => null,
            'key'      => $key,
            'label'    => Craft::t('translate', 'All'),
            'criteria' => [
                'source' => [
                    Craft::$app->path->getSiteTemplatesPath()
                ],
            ],
        ];

        $key = 'status:' . self::PENDING;
        $sources[] = [
            'status'   => self::PENDING,
            'key'      => $key,
            'label'    => Craft::t('translate', 'Pending'),
            'criteria' => [
                'source' => [
                    Craft::$app->path->getSiteTemplatesPath()
                ],
                'translateStatus' => self::PENDING
            ],
        ];

        $key = 'status:' . self::TRANSLATED;
        $sources[] = [
            'status'   => self::TRANSLATED,
            'key'      => $key,
            'label'    => Craft::t('translate', 'Translated'),
            'criteria' => [
                'source' => [
                    Craft::$app->path->getSiteTemplatesPath()
                ],
                'translateStatus' => self::TRANSLATED
            ],
        ];


        // Get template sources
        $templateSources = array();
        $options = [
            'recursive' => false,
            'only' => ['*.html','*.twig','*.js','*.json','*.atom','*.rss'],
            'except' => ['vendor/', 'node_modules/']
        ];
        $templates = FileHelper::findFiles(Craft::$app->path->getSiteTemplatesPath(), $options);

        foreach ($templates as $template) {
            // If matches, get template name
            $fileName = basename($template);
            // Fixes bug in ElementHelper::findSource in Linux OS
            $cleanTemplateKey = str_replace('/', '*', $template);
            // Add template source
            $templateSources['templatessources:'.$fileName] = [
                'label' => $fileName,
                'key' => 'templates:'.$cleanTemplateKey,
                'criteria' => [
                    'source' => [
                        $template
                    ],
                ],
            ];
        }

        // Folders
        $options = [
            'recursive' => false,
            'except' => ['vendor/', 'node_modules/']
        ];
        $templates = FileHelper::findDirectories(Craft::$app->path->getSiteTemplatesPath(), $options);

        foreach ($templates as $template) {
            // If matches, get template name
            $fileName = basename($template);
            // Fixes bug in ElementHelper::findSource in Linux OS
            $cleanTemplateKey = str_replace('/', '*', $template);
            // Add template source
            $templateSources['templatessources:'.$fileName] = [
                'label' => $fileName.'/',
                'key' => 'templates:'.$cleanTemplateKey,
                'criteria' => [
                    'source' => [
                        $template
                    ],
                ],
            ];
        }

        $sources[] = ['heading' => Craft::t('translate','Default')];

        $sources[] = [
            'label'    => Craft::t('translate', 'Templates'),
            'key'      => 'all-templates:',
            'criteria' => [
                'source' => [
                    Craft::$app->path->getSiteTemplatesPath()
                ]
            ],
            'nested' => $templateSources
        ];

        // @todo add hook
        return $sources;
    }

    /**
     * @inheritdoc
     */
    public static function indexHtml(ElementQueryInterface $elementQuery, array $disabledElementIds = null, array $viewState, string $sourceKey = null, string $context = null, bool $includeContainer, bool $showCheckboxes): string
    {
        // just 1 locale enabled
        if (empty($elementQuery->siteId)) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $elementQuery->siteId = $primarySite->id;
        }

        if ($elementQuery->translateStatus) {
            $elementQuery->status = $elementQuery->translateStatus;
        }

        $elements = TranslatePlugin::$app->translate->get($elementQuery);

        $variables = [
            'viewMode' => $viewState['mode'],
            'context' => $context,
            'disabledElementIds' => $disabledElementIds,
            'attributes' => Craft::$app->getElementIndexes()->getTableAttributes(static::class, $sourceKey),
            'elements' => $elements,
            'showCheckboxes' => $showCheckboxes
        ];

        // Better UI
        Craft::$app->view->registerJs("$('table.fullwidth thead th').css('width', '50%');");
        Craft::$app->view->registerJs("$('.buttons.hidden').removeClass('hidden');");

        $template = '_elements/'.$viewState['mode'].'view/'.($includeContainer ? 'container' : 'elements');

        return Craft::$app->view->renderTemplate($template, $variables);
    }

    /**
     * @return null|string
     */
    public function getLocale()
    {
        $site = Craft::$app->getSites()->getSiteById($this->siteId);

        return $site->language;
    }
}
