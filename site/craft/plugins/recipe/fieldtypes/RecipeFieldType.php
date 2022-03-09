<?php
/**
 * Recipe plugin for Craft CMS
 *
 * Recipe FieldType
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2016 nystudio107
 * @link      http://nystudio107.com
 * @package   Recipe
 * @since     1.0.0
 */

namespace Craft;

class RecipeFieldType extends BaseFieldType
{
    /**
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('Recipe');
    }

    /**
     * @return mixed
     */
    public function defineContentAttribute()
    {
        return false;
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @return string
     */
    public function getInputHtml($name, $value)
    {
        if (!$value)
            $value = new RecipeModel();
        else {
            $value->validate();
        }

        $id = craft()->templates->formatInputId($name);
        $namespacedId = craft()->templates->namespaceInputId($id);

/* -- Include our Javascript & CSS */

        craft()->templates->includeCssResource('recipe/css/fields/RecipeFieldType.css');
        craft()->templates->includeJsResource('recipe/js/fields/RecipeFieldType.js');

/* -- Variables to pass down to our field.js */

        $jsonVars = array(
            'id' => $id,
            'name' => $name,
            'namespace' => $namespacedId,
            'prefix' => craft()->templates->namespaceInputId(""),
            );

        $jsonVars = json_encode($jsonVars);
        craft()->templates->includeJs("$('#{$namespacedId}').RecipeFieldType(" . $jsonVars . ");");

/* -- Variables to pass down to our rendered template */

        $variables = array(
            'id' => $id,
            'name' => $name,
            'prefix' => craft()->templates->namespaceInputId(""),
            'element' => $this->element,
            'field' => $this->model,
            'values' => $value
        );

        // Whether any assets sources exist
        $sources = craft()->assets->findFolders();
        $variables['assetsSourceExists'] = count($sources);

        // URL to create a new assets source
        $variables['newAssetsSourceUrl'] = UrlHelper::getUrl('settings/assets/sources/new');

        // Set image ID
        $variables['imageId'] = $value->imageId;
        // Set image elements
        $variables['imageElements'] = $value->getImage() ? array($value->getImage()) : array();
        // Set element type
        $variables['imageElementType'] = craft()->elements->getElementType(ElementType::Asset);
        $variables['assetSources'] = $this->getSettings()->assetSources;

        // Get the recipe Category Group
        $variables['recipeCategoryGroup'] = craft()->categories->getGroupByHandle('recipeCategories');
        // Set the recipe category id
        $variables['recipeCategoryId'] = $value->recipeCategoryId;
        // Set category elements
        $variables['recipeCategoryElements'] = $value->getCategory()? array($value->getCategory()) : array();

        // Get the main Ingredients Category Group
        $variables['mainIngredientGroup'] = craft()->categories->getGroupByHandle('mainIngredients');
        // Set the main ingredients id
        $variables['mainIngredientId'] = $value->mainIngredientId;
        // Set category elements
        $variables['mainIngredientElements'] = $value->getMainIngredient()? array($value->getMainIngredient()) : array();

        // Set element type for Categories
        $variables['categoryElementType'] = craft()->elements->getElementType(ElementType::Category);

        // Get the Ingredient Units
        $units = array('' => '');
        foreach (craft()->globals->getSetByHandle('structure')->ingredientUnits as $unit) {
          $unitName = $unit['nameSingular'] . ($unit['nameSingular'] != $unit['namePlural'] ? ' / ' . $unit['namePlural'] : '');
          $units[$unit['symbol']] = $unitName;
        }
        $variables['units'] = $units;

        return craft()->templates->render('recipe/fields/RecipeFieldType.twig', $variables);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function prepValueFromPost($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function prepValue($value)
    {
        if ($value != null) {
            $value = RecipeModel::populateModel($value);
        } elseif(craft()->recipe->getRecipeFieldValues($this->element->id)) {
            $value = RecipeModel::populateModel(craft()->recipe->getRecipeFieldValues($this->element->id));
        }
        return $value;
    }

    public function onAfterElementSave()
    {
      // recipe model and element/entry has been validated. save data.
      $elementId = $this->element->id;
      $fieldHandle = $this->model->handle;
      $fieldId = craft()->fields->getFieldByHandle($fieldHandle)->id;
      $data = $this->element->getContent()->getAttribute($fieldHandle);
      // during running tasks (resaving elements due to section url change) data could be empty...
      if (!empty($data)) {
        craft()->recipe->saveRecipeField($elementId, $fieldId, $data);
      }
    }

    /**
     * Define our settings
     * @return array
     */
    protected function defineSettings()
        {
            return array(
                'assetSources' => AttributeType::Mixed,
            );
        }

    /**
     * Render the field settings
     */
    public function getSettingsHtml()
    {
        $assetElementType = craft()->elements->getElementType(ElementType::Asset);
        return craft()->templates->render('recipe/fields/RecipeFieldType_Settings', array(
            'assetSources'          => $this->getElementSources($assetElementType),
            'settings'              => $this->getSettings()
        ));
   }


    /**
     * Returns sources avaible to an element type.
     *
     * @access protected
     * @return mixed
     */
    protected function getElementSources($elementType)
    {
        $sources = array();

        foreach ($elementType->getSources() as $key => $source)
        {
            if (!isset($source['heading']))
            {
                $sources[] = array('label' => $source['label'], 'value' => $key);
            }
        }

        return $sources;
    }

    /**
     * @param mixed $values
     * @return bool
     */
    public function validate($values)
    {
		// TODO: is there another way to validate (without id)?
    	$model = new RecipeModel($values);
	    $model->validate();

	    $errors = $model->getAllErrors();
	    // ignore error if its id, we create a new model just for validation
		$valid = count($errors) == 1 && $model->getError('id');
	    return $valid;
    }
}
