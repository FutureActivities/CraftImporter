<?php
namespace Craft;

class ImporterPlugin extends BasePlugin
{
    function getName()
    {
         return Craft::t('Data Importer');
    }

    function getVersion()
    {
        return '0.0.1';
    }

    function getDeveloper()
    {
        return 'Future Activities';
    }

    function getDeveloperUrl()
    {
        return 'https://github.com/FutureActivities';
    }
    
    function init()
    {
        require_once 'library/progress/Manager.php';
        require_once 'library/progress/Registry.php';
    }
}