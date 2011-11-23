<?php
/**
 * Annual Checklist Interface
 *
 * Class AController
 * Abstract controller class
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
abstract class AController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
    // Advanced options from ini available as properties
    protected $_cookieExpiration;
    protected $_webserviceTimeout;

    public function init ()
    {
        $this->_logger = Zend_Registry::get('logger');
        // Convert POST to GET request as friendly URL
        $this->_postToGet();
        $this->_db = Zend_Registry::get('db');
        $this->_logger->debug($this->_getAllParams());
        // Add custom view helpers path
        $this->view->addHelperPath(
            APPLICATION_PATH . '/views/helpers/', 'ACI_View_Helper_');
        // Initialize Dojo, disabled by default
        Zend_Dojo::enableView($this->view);
        $this->view->dojo()->disable();
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
        $this->view->latestSearch = $this->getHelper('Query')->getLatestQuery();
        $config = Zend_Registry::get('config');
        $this->view->app = $config->eti->application;
        $this->view->googleAnalyticsTrackerId = $config->view->googleAnalytics->trackerId;
        $this->view->interfaceLanguages = $this->_setInterfaceLanguages();
        $this->_cookieExpiration = $this->_setCookieExpiration();
        $this->view->cookieExpiration = $this->_cookieExpiration;
        $this->_webserviceTimeout = $this->_setWebserviceTimeout();
        $this->view->statisticsModuleEnabled = $this->_moduleEnabled('statistics');
    }

    public function getDbAdapter ()
    {
        return $this->_db;
    }

    protected function _getSearchForm ()
    {
        return $this->getHelper('FormLoader')->getSearchForm();
    }

    protected function _renderFormPage ($header, $form)
    {
        if ($form instanceof ACI_Form_Dojo_AMultiCombo) {
            $this->view->dojo()->registerModulePath('ACI', 
                $this->view->baseUrl() . JS_PATH . '/library/ACI')->requireModule(
                'ACI.dojo.TxReadStore');
            // ComboBox (v1.3.2) custom extension
            $this->view->headScript()->appendFile(
                $this->view->baseUrl() . JS_PATH . '/ComboBox.ext.js');
        }
        $this->getHelper('Renderer')->renderFormPage($header, $form);
    }

    protected function _renderResultsPage (array $elements = array())
    {
        $this->getHelper('Renderer')->renderResultsPage($elements);
    }

    protected function _exportResults ()
    {
        $this->view->layout()->disableLayout();
        $fileName = 'CoL_data.csv';
        $controller = $this->getHelper('Query')->getLatestQueryController();
        $action = $this->getHelper('Query')->getLatestQueryAction();
        $latestSelect = $this->getHelper('Query')->getLatestSelect();
        if (!$latestSelect instanceof Zend_Db_Select) {
            $this->getHelper('Export')->setHeaders($fileName);
            exit('');
        }
        $this->getHelper('Export')->csv($controller, $action, $latestSelect, $fileName);
    }

    protected function _setSessionFromParams (array $values)
    {
        foreach ($values as $v) {
            $this->getHelper('SessionHandler')->set($v, $this->_getParam($v));
        }
    }

    protected function _setParamsFromSession (array $params)
    {
        foreach ($params as $p) {
            $v = $this->getHelper('SessionHandler')->get($p);
            if ($v !== null) {
                $this->_logger->debug("Setting $p to $v from session");
                $this->_setParam($p, $v);
            }
        }
    }
    
    protected function _createJsTranslationArray (array $jsTranslation)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $jsArray = "var translations = new Array();\n";
        foreach ($jsTranslation as $v) {
            $jsArray .= "\ttranslations['$v'] = '".$translator->translate($v)."';\n";
        }
        return $jsArray;
    }

    /**
     * Redirects a request using the given action, controller, module and params
     * in the request
     */
    protected function _postToGet ()
    {
        if ($this->getRequest()->isPost()) {
            $params = array();
            // Remove unneeded parameters
            $exclude = array(
                'action', 
                'controller', 
                'module', 
                'search', 
                'update', 
                'clear'
            );
            foreach ($this->getRequest()->getParams() as $k => $v) {
                if (!in_array($k, $exclude) && strlen(trim($v))) {
                    $params[$k] = $v;
                }
            }
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->setGotoSimple($this->getRequest()->getParam('action'), 
                $this->getRequest()->getParam('controller'), 
                $this->getRequest()->getParam('module'), $params);
        }
    }

    protected function _moduleEnabled ($module)
    {
        return Bootstrap::instance()->getOption('module.' . $module);
    }

    private function _setInterfaceLanguages ()
    {
    	/*
    	 * Protecting the current way the languages are made in the menu.
    	 */
/*        $locale = new Zend_Locale($this->view->language);
        $allLanguages = Bootstrap::instance()->getOption('language');
        $selectedLanguages = array_flip(array_keys($allLanguages, 1));
        $languageScripts = $locale->getTranslationList('ScriptToLanguage', 'en');
        // Strip off the potentially present locale first when checking for scripts!
        $currentLanguageScripts = explode(' ', $languageScripts[substr($this->view->language, 0, 2)]);
        foreach ($selectedLanguages as $iso => $language) {
            $selectedLanguages[$iso] = ucfirst(
                $locale->getTranslation($iso, 'language', $iso));
            // Append transliteration script(s) of this language does not match script(s) of current language
            // Strip off the potentially present locale first when checking for scripts!
            $scripts = explode(' ', $languageScripts[substr($iso, 0, 2)]);
            if (count(array_intersect($currentLanguageScripts, $scripts)) == 0) {
                $selectedLanguages[$iso] .= ' (' . ucfirst(
                    $locale->getTranslation($iso, 'language', 
                        $this->view->language)) . ')';
            }
        }
        asort($selectedLanguages, SORT_LOCALE_STRING);
        return $selectedLanguages;*/
    	$locale = new Zend_Locale($this->view->language);
        $allLanguages = Bootstrap::instance()->getOption('language');
        $selectedLanguages = array_flip(array_keys($allLanguages, 1));
        $languageScripts = $locale->getTranslationList('ScriptToLanguage', 'en');
        // Strip off the potentially present locale first when checking for scripts!
        $currentLanguageScripts = explode(' ', $languageScripts[substr($this->view->language, 0, 2)]);
        foreach ($selectedLanguages as $iso => $language) {
        	$selectedLanguages[$iso] = array();
      		$selectedLanguages[$iso]['english_name'] = ucfirst(
              	$locale->getTranslation($iso, 'language', $iso));
            // Append transliteration script(s) of this language does not match script(s) of current language
            // Strip off the potentially present locale first when checking for scripts!
            $scripts = explode(' ', $languageScripts[substr($iso, 0, 2)]);
            $selectedLanguages[$iso]['original_name'] = ucfirst(
                $locale->getTranslation($iso, 'language', 
                    $this->view->language));
        }
        asort($selectedLanguages, SORT_LOCALE_STRING);
        return $selectedLanguages;
    }

    private function _setCookieExpiration ()
    {
        $config = Zend_Registry::get('config');
        return $config->advanced->cookie_expiration;
    }

    private function _setWebserviceTimeout ()
    {
        $config = Zend_Registry::get('config');
        $timeout = $config->advanced->webservice_timeout;
        // Maximize to 10s
        if (empty($timeout) || $timeout > 10) {
            $timeout = 10;
        }
        return $timeout;
    }
}