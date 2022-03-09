<?php
namespace Craft;

class Recipe_IngredientRecord extends BaseRecord
{
  public function getTableName()
  {
    return 'recipe_ingredients';
  }

  protected function defineAttributes()
  {
    return array(
      'name'                  => array(AttributeType::String),
      'unit'                  => array(AttributeType::String),
      'quantity'              => array(AttributeType::Number, 'min' => 0, 'max' => 9999999.999, 'decimals' => 3)
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
}
