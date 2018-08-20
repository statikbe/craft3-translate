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

use craft\base\Component;

class App extends Component
{
    /**
     * @var Translate
     */
    public $translate;
    
    public function init()
    {
        $this->translate = new Translate();
    }
}