<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_SessionHandler
 * Session handler helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
class ACI_Helper_SessionHandler extends Zend_Controller_Action_Helper_Abstract
{
    protected $_session;
    
    public function init()
    {
        Zend_Session::start();
        $this->_session = new Zend_Session_Namespace();
        $this->_controller = $this->getRequest()->getControllerName();
        $this->_action = $this->getRequest()->getActionName();
    }
    public function set($property, $value, $addContext = true)
    {
        $this->_session->unlockAll();
        if($addContext == true) {
            $this->_addContext($property);
        }
        $this->_session->$property = $this->_cleanString($value);
    }
    public function get($property, $addContext = true)
    {
        if($addContext == true) {
            $this->_addContext($property);
        }
        return $this->_session->$property;
    }
    public function clear($property = null)
    {
        if(is_null($property)) {
            $this->_session->unsetAll();
        }
        else {
            $this->_addContext($property);
            unset($this->_session->$property);
        }
    }
    protected function _addContext(&$property)
    {
        $property = $this->_controller . '_' . $this->_action . '_' . $property;
    }
    protected function _cleanString($str)
    {
        return stripslashes($str);
    }
    public function getIterator()
    {
        return $this->_session->getIterator();
    }
}