<?php

namespace Craft;


class AegVariable {

	/**
	 * @param mixed $element the element to parse
	 * @param int $depth current recursion depth - this helps prohibiting an infinite loop
	 * @return array the parsed data
	 */
	public function getFieldData($element) {
		$layout = $element->getFieldLayout();
		$fields = $layout->getFields();

		$content = array();
		/* Add title and category attributes to articles/recipes to simplify calls */
		if ($element->getElementType() == 'Entry') {
			// set title
			$content['title'] = $element->getTitle();
			$content['uri'] = $element->uri;
			$content['id'] = $element->id;
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

  // just get the teaser data for articles and recipes
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
      'shortIntroduction' => strip_tags($isRecipe ? $entry->recipe->description : ($entry->shortIntroduction ? $entry->shortIntroduction . "" : $entry->about . "")),
      'copy' => strip_tags($isRecipe ? $entry->recipe->description : ($entry->shortIntroduction ? $entry->shortIntroduction . "" : $entry->about . "")),
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

  public function getAuthorArticles($author, $currentEntry) {
		/** @var EntryModel $currentEntry */

		$articles = array();

		$blockCriteria = craft()->elements->getCriteria(Neo_ElementType::NeoBlock);
		$blockCriteria->relatedTo = $author;

		$ownerIds = array();
		foreach ($blockCriteria as $block) {
			if ($block->ownerId != $currentEntry->id) {
				$ownerIds[] = $block->ownerId;
			}
		}

		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = array('articles', 'recipes');
		$criteria->id = $ownerIds;
		$criteria->limit = 2;

		foreach ($criteria->find() as $article) {
			$articles[] = $this->getTeaserData($article);
		}

		return $articles;
	}

	public function getRelatedArticles($entry) {

		$teaserData = array();

		$contentTeaser = $entry->relatedTeaser->find();
		$contentTeaserIds = $entry->relatedTeaser->ids();

    // check if we need to fetch anything at all
    $givenCount = sizeof($contentTeaser);
    if ($givenCount < 3) {
      // we need two recipes and one article
      $recipesToFetch = 2;
      $articlesToFetch = 1;
      // get information about the set teasers
      foreach ($contentTeaser as $teaser) {
        // if it's a recipe...
        if ($teaser->section == 'Recipes') {
          // ... lower the amount of recipes to fetch. otherwise...
          $recipesToFetch--;
        }
        else {
          // ... we assume it's an article
          $articlesToFetch--;
        }
      }
      // is our entry a recipe?
      $isRecipe = !empty($entry->recipe);
      if ($recipesToFetch > 0) {
        // Get Recipes with the same main ingredient
        // this only works if the entry is a recipe
        // or an article with a main ingredient
        if ($isRecipe or $entry->mainIngredient->first()) {
          // get the main ingredient depending on the entry type
          $mainIngredient = $isRecipe ? $entry->recipe->getMainIngredient() : $entry->mainIngredient->first();
          // get the related recipes
          $recipes = craft()->recipe->getRecipes(array('mainIngredientId' => $mainIngredient->id), array_merge(array($entry->id), $contentTeaserIds), $recipesToFetch);
          for ($i = 0; $i < sizeof($recipes) and $recipesToFetch > 0; $i++, $recipesToFetch--) {
            $contentTeaser[] = $recipes[$i];
            $contentTeaserIds[] = $recipes[$i]->id;
          }
        }
        // do we have enough recipes yet?
        if ($recipesToFetch > 0) {
          // this works a bit more dynamic
          $attributes = ['contentFormat', 'diet'];
          for ($i = 0; $i < sizeof($attributes) and $recipesToFetch > 0; $i++) {
            $attribute = $attributes[$i];
            if ($entry->$attribute->first()) {
              $criteria = craft()->elements->getCriteria(ElementType::Entry);
              $criteria->section = 'Recipes';
              $criteria->relatedTo = array(
                'field' => $attribute,
                'targetElement' => $entry->$attribute->first()
              );
              $criteria->id = 'and, not ' . join(', not ', array_merge(array($entry->id), $contentTeaserIds));
              $criteria->limit = $recipesToFetch;
              $recipes = $criteria->find();
              foreach ($recipes as $recipe) {
                $contentTeaser[] = $recipe;
                $contentTeaserIds[] = $recipe->id;
                $recipesToFetch--;
              }
            }
          }
        }
        // still not enough recipes?
        if ($recipesToFetch > 0) {
          // just take the latest recipes
          $criteria = craft()->elements->getCriteria(ElementType::Entry);
          $criteria->section = 'Recipes';
          $criteria->id = 'and, not ' . join(', not ', array_merge(array($entry->id), $contentTeaserIds));
          $criteria->limit = $recipesToFetch;
          $recipes = $criteria->find();
          foreach ($recipes as $recipe) {
            $contentTeaser[] = $recipe;
            $contentTeaserIds[] = $recipe->id;
          }
        }
        // we can assume now that there are two recipes in the list
        // otherwise there simply are not enough recipes at all
      }
      if ($articlesToFetch > 0) {
        // get the t+t category
        $criteria = craft()->elements->getCriteria(ElementType::Category);
        $criteria->slug = 'tipps-tricks';
        $tntCategory = $criteria->first();
        // the attributes to check
        $attributes = ['contentFormat', 'diet', ''];
        for ($i = 0; $i < sizeof($attributes) and $articlesToFetch > 0; $i++) {
          $attribute = $attributes[$i];
          // build a relatedTo-Condition - by default just the tntCategory
          $relatedToCondition = array(
            'field' => 'category',
            'targetElement' => $tntCategory
          );
          // if 'attribute' is defined, the condition gets a bit more complicated
          if ($attribute and $entry->$attribute->first()) {
            $relatedToCondition = array('and', array(
              'field' => $attribute,
              'targetElement' => $entry->$attribute->first()
            ), $relatedToCondition);
          }
          // get the entries
          $criteria = craft()->elements->getCriteria(ElementType::Entry);
          $criteria->section = 'Articles';
          $criteria->relatedTo = $relatedToCondition;
          $criteria->id = 'and, not ' . join(', not ', array_merge(array($entry->id), $contentTeaserIds));
          $criteria->limit = $articlesToFetch;
          $articles = $criteria->find();
          foreach ($articles as $article) {
            $contentTeaser[] = $article;
            $contentTeaserIds[] = $article->id;
            $articlesToFetch--;
          }
        }
        // TODO: actually we'd have to cover the case that there is no article
        // TODO: in the t+t category...
      }
    }
		// build fields
		foreach ($contentTeaser as $article) {
			$teaserData[] = $this->getTeaserData($article);
		}

		return $teaserData;
	}

	public function scaledImage($imageUrl, $width, $height) {
		$criteria = craft()->elements->getCriteria(ElementType::Asset);
		$criteria->filename = basename($imageUrl);

		/** @var AssetFileModel $asset */
		$asset = $criteria->first();

		if ($asset) {
			return $asset->getUrl(array('width' => $width, 'height' => $height));
		} else {
			return '';
		}
	}
}
