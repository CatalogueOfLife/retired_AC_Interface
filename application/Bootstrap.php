<?php
/**
 * Bootstrap class
 *
 * All methods in this class that are prefixed by an underscore
 * are automatically loaded in the application init.
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    /**
     * Configuration initialization
     * It loads the application configuration and sets it in teh registry
     * to be accessed globally
     */
    public function _initConfig()
    {
        $config = new Zend_Config_Ini(
            APPLICATION_PATH . '/configs/application.ini',
            APPLICATION_ENV);
        
        Zend_Registry::set('config', $config);
    }
    
    /**
     * View initialization
     * Loads the custom implementation of the Smarty View to use
     * this template engine in place of the Zend_View.
     * Registers the view object so that it can be used globally.
     *
     * @return Zend_View $view
     */
    public function _initView ()
    {
        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        
        return $view;
    }
    
    /**
     * Database initialization based on the application ini config file
     *
     */
    public function _initDatabase()
    {
        $config = Zend_Registry::get('config');
        $db = Zend_Db::factory($config->resources->db);
        $db->getConnection();//test connection
        //Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }
    
    public function _initLogger()
    {
        $config = Zend_Registry::get('config');
        $writer = new Zend_Log_Writer_Firebug();
        $writer->addFilter((int)$config->log->filter->priority);
        $logger = new Zend_Log($writer);
        Zend_Registry::set('logger', $logger);
    }
    
    public function _initTranslate()
    {
        $translate = new Zend_Translate('Ini', APPLICATION_PATH .
            '/languages/lang.en.ini', 'en');
        Zend_Registry::set('translate', $translate);
    }
    
    public function _initLayout()
    {
        Zend_Layout::startMvc();
    }
}