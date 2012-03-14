<?php
/**
 * Annual Checklist Interface
 *
 * index.php
 * Initial page, launches the bootstrappers
 *
 * @category    ACI
 * @package     public
 */
// Define path to application directory
defined('APPLICATION_PATH')
    || define(
        'APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application')
    );

// Define application environment
defined('APPLICATION_ENV')
    || define(
        'APPLICATION_ENV', (getenv('APPLICATION_ENV') ?
        getenv('APPLICATION_ENV') : 'production')
    );

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(realpath(APPLICATION_PATH . '/../library'), get_include_path())
    )
);

// Incude classes required for the initialitzation of the app
require_once 'Zend/Application.php';
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Config/Ini.php';

// Load configuration
$config = new Zend_Config_Xml(
    APPLICATION_PATH . '/configs/application.xml',
    APPLICATION_ENV,
    true
);
try {
    $config->merge(
        new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/config.ini',
            APPLICATION_ENV
        )
    );
}
// There is no config file or the section APPLICATION_ENV doe not exist
catch(Zend_Config_Exception $e) {
    exit(
        '<b>The configuration of the application is not valid</b><br/>' .
        'Please make sure that the proper environment [' . APPLICATION_ENV . 
        '] has been defined in the configuration file'
    );
}
define('TREE_ICONS_PATH', APPLICATION_PATH.'/../public/images/tree_icons');
define('JS_PATH', $config->resources->view->scripts);
// Init application
$application = new Zend_Application(APPLICATION_ENV, $config);
// Store config
Zend_Registry::set('config', $config);
// Run bootstrap
$application->bootstrap()->run();