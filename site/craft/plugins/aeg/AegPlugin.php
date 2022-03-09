<?php
namespace Craft;

class AegPlugin extends BasePlugin
{
	public function init()
	{
        if (craft()->request->isCpRequest()) {
            $curUri = craft()->request->getUrl();
            if (strpos($curUri, '/admin/entries/articles/') !== false) {
                craft()->templates->includeJsResource('aeg/js/aeg.js');
            }
        }
	}

	public function getName()
	{
		return 'Aeg';
	}

	public function getVersion()
	{
		return '1.0';
	}

	public function getSchemaVersion()
	{
		return '1.0';
	}

	public function getDeveloper()
	{
		return 'Fork Unstable Media GmbH';
	}

	public function getDeveloperUrl()
	{
		return 'http://fork.de';
	}

    public function addTwigExtension()
    {
        Craft::import('plugins.aeg.twigextensions.AegTwigExtension');
        return new AegTwigExtension();
    }

    // custom email messages (ingredients mail)
    public function registerEmailMessages()
    {
        return array(
            'aegIngredientsMail',
        );
    }
}