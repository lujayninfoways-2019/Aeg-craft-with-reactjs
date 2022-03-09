<?php
namespace Craft;

class Recipe_RecipeRecord extends BaseRecord
{
  public function getTableName()
  {
    return 'recipe_recipe';
  }

  protected function defineAttributes()
  {
    return array(
      'recipeCategoryId'      => array(AttributeType::Number, 'required' => true),
      'mainIngredientId'      => array(AttributeType::Number, 'required' => true),
      'imageId'               => array(AttributeType::Number, 'required' => true),
      'name'                  => array(AttributeType::String, 'required' => true),
      'description'           => array(AttributeType::String, 'column' => ColumnType::Text, 'required' => true),
      'prepTime'              => array(AttributeType::Number, 'required' => true),
      'skill'                 => array(AttributeType::Enum, 'required' => true, 'values' => "beginner,intermediate,advanced"),
      'serves'                => array(AttributeType::Number, 'required' => true),
      'copy'                  => array(AttributeType::String, 'column' => ColumnType::Text),
      'directions'            => array(AttributeType::String, 'column' => ColumnType::Text, 'required' => true),

      // TBD: Nutritional Information
      'servingSize'           => array(AttributeType::String),
      'calories'              => array(AttributeType::Number),
      'carbohydrateContent'   => array(AttributeType::Number),
      'cholesterolContent'    => array(AttributeType::Number),
      'fatContent'            => array(AttributeType::Number),
      'fiberContent'          => array(AttributeType::Number),
      'proteinContent'        => array(AttributeType::Number),
      'saturatedFatContent'   => array(AttributeType::Number),
      'sodiumContent'         => array(AttributeType::Number),
      'sugarContent'          => array(AttributeType::Number),
      'transFatContent'       => array(AttributeType::Number),
      'unsaturatedFatContent' => array(AttributeType::Number),
    );
  }

  public function defineRelations()
  {
    return array(
      'element' => array(static::BELONGS_TO,
        'ElementRecord',
        'required' => true,
        'unique' => true,
        'onDelete' => static::CASCADE),
      'field' => array(static::BELONGS_TO,
        'FieldRecord',
        'required' => true,
        'onDelete' => static::CASCADE),
      'recipeCategory' => array(static::HAS_ONE,
        'CategoryRecord',
        'recipe_recipe.recipeCategoryId',
        'required' => true,
        'onDelete' => static::SET_DEFAULT),
      'mainIngredient' => array(static::HAS_ONE,
        'CategoryRecord',
        'recipe_recipe.mainIngredientId',
        'required' => true,
        'onDelete' => static::SET_DEFAULT),
      'image' => array(static::HAS_ONE,
        'AssetFileRecord',
        'recipe_recipe.imageId',
        'required' => true,
        'onDelete' => static::SET_DEFAULT),
      'ingredients' => array(static::HAS_MANY,
        'Recipe_IngredientRecord',
        'recipeId',
        'required' => true,
        'onDelete' => static::SET_DEFAULT),
      'ratings'     => array(static::HAS_MANY,
        'Recipe_RatingsRecord',
        'recipeId',
        'required' => true,
        'onDelete' => static::SET_DEFAULT),
    );
  }
}
