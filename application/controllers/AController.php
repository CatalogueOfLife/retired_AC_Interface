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
    
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_db = Zend_Registry::get('db');
        $this->_logger->debug($this->_getAllParams());
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
        $this->view->latestSearch = $this->getHelper('Query')->getLatestQuery();
        $config = Zend_Registry::get('config');
        $this->view->googleAnalyticsTrackerId =
            $config->resources->view->googleAnalytics->trackerId;
    }
    
    public function getDbAdapter()
    {
        return $this->_db;
    }
    
    protected function _getSearchForm()
    {
        return $this->getHelper('FormLoader')->getSearchForm();
    }
    
    protected function _renderFormPage($header, $form)
    {
        if ($form instanceof ACI_Form_Dojo_AMultiCombo) {
            $this->view->dojo()
                 ->registerModulePath(
                     'ACI', $this->view->baseUrl() . '/scripts/library/ACI'
                 )
                 ->requireModule('ACI.dojo.TxReadStore');
            // ComboBox (v1.3.2) custom extension
            $this->view->headScript()->appendFile(
                $this->view->baseUrl() . '/scripts/ComboBox.ext.js'
            );
        }
        $this->getHelper('Renderer')->renderFormPage($header, $form);
    }
    
    protected function _renderResultsPage(array $elements = array())
    {
        $this->getHelper('Renderer')->renderResultsPage($elements);
    }
    
    protected function _setSessionFromParams(array $values)
    {
        foreach ($values as $v) {
            $this->getHelper('SessionHandler')->set($v, $this->_getParam($v));
        }
    }
    protected function _setParamsFromSession(array $params)
    {
        foreach ($params as $p) {
            $v = $this->getHelper('SessionHandler')->get($p);
            if ($v !== null) {
                $this->_logger->debug("Setting $p to $v from session");
                $this->_setParam($p, $v);
            }
        }
    }
}