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
        $translator = new Zend_Translate('Ini', APPLICATION_PATH .
            '/languages/lang.en.ini', 'en');
        Zend_Registry::set('translator', $translator);
    }
    
    /**
     * View initialization
     *
     * @return Zend_View $view
     */
    public function _initView ()
    {
        $config = Zend_Registry::get('config');
        $view = new Zend_View();
        $view->doctype('XHTML1_STRICT');
        $view->setEncoding('UTF-8');
        $view->headMeta()
            ->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
        $view->headTitle('Catalogue of Life - ' .
            $config->custom->application->version . ' Annual Checklist');
        $view->headTitle()->setSeparator(' :: ');
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        
        //Variables
        $view->t = Zend_Registry::get('translator');
        $view->app = $config->custom->application;
        
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
    
    public function _initLayout()
    {
        Zend_Layout::startMvc();
    }
}