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
        if ($config->log->enabled) {
            if ('development' == APPLICATION_ENV) {
                // Log into Firebug
                $writer = new Zend_Log_Writer_Firebug();
            }
            else {
                // Log to file
                $writer = new Zend_Log_Writer_Stream(
                    APPLICATION_PATH . '/log/error.log'
                );
            }
        }
        else {
            // Do not log
            $writer = new Zend_Log_Writer_Null();
        }
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
        $view->doctype('XHTML1_STRICT');
        $view->encoding = $config->resources->view->encoding;
        $view->setEncoding($view->encoding);
        $view->headMeta()->appendHttpEquiv(
            'Content-Type', 'text/html;charset=' . $view->encoding
        );
        $view->headTitle(
            'Catalogue of Life - ' .
            $config->eti->application->edition . ' Dynamic Checklist'
        );
        $view->headTitle()->setSeparator(' :: ');        
        // Add custom view helpers path        
        $view->addHelperPath('Eti/View/Helper/', 'Eti_View_Helper_');        
        // View renderer
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        Zend_Controller_Action_HelperBroker::addPath(
            APPLICATION_PATH . '/controllers/helpers', 'ACI_Helper'
        );
        return $view;
    }
    
    /**
     * Database initialization based on the application config file
     */
    public function _initDatabase()
    {
        $config = Zend_Registry::get('config');
        $db = Zend_Db::factory($config->database);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
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
    
    public function _initCache()
    {
        $config = Zend_Registry::get('config');
        $cache = null;
        
        if ($config->cache->enabled) {
            $frontendOptions =
                array(
                    'lifetime' => null,
                    'automatic_serialization' => true
                );
            $backendOptions =
                array(
                    'cache_dir' => $config->cache->directory,
                    'hashed_directory_level' => 1
                );
            if ($config->cache->prefix)  {
                $backendOptions['file_name_prefix'] = $config->cache->prefix;
            }
            try {
                $cache = Zend_Cache::factory(
                    'Core', 'File', $frontendOptions, $backendOptions
                );
            } catch (Zend_Cache_Exception $e) {}
        }
        Zend_Registry::set('cache', $cache);
    }
}