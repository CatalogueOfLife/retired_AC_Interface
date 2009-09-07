<?php
class SearchControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    
    public function testDeafultSearchPageIsASuccessfulRequestToSearchAll()
    {
        $this->dispatch('/search');
        $this->assertFalse($this->response->isException());
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertController('search');
        $this->assertAction('all');
    }
}