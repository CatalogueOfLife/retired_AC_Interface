<?php
class InfoController extends Zend_Controller_Action
{
    public function init()
    {}
    
    public function aboutAction()
    {
        
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('about');
    }
    
}