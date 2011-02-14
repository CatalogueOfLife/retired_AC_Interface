<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_FormLoaderMock
 * Mock of the ACI_Helper_FormLoader that allows to set the controller and the
 * action through the constructor
 *
 * @category    ACI
 * @package     tests
 * @subpackage  mocks
 *
 */
class ACI_Helper_FormLoaderMock extends ACI_Helper_FormLoader
{
    public function __construct($controller = null, $action = null) {
        $this->_controller = $controller;
        $this->_action = $action;
    }
}