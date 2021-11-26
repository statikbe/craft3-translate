# Translate for Craft CMS 3.x

Translate static string from the Craft control panel

![Codeception](https://github.com/statikbe/craft3-translate/workflows/Run%20Codeception%20unit%20tests/badge.svg)

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require statikbe/craft-translate

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Translate.


## Plugin & module translations

From version 1.3.0 onwards, translations for plugins and modules can now be saved in your site as well.

This can be done by adding the following event to your plugin file:

```php
use statikbe\translate\elements\Translate;
use statikbe\translate\events\RegisterPluginTranslationEvent;

 Event::on(
    Translate::class,
    Translate::EVENT_REGISTER_PLUGIN_TRANSLATION,
    function (RegisterPluginTranslationEvent $event) {
        $event->plugins['plugin-handle'] = \Craft::$app->getPlugins()->getPlugin('plugin-handle');
    }
);
```

Translations for the plugin will then be saved in ``site\translations\locale\plugin-handle.php``

---

Brought to you by [Statik](https://www.statik.be), heavily inspired by [boboldehampsink/translate](https://github.com/boboldehampsink/translate).
