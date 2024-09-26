<?php

namespace thewebbakery\productattributefields;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use thewebbakery\productattributefields\fields\ProductAttributesField;
use thewebbakery\productattributefields\fields\ProductAttributesFieldJson;
use thewebbakery\productattributefields\fields\ProductAttributeValueField;
use thewebbakery\productattributefields\models\Settings;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Product Attribute Fields plugin
 *
 * @method static ProductAttributes getInstance()
 * @method Settings getSettings()
 * @author The Web Bakery <support@thewebbakery.nl>
 * @copyright The Web Bakery
 * @license https://craftcms.github.io/license/ Craft License
 */
class ProductAttributes extends Plugin
{

  public static $plugin;

  public string $schemaVersion = '1.0.0';
  public bool $hasCpSettings = true;

  public static function config(): array
  {
    return [
      'components' => [
        // Define component configs here...
      ],
    ];
  }

  public function init(): void
  {
    parent::init();

    // Register the custom field using the event listener
    Event::on(
      Fields::class,
      Fields::EVENT_REGISTER_FIELD_TYPES,
      function (RegisterComponentTypesEvent $event) {
        $event->types[] = ProductAttributesField::class;
        $event->types[] = ProductAttributeValueField::class;
        $event->types[] = ProductAttributesFieldJson::class;
      }
    );

    // Register the controller route for fetching entry types
    Craft::$app->getUrlManager()->addRules([
      'actions/product-attributes/entry/get-entry-types' => 'product-attributes/entry/get-entry-types',
      'actions/product-attributes/entry/get-entries' => 'product-attributes/entry/get-entries',
    ], false);
  }

  /**
   * @throws InvalidConfigException
   */
  protected function createSettingsModel(): ?Model
  {
    return Craft::createObject(Settings::class);
  }

  /**
   * @throws SyntaxError
   * @throws RuntimeError
   * @throws Exception
   * @throws LoaderError
   */
  protected function settingsHtml(): ?string
  {
    return Craft::$app->view->renderTemplate('product-attribute-fields/_settings.twig', [
      'plugin' => $this,
      'settings' => $this->getSettings(),
    ]);
  }
}
