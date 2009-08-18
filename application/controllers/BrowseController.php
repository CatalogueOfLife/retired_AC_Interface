<?php
class BrowseController extends Zend_Controller_Action
{
    protected $_logger;
    
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    public function treeAction()
    {
         $this->view->title = $this->view->t
            ->translate('Taxonomic_tree');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function classificationAction()
    {
         $this->view->title = $this->view->t
            ->translate('Taxonomic_classification');
        $this->view->headTitle($this->view->title, 'APPEND');
        
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('tree');
    }
    
}