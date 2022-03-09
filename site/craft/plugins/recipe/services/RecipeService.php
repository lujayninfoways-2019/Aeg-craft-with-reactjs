<?php
namespace Craft;

/**
 * Class RecipeService
 * Namespace Craft
 */
class RecipeService extends BaseApplicationComponent  {

  /**
   * Saves Field data to DB
   * @param $recipeField
   */
  public function saveRecipeField($elementId, $fieldId, $data) {
    $recipeRecord = new Recipe_RecipeRecord();
    // Check for existing recipe row
    if ($recipeRecord->exists('`elementId`=' . $elementId)) {
      // it exists -> get it!
      $recipeRecord = $recipeRecord->find('`elementId`=' . $elementId);
    } else {
      // Set the element and field IDs
      $recipeRecord->elementId = $elementId;
      $recipeRecord->fieldId = $fieldId;
    }

    // fill record with data (use setAttributes safeOnly false to also set attributes without validation rules)
	$data = $this->flattenIdValues($data);
	$recipeRecord->setAttributes($data, false);

    // save to get an id
    if($recipeRecord->save()) {
      // instantiate ratings, if the recipe is new
      // (or if for whatever reason no ratings exist)
      // otherwise there is no need to touch the ratings
      if (!$recipeRecord->ratings) {
        // initialize ratings
        $ratings = array(
          new Recipe_RatingsRecord(),
          new Recipe_RatingsRecord(),
          new Recipe_RatingsRecord(),
          new Recipe_RatingsRecord(),
          new Recipe_RatingsRecord(),
        );
        for ($i = 0; $i < count($ratings); $i++) {
          $ratings[$i]->stars = $i+1;
          $ratings[$i]->votes = 0;
          $ratings[$i]->recipeId = $recipeRecord->id;
          $ratings[$i]->save();
        }
        $recipeRecord->ratings = $ratings;
        $recipeRecord->save();
      }

      // get the old ingredients
      $oldIngredients = $recipeRecord->ingredients;
      // update ingredients
      $i = 0;
      foreach ($data['ingredients'] as $newData) {
        // if there is an old ingredient at this position,
        // use it to overwrite it
        if ($i < count($oldIngredients)) {
          $new = $oldIngredients[$i];
        } else {
          // otherwise create a new one
          $new = new Recipe_IngredientRecord();
          $new->recipeId = $recipeRecord->id;
        }
        // update data
        $new->name = $newData['name'];
        $new->unit = $newData['unit'];
        $new->quantity = $newData['quantity'];
        $new->save();
        $i++;
      }
      // loop over possibly remaining old ingredients
      // and delete them
      for ($i;$i<count($oldIngredients);$i++) {
        $oldIngredients[$i]->delete();
      }
    } else {
      // TODO: handle errors
    }
  }

  /**
   * Get Field Values from DB
   * @param $entryId
   * @return array
   */
  public function getRecipeFieldValues($entryId) {
    $recipeRecord = new Recipe_RecipeRecord();
    // check if the field exists
    if ($entryId and $recipeRecord->exists('`elementId`=' . $entryId)) {
      // if yes, get it
      $recipeRecord = $recipeRecord->find('`elementId`=' . $entryId);
      $data = $recipeRecord->attributes;
      $data['ratings'] = array();
      foreach ($recipeRecord->ratings as $rating) {
        $data['ratings'][] = $rating->attributes;
      }
      $data['ingredients'] = array();
      foreach ($recipeRecord->ingredients as $ingredient) {
        $data['ingredients'][] = $ingredient->attributes;
      }
      return $data;
    }
    return null;
  }

  // flatten category id values to allow saving record with single ids
  public function flattenIdValues($data) {
	  foreach ($data as $name => $value) {
		  if (is_array($value) && sizeof($value) == 1 && is_numeric($value[0])) {
			  $data[$name] = $value[0];
		  }
	  }
	  return $data;
  }

  public function vote($entry, $stars) {
    // TODO: implement voting function
    return false;
  }

  /**
   * wrapper for findAllByAttributes function - get recipes by custom filters
   * @param array $criteria must be a key-value array
   * @param array $excludes a list of entry-ids to exclude
   * @param int $limit amount of recipes to fetch
   * @return mixed - a list of found entries
   */
  public function getRecipes($criteria, $excludes = NULL, $limit = -1) {
    $recipeRecord = new Recipe_RecipeRecord();
    $conditions = [];
    // check for ids to exclude
    if ($excludes) {
      $conditions[] = 'elementId NOT IN (' . join(',', $excludes) . ')';
    }
    // check for prepTime values - always interpreted as a 'max' condition
    if (array_key_exists('prepTime', $criteria) and $criteria['prepTime'] != 'all') {
      $conditions[] = 'prepTime <= ' . $criteria['prepTime'];
      unset($criteria['prepTime']);
    }
    if (sizeof($conditions) > 0) {
      $recipes = $recipeRecord->findAllByAttributes($criteria, array('condition' => join(' AND ', $conditions)));
    } else {
      $recipes = $recipeRecord->findAllByAttributes($criteria);
    }
    $entryIds = [];
    foreach ($recipes as $recipe) {
      $entryIds[] = $recipe->elementId;
    }
    // use a criteria model to get the entries ordered by postdate automatically
    $criteria = craft()->elements->getCriteria(ElementType::Entry);
    $criteria->section = 'Recipes';
    $criteria->id = $entryIds;
    $criteria->limit = $limit;
    $entries = $criteria->find();
    return $entries;
  }

  /**
   * wrapper for getRecipes function - returns only the recipes' ids
   * @param array $criteria must be a key-value array
   * @param array $excludes a list of entry-ids to exclude
   * @param int $limit amount of recipes to fetch
   * @return mixed - a list of found entries
   */
  public function getRecipeIds($criteria, $excludes = NULL, $limit = -1) {
    $recipes = $this->getRecipes($criteria, $excludes, $limit);
    $ids = array();
    foreach ($recipes as $recipe) {
      $ids[] = $recipe->id;
    }
    return $ids;
  }
}
