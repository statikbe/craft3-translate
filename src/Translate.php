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

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use craft\events\DefineComponentsEvent;
use yii\base\Event;


class Translate extends Plugin
{
    /**
     * Enable use of Translate::$app-> in place of Craft::$app->
     *
     * @var [type]
     */
    public static $app;

    public $hasCpSection = true;

    public $hasCpSettings = false;

    public function init()
    {
        parent::init();
        self::$app = $this->get('app');

    }
}

