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
    }
    
    protected function _getSearchForm()
    {
        return $this->getHelper('FormLoader')->getSearchForm();
    }
    
    protected function _highlightMatch($haystack, $needle)
    {
        return $this->getHelper('TextDecorator')
            ->highlightMatch($haystack, $needle);
    }
}