<?php
namespace Craft;


class RecipeController extends BaseController {

  protected $allowAnonymous = array('actionGetRecipeCount');

  // /actions/recipe/getRecipeCount
  public function actionGetRecipeCount() {
    $this->requireAjaxRequest();
    $params = craft()->request->getRestParams();
    // exclude 'diets'
    if (array_key_exists('diets', $params)) {
      $diets = $params['diets'];
      unset($params['diets']);
    }

    // exclude 'prepTime' if all (also see templates/search/recipe-results.twig !)
    if (array_key_exists('time', $params) and $params['time'] == 'all') {
      unset($params['time']);
    }

    // map params to db-names
    $mapping = array(
      'ingredients' => 'mainIngredientId',
      'categories' => 'recipeCategoryId',
      'difficulties' => 'skill',
      'time' => 'prepTime',
    );
    foreach ($mapping as $old => $new) {
      if (array_key_exists($old, $params)) {
        $params[$new] = $params[$old];
        unset($params[$old]);
      }
    }

    $recipeIds = craft()->recipe->getRecipeIds($params);

    if (!empty($diets)) {
      $criteria = craft()->elements->getCriteria(ElementType::Entry);
      $criteria->relatedTo = array(
        'field' => 'diet',
        'targetElement' => $diets
      );
      $criteria->id = $recipeIds;
      $count = $criteria->count();
    } else {
      $count = sizeof($recipeIds);
    }

    // TODO: Maybe error handling
    $this->returnJson(array('count' => $count));
  }
}
