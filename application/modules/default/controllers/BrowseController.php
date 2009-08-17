<?php
class BrowseController extends Zend_Controller_Action
{
    protected $_logger;
    
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->view->translate = Zend_Registry::get('translate');
    }
    
    public function treeAction()
    {
        
    }
    
    public function classificationAction()
    {
        
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('tree');
    }
    
}