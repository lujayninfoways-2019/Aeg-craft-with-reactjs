<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m171017_101622_recipe_ChangeRecipeDescriptionToText extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        return $this->alterColumn('recipe_recipe', 'description', array('column' => 'text', 'required' => true));
	}
}
