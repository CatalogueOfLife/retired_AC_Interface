<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_FormLoader
 * Form loader helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
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
                $form = new ACI_Form_Dojo_BrowseClassification();
                break;
            case 'search':
                switch($this->_action) {
                    case 'scientific':
                        $form = new ACI_Form_Dojo_SearchScientific();
                        break;
                    case 'all':
                    case 'common':
                    case 'distribution':
                        $form = new ACI_Form_Dojo_Search();
                        break;
                }
                break;
        }
        if(!$form instanceof Zend_Form) {
            return null;
        }
        return $form->setAction($this->getAction());
    }
    
    public function getAction()
    {
        return $this->getFrontController()->getBaseUrl() . '/' .
            $this->_controller . '/' . $this->_action;
    }
}