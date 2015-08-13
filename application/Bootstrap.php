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
     * @var Bootstrap
     */
    private static $_instance = null;
    const DEFAULT_LANGUAGE = 'en';
    public $currentLanguage;

    public function _initAutoload ()
    {
        // Set default timezone to suppress strict error
        date_default_timezone_set(@date_default_timezone_get());

        $resourceLoader = new Zend_Loader_Autoloader_Resource(
            array(
                'basePath' => APPLICATION_PATH,
                'namespace' => 'ACI'
            ));
        $resourceLoader->addResourceType('model', 'models/', 'Model')->addResourceType('form', 'forms/',
            'Form');
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Eti_');
    }

    public function _initLogger ()
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
                    APPLICATION_PATH . '/log/error.log');
            }
        }
        else {
            // Do not log
            $writer = new Zend_Log_Writer_Null();
        }
        $writer->addFilter((int) $config->log->filter->priority);
        $logger = new Zend_Log($writer);
        Zend_Registry::set('logger', $logger);
    }

    public function _initTranslate ()
    {
        $this->currentLanguage = $this->_getCurrentLanguage();
        $translator = new Zend_Translate('Ini',
            APPLICATION_PATH . '/data/languages/lang.' . $this->currentLanguage . '.ini');
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
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=' . $view->encoding);
        $view->headTitle('Catalogue of Life - ' . $config->eti->application->edition);
        $view->headTitle()->setSeparator(' : ');
        // Also pass currentLanguage to viewer
        $view->language = $this->_getCurrentLanguage();
        $view->hash = md5(time());
        // Add custom view helpers path
        $view->addHelperPath('Eti/View/Helper/', 'Eti_View_Helper_');
        // View renderer
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH . '/controllers/helpers', 'ACI_Helper');
        return $view;
    }

    /**
     * Database initialization based on the application config file
     */
    public function _initDatabase ()
    {
        $config = Zend_Registry::get('config');
        $db = Zend_Db::factory($config->database);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        Zend_Registry::set('db', $db);
    }

    public function _initSession ()
    {
        Zend_Session::setOptions(array(
            'strict' => true
        ));
    }

    public function _initLayout ()
    {
        Zend_Layout::startMvc();
    }

    public function _initCache ()
    {
        $config = Zend_Registry::get('config');
        $cache = null;

        if ($config->cache->enabled) {
            $frontendOptions = array(
                'lifetime' => isset($config->cache->prefix) ? $config->cache->prefix : null,
                'automatic_serialization' => true
            );
            $backendOptions = array(
                'cache_dir' => $config->cache->directory,
                'hashed_directory_level' => 1
            );
            if ($config->cache->prefix) {
                $backendOptions['file_name_prefix'] = $config->cache->prefix;
            }
            try {
                $cache = Zend_Cache::factory('Core', 'File',
                    $frontendOptions, $backendOptions);
            }
            catch (Zend_Cache_Exception $e) {}
        }
        Zend_Registry::set('cache', $cache);
    }

    /**
     * Convenience method to get a reference to The Bootstrap
     * singleton when not in an action.
     *
     * @return Bootstrap
     */
    public static function instance ()
    {
        if (self::$_instance === null) {
            self::$_instance = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        }
        return self::$_instance;
    }

    public function getOption ($key)
    {
        $chunks = explode('.', $key);
        if (count($chunks) === 1) {
            return parent::getOption($key);
        }
        $options = parent::getOptions();
        foreach ($chunks as $chunk) {
            if (array_key_exists($chunk, $options)) {
                $options = $options[$chunk];
            }
            else {
                return 0;
            }
        }
        return $options;
    }

    private function _getCurrentLanguage ()
    {
        if ($this->currentLanguage) {
            return $this->currentLanguage;
        }
        return $this->_setCurrentLanguage();
    }

    private function _setCurrentLanguage ()
    {
        $config = Zend_Registry::get('config');
        $cookieExpiration = $config->advanced->cookie_expiration;
        $currentLanguage = self::DEFAULT_LANGUAGE;
        // Language has already been set in cookie
        if (isset($_COOKIE['aci_language'])) {
            if (file_exists(APPLICATION_PATH . '/data/languages/lang.' . $_COOKIE['aci_language'] . '.ini')) {
                return $_COOKIE['aci_language'];
            }
        }
        // Test if translation in browser language exists; if not return default
        if ($browserLanguage = $this->_setLanguageBasedOnBrowser()) {
            $currentLanguage = $browserLanguage;
        }
        setcookie('aci_language', $currentLanguage, time() + $cookieExpiration,
            '/', '');

        return $currentLanguage;
    }

    private function _setLanguageBasedOnBrowser ()
    {
        $locale = new Zend_Locale();
        $browserLanguage = $locale->getLanguage();
        $browserRegion = $locale->getRegion();
        $allLanguages = self::getOption('language');
        // Translation file in browser language is available and language is enabled in config.ini
        // First try if localized translation file is available; if not test for just the language
        $translationFiles = array(
            $browserLanguage . '_' . strtoupper($browserRegion),
            $browserLanguage
        );
        foreach ($translationFiles as $language) {
            if (array_key_exists($language, $allLanguages) && $allLanguages[$language] == 1) {
                return $language;
            }
        }
        return false;
    }
}