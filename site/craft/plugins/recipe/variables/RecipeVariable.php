<?php

namespace Craft;
class RecipeVariable {
  /**
   * Wrapper function for 'getRecipes' in RecipeService
   * @param mixed $criteria
   * @param array $excludes
   * @param int $limit
   * @return mixed
   */
  public function getRecipes($criteria, $excludes = NULL, $limit = -1) {
    return craft()->recipe->getRecipes($criteria, $excludes, $limit);
  }

  /**
   * Wrapper function for 'getRecipeIds' in RecipeService
   * @param mixed $criteria
   * @param array $excludes
   * @param int $limit
   * @return mixed
   */
  public function getRecipeIds($criteria, $excludes = NULL, $limit = -1) {
    return craft()->recipe->getRecipeIds($criteria, $excludes, $limit);
  }
}
