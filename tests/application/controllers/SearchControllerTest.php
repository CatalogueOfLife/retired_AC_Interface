<?php
require_once 'Zend/Test/PHPUnit/ControllerTestCase.php';

class SearchControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    
    public function testDefaultPageIsASuccessfulRequestToSearchAll ()
    {
        //TODO: fix, tests on controllers not working yet
        $this->_forward(
            Zend_Controller_Front::getInstance()->getDefaultAction(),
            Zend_Controller_Front::getInstance()->getDefaultControllerName()
        );
        // Tests there are no exceptions on the home page
        $this->assertFalse($this->response->isException());
        $this->assertResponseCode(200);
        // Tests for redirection to the error handler
        $this->assertNotRedirect();
        $this->assertController('search');
        $this->assertAction('all');
    }
}