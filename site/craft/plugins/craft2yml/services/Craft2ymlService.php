<?php
namespace Craft;

use Symfony\Component\Yaml\Yaml;

class Craft2ymlService extends BaseApplicationComponent {

	/**
	 * @param string $entryId the id of the entry to generate yml from
	 * @return string
	 */
	public function getYmlByEntryId($entryId)
	{
		$ymlContent = array();

		// globals data
		$globalsContent = array();
		$globalSets = craft()->globals->getAllSets();
		/** @var GlobalSetModel $globalSet */
		foreach ($globalSets as $globalSet) {
			$globalsContent[$globalSet->handle] = $this->getFieldData($globalSet);
		}
		$ymlContent = array_merge($ymlContent, $globalsContent);

		// entry data
		$entry = craft()->entries->getEntryById($entryId);
		$content = array('entry' => $this->getFieldData($entry));

		$ymlContent = array_merge($ymlContent, $content);

		// build yaml
		$inlineLimit = craft()->config->get('inlineLimit', 'craft2yml');
		return Yaml::dump($ymlContent, $inlineLimit, 2);
	}

	// TODO: handle more field types (date, table...)
  /**
   * @param mixed $element the element to parse
   * @param int $depth current recursion depth - this helps prohibiting an infinite loop
   * @return array the parsed data
   */
	private function getFieldData($element) {
		$layout = $element->getFieldLayout();
		$fields = $layout->getFields();

    $content = array();
    /* Add title and category attributes to articles/recipes to simplify calls */
    if ($element->getElementType() == 'Entry') {
      // set title
      $content['title'] = $element->getTitle();
      $content['uri'] = $element->uri;
      if ($element->getSection()->name == 'Recipes') {
        // set category
        $criteria = craft()->elements->getCriteria(ElementType::Category);
        $criteria->title = 'Kochen & Backen';
        $category = $criteria->first();
        if ($category) {
          $content['category'] = [
            array_merge(array(
              'id' => $category->id,
              'title' => $category->title,
              'uri' => $category->uri
            ),
              $this->getFieldData($category)
            )
          ];
        }
        // set image
        $content['image'] = $element->recipe->image->getUrl();
      }
    }
		/** @var FieldLayoutFieldModel $fieldLayout */
		foreach ($fields as $fieldLayout) {
			/** @var FieldModel $field */
			$field = $fieldLayout->getField();
			if ($field->type == 'Assets') {
				$asset = $element->getFieldValue($field->handle)->first();
				$content[$field->handle] = $asset ? $asset->getUrl() : '';
			} elseif ($field->type == 'Matrix') {
				/** @var MatrixBlockModel $matrixBlock */
				$matrixBlock = $element->getFieldValue($field->handle);
				/** @var MatrixBlockModel $item */
				foreach ($matrixBlock->getChildren() as $item) {
					$content[$field->handle][] = array_merge(array('type' => $item->getType()->handle), $this->getFieldData($item));
				}
			} elseif ($field->type == 'Neo') {
        /** @var Neo_BlockModel $neoBlock */
        $neoBlock = $element->getFieldValue($field->handle);
        /** @var Neo_BlockModel $child */
        $child = $neoBlock->getChildren()->first();
        while ($child) {
          if ($child->hasDescendants()) {
            $grandchildren = array();
            foreach ($child->getDescendants() as $grandchild) {
              $grandchildren[] = array_merge(array('type' => $grandchild->getType()->handle), $this->getFieldData($grandchild));
            }
            $content[$field->handle][] = array_merge(array('type' => $child->getType()->handle), $this->getFieldData($child), array('children' => $grandchildren));
          } else {
            $content[$field->handle][] = array_merge(array('type' => $child->getType()->handle), $this->getFieldData($child));
          }
          $child = $child->getNextSibling();
        }
      } elseif ($field->type == 'Table') {
        $tableData = $element->getContent()->getAttribute($field->handle);
        $columnSettings = $field->settings['columns'];
        $fieldValue = array();
        foreach ($tableData as $row) {
          $rowData = array();
          foreach ($row as $col => $value) {
            $rowData[$columnSettings[$col]['handle']] = $value;
          }
          $fieldValue[] = $rowData;
        }
        $content[$field->handle] = $fieldValue;
      } elseif ($field->type == 'Recipe') {
        $content[$field->handle] = $element->getFieldValue($field->handle)->getAttributesYAML();
      } else if ($field->type == 'Entries') {
        $entries = $element->getFieldValue($field->handle);
        foreach ($entries->find() as $entry) {
          $content[$field->handle][] = $this->getTeaserData($entry);
        }
      } elseif ($field->type == 'Categories') {
        $categories = $element->getFieldValue($field->handle);
        foreach ($categories->find() as $category) {
          $content[$field->handle][] = $this->getCategoryData($category);
        }
      } elseif ($field->handle == "instructions") {
			  // Exception for instructions (pages)
			  $richTextData = new RichTextData($element->getContent()->getAttribute($field->handle), craft()->templates->getTwig()->getCharset());
        $pages = array();
        foreach ($richTextData->getPages() as $page) {
          $pages[] = (string)$page;
        }
        $content[$field->handle] = $pages;
      } else {
        $content[$field->handle] = $element->getContent()->getAttribute($field->handle);
			}
		}

		return $content;
	}

  // just get the teaser data for articles, recipes and authors
  public function getTeaserData($entry) {

    $isRecipe = !empty($entry->recipe);

    $imageUrl = $isRecipe ? $entry->recipe->image->getUrl() : ($entry->image->first() ? $entry->image->first()->getUrl() : null);

    /** @var EntryModel $entry */
    $data = array(
      'title' => $entry->title,
      'uri' => $entry->uri,
      'url' => $entry->uri,
      'image' => $imageUrl,
      'imageUrl' => $imageUrl,
      'category' => array($isRecipe ? $entry->recipe->category->title : ($entry->category->first() ? $entry->category->first()->title : null) ),
      'shortIntroduction' => $isRecipe ? $entry->recipe->description : ($entry->shortIntroduction ? $entry->shortIntroduction . "" : $entry->about . ""),
      'copy' => $isRecipe ? $entry->recipe->description : ($entry->shortIntroduction ? $entry->shortIntroduction . "" : $entry->about . ""),
    );

    if ($isRecipe) {
      $data['recipe'] = $entry->getFieldValue('recipe')->getAttributesYAML();;
    }

    return $data;

  }

  // just get basic information about a category
  public function getCategoryData($category) {
    $data = array(
      'id' => $category->id,
      'title' => $category->title,
      'uri' => $category->uri
    );
    if ($category->image->first()) {
      $data['image'] = $category->image->first()->url;
    }
    return $data;
  }
}
