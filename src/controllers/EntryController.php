<?php

namespace thewebbakery\productattributefields\controllers;

use Craft;
use craft\web\Controller;
use craft\elements\Entry;
use craft\services\Sections;
use Illuminate\Support\Collection;
use yii\web\Response;

class EntryController extends Controller
{

  public $enableCsrfValidation = false;
  protected array|int|bool $allowAnonymous = false;

  public function actionGetEntryTypes(): Response
  {
    // Correctly access the sections service
    $sections = Craft::$app->sections->getAllEntryTypes();
    $entryTypeOptions = [];

    foreach ($sections as $section) {
      $entryTypes = Craft::$app->sections->getEntryTypesByHandle($section);

      foreach ($entryTypes as $entryType) {
        $entryTypeOptions[] = [
          'label' => $entryType->name,
          'value' => $entryType->id
        ];
      }
    }

    // Return the entry type options as JSON
    return $this->asJson(['entryTypeOptions' => $entryTypeOptions]);
  }

  public function ids(): Collection
  {
    return $this->map(fn($entryType) => $entryType->id);
  }

  public function actionGetEntries(): Response
  {
    $entryTypeId = Craft::$app->request->getParam('entryTypeId');

    // Fetch entries of the selected entry type
    $entries = Entry::find()
      ->typeId($entryTypeId) // Filter by entry type
      ->all();

    $data = [];
    foreach ($entries as $entry) {
      $data[] = [
        'id' => $entry->id,
        'title' => $entry->title,
      ];
    }

    return $this->asJson(['entries' => $data]);
  }
}
