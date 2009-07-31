<?php
require_once 'PHPUnit/Framework/TestCase.php';

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController->setControllerDirectory(
            array(
                'default' => APPLICATION_PATH . '/modules/default/controllers')
            );
        $this->frontController->setDefaultModule('default');
    }
    public function testHomePageIsASuccessfulRequestToIndex ()
    {
        // Runs the test on /, the homepage
        $this->dispatch('/');
        // Tests there are no exceptions on the home page
        $this->assertFalse($this->response->isException());
        // Tests for redirection to the error handler
        $this->assertNotRedirect();
        $this->assertController('index');
        $this->assertAction('index');
    }
}

