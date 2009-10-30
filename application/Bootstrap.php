<?php
/**
 * Annual Checklist Interface
 *
 * Class Bootstrap
 * Handles the application initialization
 * All methods in this class that are prefixed by an underscore
 * are automatically loaded in the application init
 *
 * @category    ACI
 * @package     application
 * @subpackage  core
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
    
    public function _initAutoload()
    {
        $resourceLoader = new Zend_Loader_Autoloader_Resource(
            array(
                'basePath'  => APPLICATION_PATH,
                'namespace' => 'ACI'
            )
        );
        $resourceLoader->addResourceType('model', 'models/', 'Model')
                       ->addResourceType('form', 'forms/', 'Form');
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Eti_');
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
            '/data/languages/lang.en.ini', 'en');
        Zend_Registry::set('Zend_Translate', $translator);
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
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->setEncoding('ISO-8859-1');
        $view->headMeta()
            ->appendHttpEquiv('Content-Type', 'text/html;charset=iso-8859-1');
        $view->headTitle(
            'Catalogue of Life - ' .
            $config->custom->application->version . ' Annual Checklist'
        );
        $view->headTitle()->setSeparator(' :: ');
        // Add custom view helpers path
        $view->addHelperPath('Eti/View/Helper/', 'Eti_View_Helper');
        $view->addHelperPath(
            APPLICATION_PATH . '/views/helpers/', 'ACI_View_Helper'
        );
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        // Initialize Dojo, disabled by default
        Zend_Dojo::enableView($view);
        $view->dojo()->disable();
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        Zend_Controller_Action_HelperBroker::addPath(
            APPLICATION_PATH . '/controllers/helpers', 'ACI_Helper'
        );
        //Variables
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
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        Zend_Registry::set('db', $db);
    }
    
    public function _initSession()
    {
        Zend_Session::setOptions(array('strict' => true));
    }
    
    public function _initLayout()
    {
        Zend_Layout::startMvc();
    }
}