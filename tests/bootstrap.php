<?php
/**
 * Annual Checklist Interface
 *
 * bootstrap.php
 * Test bootstrapper
 *
 * @category    ACI
 * @package     tests
 * @subpackage  core
 *
 */
// Define path to application directory
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
define('APPLICATION_ENV', 'testing');

// Ensure library/ is on include_path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(realpath(APPLICATION_PATH . '/../library'), get_include_path())
    )
);

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(true);
$front->returnResponse(true);

$application->bootstrap()->run();
