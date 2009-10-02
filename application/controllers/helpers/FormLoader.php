<?php
class ACI_Helper_FormLoader extends Zend_Controller_Action_Helper_Abstract
{
    protected $_controller;
    protected $_action;
    
    public function init()
    {
        $this->_controller = $this->getRequest()->getControllerName();
        $this->_action = $this->getRequest()->getActionName();
    }
    
    public function getSearchForm()
    {
        switch($this->_controller) {
            case 'browse':
                return new ACI_Form_Dojo_BrowseClassification();
                break;
            case 'search':
                switch($this->_action) {
                    case 'scientific':
                        return new ACI_Form_Dojo_SearchScientific();
                        break;
                    case 'all':
                    case 'common':
                    case 'distribution':
                        return new ACI_Form_Dojo_Search();
                        break;
                }
                break;
        }
        return null;
    }
}