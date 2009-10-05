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
        Zend_Registry::get('logger')
            ->debug("SESS Obj: " . var_export($this->getIterator(), true));
    }
    public function set($property, $value)
    {
        $this->_session->unlockAll();
        $this->_addContext($property);
        Zend_Registry::get('logger')
            ->debug("SESS: Setting $property to $value");
        $this->_session->$property = $value;
    }
    public function get($property)
    {
        $this->_addContext($property);
        Zend_Registry::get('logger')
            ->debug("SESS: Getting $property = " . $this->_session->$property);
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
    public function getIterator()
    {
        return $this->_session->getIterator();
    }
}