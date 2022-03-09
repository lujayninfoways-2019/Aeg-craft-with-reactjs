<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170922_122720_recipe_IngredientsWithDigits extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
	  return $this->alterColumn('recipe_ingredients', 'quantity', 'decimal(10,3) unsigned');
	}
}
