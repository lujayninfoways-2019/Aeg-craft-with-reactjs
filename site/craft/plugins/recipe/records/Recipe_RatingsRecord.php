<?php
namespace Craft;

class Recipe_RatingsRecord extends BaseRecord
{
  public function getTableName()
  {
    return 'recipe_ratings';
  }

  protected function defineAttributes()
  {
    return array(
      'stars' => array(AttributeType::Number, 'default' => 0),
      'votes' => array(AttributeType::Number, 'default' => 0)
    );
  }

  public function defineRelations()
  {
    return array(
      'recipe' => array(static::BELONGS_TO,
        'Recipe_RecipeRecord',
        'required' => true,
        'onDelete' => static::CASCADE
      ),
    );
  }

  public function defineIndexes()
  {
    return array(
      array('columns' => array('recipeId', 'stars'), 'unique' => true),
    );
  }
}
