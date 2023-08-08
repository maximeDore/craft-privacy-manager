<?php

/**
 * Troisième Google Tag Manager plugin for Craft CMS 4.x
 *
 * Inject Google Tag manager script and noscript into the head and body (respectively) after the user has given consent
 *
 * @link      https://www.troisieme.ca
 * @copyright Copyright (c) 2023 Troisième
 */

namespace troisieme\privacymanager;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\twig\variables\Cp;
use troisieme\privacymanager\models\Settings;
use troisieme\privacymanager\variables\PrivacyManagerVariable;
use yii\base\Event;


/**
 * Class PrivacyManager
 *
 * @author    Troisième
 * @package   PrivacyManager
 * @since     1.0.0
 *
 */
class PrivacyManager extends Plugin
{
   // Static Properties
   // =========================================================================

   /**
    * @var PrivacyManager
    */
   public static $plugin;

   // Public Properties
   // =========================================================================

   /**
    * @var string
    */
   public string $schemaVersion = "1.0.0";

   /**
    * @var bool
    */
   public bool $hasCpSettings = true;

   /**
    * @var bool
    */
   public bool $hasCpSection = true;

   // Public Methods
   // =========================================================================

   /**
    * @inheritdoc
    */
   public function __construct($id, $parent = null, array $config = [])
   {

      // Set this as the global instance of this plugin class
      static::setInstance($this);

      parent::__construct($id, $parent, $config);
   }

   /**
    * @inheritdoc
    */
   public function init(): void
   {
      parent::init();
      self::$plugin = $this;

      Event::on(
         CraftVariable::class,
         CraftVariable::EVENT_INIT,
         static function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('privacyManager', PrivacyManagerVariable::class);
         }
      );

      Craft::info(
         Craft::t(
            'privacy-manager',
            '{name} plugin loaded',
            ['name' => $this->name]
         ),
         __METHOD__
      );
   }

   // Protected Methods
   // =========================================================================

   /**
    * @inheritdoc
    */
   protected function createSettingsModel(): ?Model
   {
      return new Settings();
   }

   /**
    * @inheritdoc
    */
   protected function settingsHtml(): string
   {
      // Get and pre-validate the settings
      $settings = $this->getSettings();
      $settings->validate();

      // Get the settings that are being defined by the config file
      $overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

      return Craft::$app->view->renderTemplate(
         'privacy-manager/settings',
         [
            'settings'  => $settings,
            'overrides' => array_keys($overrides),
         ]
      );
   }
}
