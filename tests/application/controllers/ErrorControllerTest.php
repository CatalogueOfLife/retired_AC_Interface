<?php
/**
 * Annual Checklist Interface
 *
 * Class ErrorControllerTest
 * Unit tests to the errors controller
 *
 * @category    ACI
 * @package     tests
 * @subpackage  controllers
 *
 */
class ErrorControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    /**
     * Error controller redirects requests with no action to search/all
     */
    public function testDefaultErrorPageIsASuccessfulRequestToSearchAll()
    {
        $this->dispatch('/error');
        $this->assertFalse($this->response->isException());
        $this->assertResponseCode(200);
        $this->assertController('search');
        $this->assertAction('all');
    }
    
    public function testDefaultErrorActionIsASuccessfulRequestToSearchAll()
    {
        $this->dispatch('/error/dummy');
        $this->assertController('search');
        $this->assertAction('all');
    }
    
    public function testErrorAction()
    {
        $this->dispatch('/error/error');
        $this->assertController('error');
        $this->assertAction('error');
    }
}