<?php
/**
 * Annual Checklist Interface
 *
 * Class InfoControllerTest
 * Unit tests to the info controller
 *
 * @category    ACI
 * @package     tests
 * @subpackage  controllers
 *
 */
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
    
    public function testDefaultInfoAction()
    {
        $this->dispatch('/info/dummy');
        $this->assertController('info');
        $this->assertAction('about');
    }
}