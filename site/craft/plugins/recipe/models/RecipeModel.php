<?php
/**
 * Recipe plugin for Craft CMS
 *
 * Recipe Model
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2016 nystudio107
 * @link      http://nystudio107.com
 * @package   Recipe
 * @since     1.0.0
 */

namespace Craft;

class RecipeModel extends BaseModel
{
    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'                    => array(AttributeType::Number, 'required' => true),
            'name'                  => array(AttributeType::String, 'required' => true),
            'recipeCategoryId'      => array(AttributeType::Mixed, 'required' => true), // use Mixed type only for validation (is an array)
            'imageId'               => array(AttributeType::Mixed, 'required' => true), // use Mixed type only for validation (is an array)
            'description'           => array(AttributeType::String, 'required' => true),
            'prepTime'              => array(AttributeType::Number, 'required' => true, 'min' => 0, 'default' => 0),
            'cookTime'              => array(AttributeType::Number, 'min' => 0),
            'totalTime'             => array(AttributeType::Number, 'min' => 0),
            'skill'                 => array(AttributeType::Enum, 'required' => true, 'values' => "beginner,intermediate,advanced", 'default' => 'intermediate'),
            'mainIngredientId'      => array(AttributeType::Mixed, 'required' => true), // use Mixed type only for validation (is an array)
            'serves'                => array(AttributeType::Number, 'required' => true, 'min' => 1, 'default' => 1),
            'ingredients'           => array(AttributeType::Mixed, 'required' => true),
            'copy'                  => array(AttributeType::String),
            'directions'            => array(AttributeType::String, 'required' => true),
            'ratings'               => array(AttributeType::Mixed, 'default' => array(array('stars' => 5, 'votes' => 0)
                                                                                    , array('stars' => 4, 'votes' => 0)
                                                                                    , array('stars' => 3, 'votes' => 0)
                                                                                    , array('stars' => 2, 'votes' => 0)
                                                                                    , array('stars' => 1, 'votes' => 0))),

            // TBD: Nutritional Information
            'servingSize'           => array(AttributeType::String),
            'calories'              => array(AttributeType::Number, 'min' => 0),
            'carbohydrateContent'   => array(AttributeType::Number, 'min' => 0),
            'cholesterolContent'    => array(AttributeType::Number, 'min' => 0),
            'fatContent'            => array(AttributeType::Number, 'min' => 0),
            'fiberContent'          => array(AttributeType::Number, 'min' => 0),
            'proteinContent'        => array(AttributeType::Number, 'min' => 0),
            'saturatedFatContent'   => array(AttributeType::Number, 'min' => 0),
            'sodiumContent'         => array(AttributeType::Number, 'min' => 0),
            'sugarContent'          => array(AttributeType::Number, 'min' => 0),
            'transFatContent'       => array(AttributeType::Number, 'min' => 0),
            'unsaturatedFatContent' => array(AttributeType::Number, 'min' => 0),
        ));
    }

/* -- Accessors ------------------------------------------------------------ */

    /**
     * @return string the URL to the image
     */
    public function getImageUrl()
    {
        $result = "";
        if (isset($this->imageId))
        {
            $image = craft()->assets->getFileById($this->imageId);
            if ($image)
                $result = $image->url;
        }
        return $result;
    }

    /**
     * @return string the URL to the image
     */
    public function getImage()
    {
        $result = null;
        if (isset($this->imageId))
        {
            $image = craft()->assets->getFileById($this->imageId);
            if ($image)
                $result = $image;
        }
        return $result;
    }

    /**
     * @return the recipe's category
     */
    public function getCategory()
    {
        $result = null;
        if (isset($this->recipeCategoryId))
        {
            $category = craft()->categories->getCategoryById($this->recipeCategoryId);
            if ($category)
                $result = $category;
        }
        return $result;
    }

    /**
     * @return the main ingredient
     */
    public function getMainIngredient()
    {
        $result = null;
        if (isset($this->mainIngredientId))
        {
            $ingredient = craft()->categories->getCategoryById($this->mainIngredientId);
            if ($ingredient)
                $result = $ingredient;
        }
        return $result;
    }

	/**
	 * @param int $serving
	 * @param bool $returnStrings
	 * @return array of objects or strings for the ingredients
	 */
    public function getIngredients($serving=0, $returnStrings = false)
    {
        $result = array();
        foreach ($this->ingredients as $row)
        {
            $ingredient = array();
            foreach ($row as $key => $value) {
                if ($key == 'quantity') {
                    $multiplier = 1;
                    if ($serving > 0)
                        $multiplier = $serving / $this->serves;
                    $quantity = $row['quantity'] * $multiplier;
                    $ingredient['quantity'] = $quantity;
                } else {
                    $ingredient[$key] = $row[$key];
                }
            }
            if ($returnStrings) {
                $result[] = "{$ingredient['quantity']}{$ingredient['unit']} {$ingredient['name']}";
            } else {
	            $result[] = $ingredient;
            }
        }
        return $result;
    }

    /**
     * @return array of strings for the directions
     */
    public function getDirections()
    {
      // TODO: save directions like craft saves richtext fields, to be able to use this:
/*        if (gettype($this->directions) == 'string') {
            $rtData = new RichTextData($this->directions, craft()->templates->getTwig()->getCharset());
            return $rtData->getPages();
        }*/

        $result = array();
        if (gettype($this->directions) == 'string') {
            foreach (preg_split("/<hr[^>]*class=\"redactor_pagebreak\"[^>]*>/", $this->directions) as $direction) {
                array_push($result, trim($direction));
            }
        }
        return $result;
    }

    /**
     * @return string the aggregate rating for this recipe
     */
    public function getAggregateRating()
    {
        $result = 0;
        $total = 0;
        if (isset($this->ratings) && !empty($this->ratings))
        {
            foreach ($this->ratings as $row)
            {
				//$result += $row['rating'];
                //$total++;
	            // we use custom rating...
                $result += $row['votes'] * $row['stars'];
                $total += $row['votes'];
            }
            if ($total == 0)
            	$result = "";
            else
	            $result = $result / $total;
        }
        else
            $result = "";
        return $result;
    }

    /**
     * @return string the number of ratings
     */
    public function getRatingsCount()
    {
        $total = 0;
        if (isset($this->ratings) && !empty($this->ratings))
        {
            foreach ($this->ratings as $row)
            {
                //$total++;
	            $total += $row['votes'];
            }
        }
        return $total;
    }

    /**
     * @return string the rendered HTML JSON-LD microdata
     */
    public function renderRecipeJSONLD()
    {
        if (craft()->plugins->getPlugin('Seomatic'))
        {
            $metaVars = craft()->seomatic->getGlobals("", craft()->language);
            $recipeJSONLD = array(
                "type" => "Recipe",
                "name" => $this->name,
                "image" => $this->getImageUrl(),
                "description" => $this->description,
                "recipeYield" => $this->serves,
                "recipeIngredient" => $this->getIngredients(0, true),
                "recipeInstructions" => $this->getDirections(),
                );
            $recipeJSONLD = array_filter($recipeJSONLD);

            $nutrition = array(
                "type"                  => "NutritionInformation",
                'servingSize'           => $this->servingSize,
                'calories'              => $this->calories,
                'carbohydrateContent'   => $this->carbohydrateContent,
                'cholesterolContent'    => $this->cholesterolContent,
                'fatContent'            => $this->fatContent,
                'fiberContent'          => $this->fiberContent,
                'proteinContent'        => $this->proteinContent,
                'saturatedFatContent'   => $this->saturatedFatContent,
                'sodiumContent'         => $this->sodiumContent,
                'sugarContent'          => $this->sugarContent,
                'transFatContent'       => $this->transFatContent,
                'unsaturatedFatContent' => $this->unsaturatedFatContent,
            );
            $nutrition = array_filter($nutrition);
            $recipeJSONLD['nutrition'] = $nutrition;
            if (count($recipeJSONLD['nutrition']) == 1)
                unset($recipeJSONLD['nutrition']);

            $aggregateRating = $this->getAggregateRating();
            if ($aggregateRating)
            {
                $aggregateRatings = array(
                    "type"          => "AggregateRating",
                    'ratingCount'   => $this->getRatingsCount(),
                    'bestRating'    => '5',
                    'worstRating'   => '1',
                    'ratingValue'   => $aggregateRating,
                );
                $aggregateRatings = array_filter($aggregateRatings);
                $recipeJSONLD['aggregateRating'] = $aggregateRatings;

	            // TODO: we don't use seperate reviews with author and texts, do we?
//                $reviews = array();
//                foreach ($this->ratings as $rating)
//                {
//                	$review = array(
//                        "type"          => "Review",
//                        'author'        => $rating['author'],
//                        'name'          => $this->name . Craft::t(" Review"),
//                        'description'   => $rating['review'],
//                        'reviewRating'  => array(
//                            "type"          => "Rating",
//                            'bestRating'    => '5',
//                            'worstRating'   => '1',
//                            'ratingValue'   => $rating['rating'],
//                            ),
//                        );
//                array_push($reviews, $review);
//                }
//                $reviews = array_filter($reviews);
//                $recipeJSONLD['review'] = $reviews;
            }

            if ($this->prepTime)
                $recipeJSONLD['prepTime'] = "PT" . $this->prepTime . "M";
            if ($this->cookTime)
                $recipeJSONLD['cookTime'] = "PT" . $this->cookTime . "M";
            if ($this->totalTime)
                $recipeJSONLD['totalTime'] = "PT" . $this->totalTime . "M";

            $recipeJSONLD['author'] = $metaVars['seomaticIdentity'];

            craft()->seomatic->sanitizeArray($recipeJSONLD);
            $result = craft()->seomatic->renderJSONLD($recipeJSONLD, false);
        }
        else
            $result = "<!-- SEOmatic plugin must be installed to render the JSON-LD microdata -->";

        return TemplateHelper::getRaw($result);
    }

    public function getAttributesYAML($names = NULL, $flattenValues = FALSE) {
        // exclude unnecessary values
        $excludes = array('dateCreated', 'dateUpdated', 'uid', 'id', 'elementId', 'fieldId', 'recipeId', 'servingSize', 'calories', 'carbohydrateContent', 'cholesterolContent', 'fatContent', 'fiberContent', 'proteinContent', 'saturatedFatContent', 'sodiumContent', 'sugarContent', 'transFatContent', 'unsaturatedFatContent');
        $categories = array('recipeCategoryId', 'mainIngredientId');
        $assets = array('imageId');
        // check if the field exists
        $data = array();
        foreach ($this->attributes as $handle => $attribute) {
            if (in_array($handle, $categories)) {
                $cat = craft()->categories->getCategoryById($attribute);
                $data[str_replace('Id', '', $handle)] = array('title' => $cat->getTitle(), 'uri' => $cat->getUrl());
            } elseif (in_array($handle, $assets)) {
                $img = craft()->assets->getFileById($attribute);
                $data[str_replace('Id', '', $handle)] = array('caption' => $img->caption, 'uri' => $img->getUrl());
            } elseif ($handle == 'directions') {
                $data[$handle] = $this->getDirections();
            } elseif (!in_array($handle, $excludes)) {
                $data[$handle] = $attribute;
            }
        }

        $data['ingredients'] = array();
        foreach ($this->getIngredients() as $ingredient) {
            $ingredientData = array();
            foreach ($ingredient as $handle => $attribute) {
                if (!in_array($handle, $excludes)) {
                    $ingredientData[$handle] = $attribute;
                }
            }
            $data['ingredients'][] = $ingredientData;
        }
        $data['ratings'] = array();
        foreach ($this->ratings as $rating) {
            $ratingData = array();
            foreach ($rating as $handle => $attribute) {
                if (!in_array($handle, $excludes)) {
                    $ratingData[$handle] = $attribute;
                }
            }
            $data['ratings'][] = $ratingData;
        }
        return $data;
    }
}
