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
        $config = Zend_Registry::get('config');
        $smartySettings = $config->get('smarty')->toArray();
        
        $view = new Eti_View_Smarty($smartySettings);
        
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        
        $viewRenderer->setViewBasePathSpec($smartySettings['template_dir'])
            ->setViewScriptPathSpec(':controller/:action.:suffix')
            ->setViewScriptPathNoControllerSpec(':action.:suffix')
            ->setViewSuffix('tpl');
            
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        
        $view->doctype('XHTML1_STRICT');
                
        Zend_Registry::set('view', $view);
        
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
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('dbAdapter', $db);
    }
}