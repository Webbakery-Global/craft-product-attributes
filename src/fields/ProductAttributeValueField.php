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
use SpomkyLabs\Pki\X501\ASN1\AttributeType;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\db\Schema;
use craft\elements\Entry;
use craft\helpers\Json;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;

class ProductAttributeValueField extends Field implements SortableFieldInterface, InlineEditableFieldInterface
{
  /**
   * @inheritdoc
   */
  public static function displayName(): string
  {
    return Craft::t('product-attribute-fields', 'Product Eigenschap');
  }

  public function defineContentAttribute()
  {
    return Schema::TYPE_TEXT;
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

    if (!is_null($element->draftId)) {
      return 'Sla eerst je product op';
    }

    $product = Product::find()->id($element->ownerId)->one();

    $productAttributes = $product->productAttributes->all() ?: [];

    $dropdowns = [];

    foreach($productAttributes as $productAttribute) {

      $slug = $productAttribute->slug; // Replace with the actual property if different

      if ($slug === 'type-systeem') {
        $slug = 'msSystem';
      }

      // Query entries based on the attribute slug
      $entries = Entry::find()
        ->section($slug) // Use the slug as the section handle
        ->type('eigenschapTitle') // Replace with the actual entry type if different
        ->all();



      $dropdowns[] = [
        'title' => $productAttribute->title,
        'slug' => $slug,
        'entries' => $entries,
      ];
    }

    $value = is_array($value) ? $value : Json::decodeIfJson($value);

    // Render the field template with the current value
    return Craft::$app->getView()->renderTemplate(
      'product-attribute-fields/_layouts/product-attribute-value/index',
      [
        'name' => $this->handle,
        'value' => $value,
        'dropdowns' => $dropdowns,
      ]
    );
  }

  // Define how the field's value should be stored in the database
  public function normalizeValue($value, ElementInterface $element = null): mixed
  {
    // If the value is an empty string, set it as an empty array instead
    if (empty($value)) {
      return [];
    }

    // Ensure that any serialized JSON data is decoded back to an array
    return is_array($value) ? $value : Json::decodeIfJson($value);
  }

  public function serializeValue($value, ElementInterface $element = null): mixed
  {
    // Ensure we are encoding only non-empty arrays
    return !empty($value) ? Json::encode($value) : null;
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
