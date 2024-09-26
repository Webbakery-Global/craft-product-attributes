<?php

namespace thewebbakery\productattributefields\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\InlineEditableFieldInterface;
use craft\base\SortableFieldInterface;
use craft\elements\Category;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\db\Schema;
use craft\elements\Entry;
use craft\helpers\Json;

class ProductAttributesField extends Field implements SortableFieldInterface, InlineEditableFieldInterface
{
  /**
   * @inheritdoc
   */
  public static function displayName(): string
  {
    return Craft::t('product-attribute-fields', 'Product Attributes');
  }

  // This defines how the field is stored in the database

  /**
   * @inheritdoc
   */
  public static function icon(): string
  {
    return 'i-cursor';
  }

  /**
   * @inheritdoc
   */
  public static function phpType(): string
  {
    return 'mixed';
  }

  /**
   * @inheritdoc
   */
  public static function dbType(): array|string|null
  {
    return Schema::TYPE_TEXT;  // Storing category ID as an integer
  }

  /**
   * @throws SyntaxError
   * @throws RuntimeError
   * @throws Exception
   * @throws LoaderError
   */
  public function getInputHtml($value, ElementInterface $element = null): string
  {
    // Render the field template with the current value
    return Craft::$app->getView()->renderTemplate(
      'product-attribute-fields/_layouts/product-attributes/index',
      [
        'name' => $this->handle,
        'value' => $value,
      ]
    );
  }

  // Define how the field's value should be stored in the database
  public function normalizeValue($value, ElementInterface $element = null): mixed
  {
    return is_array($value) ? $value : Json::decodeIfJson($value);
  }

  // Define how the field's value should be saved to the database
  public function serializeValue($value, ElementInterface $element = null): mixed
  {
    return Json::encode($value);
  }

  // If you want to enforce the value property, you can add a getter
  public function getValue()
  {
    return $this->value;
  }


  /**
   * @inheritdoc
   */
  public function getElementValidationRules(): array
  {
    return [];
  }

  /**
   * @inheritdoc
   */
  protected function searchKeywords(mixed $value, ElementInterface $element): string
  {
    return StringHelper::toString($value, ' ');
  }
}
