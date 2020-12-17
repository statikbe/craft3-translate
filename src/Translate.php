<?php
/**
 * Translate plugin for Craft CMS 3.x
 *
 * Translate your website templates and plugins into multiple languages. Bulk translation with Google Translate or Yandex.
 *
 * @link      https://www.statik.be
 * @copyright Copyright (c) 2017 Statik.be
 */

namespace statikbe\translate;

use craft\base\Plugin;
use statikbe\translate\services\Translate as TranslateService;


/**
 * Class Translate
 * @package statikbe\translate
 * @property TranslateService translate
 */
class Translate extends Plugin
{
    public $hasCpSection = true;

    public $hasCpSettings = false;

    public function init()
    {
        parent::init();

        $this->setComponents([
            'translate' => TranslateService::class,
        ]);

    }
}

