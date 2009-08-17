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
     * Modules initialization
     *
     */
    public function _initModules()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->setControllerDirectory(
            array(
                'default' => APPLICATION_PATH . '/modules/default/controllers'
            )
        );
        $front->setDefaultModule('default');
        $front->setDefaultControllerName('search');
        $front->setDefaultAction('all');
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
    
    public function _initLayout()
    {
        Zend_Layout::startMvc();
    }
}