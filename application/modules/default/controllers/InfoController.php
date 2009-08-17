<?php
class InfoController extends Zend_Controller_Action
{
    protected $_logger;
    
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->view->translate = Zend_Registry::get('translate');
    }
    
    public function aboutAction()
    {
        
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('about');
    }
    
}