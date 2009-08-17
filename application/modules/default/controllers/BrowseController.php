<?php
class BrowseController extends Zend_Controller_Action
{
    public function init()
    {}
    
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