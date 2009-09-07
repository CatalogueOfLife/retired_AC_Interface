<?php
class InfoControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    
    /**
     * Info controller redirects requests with no action to info/about
     */
    public function testDefaultInfoPageIsASuccessfulRequestToInfoAbout()
    {
        $this->dispatch('/info');
        $this->assertFalse($this->response->isException());
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertController('info');
        $this->assertAction('about');
    }
}