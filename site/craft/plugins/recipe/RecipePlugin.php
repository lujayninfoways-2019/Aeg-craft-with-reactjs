<?php
/**
 * Recipe plugin for Craft CMS
 *
 * A recipe fieldtype
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2016 nystudio107
 * @link      http://nystudio107.com
 * @package   Recipe
 * @since     1.0.0
 */

namespace Craft;

class RecipePlugin extends BasePlugin
{
    // TODO: automatically add categories after install

    /**
     * @return mixed
     */
    public function init()
    {
    }

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
    public function getDescription()
    {
        return Craft::t('A customized version of nystudio107\'s recipe plugin by Fork');
    }

    /**
     * @return string
     */
    public function getDocumentationUrl()
    {
    }

    /**
     * @return string
     */
    public function getReleaseFeedUrl()
    {
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @return string
     */
    public function getSchemaVersion()
    {
        return '2.0.1';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'nystudio107 / Fork Unstable Media GmbH';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://fork.de';
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
     */
    public function onBeforeInstall()
    {
    }

    /**
     */
    public function onAfterInstall()
    {

/* -- Show our "Welcome to Recipe" message */

        craft()->request->redirect(UrlHelper::getCpUrl('recipe/welcome'));
    }

    /**
     */
    public function onBeforeUninstall()
    {
    }

    /**
     */
    public function onAfterUninstall()
    {
    }
}
