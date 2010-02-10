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
    
    public function testInfoAction()
    {
        $this->dispatch('/info/about');
        $this->assertController('info');
        $this->assertAction('about');
    }
    
    public function testSpecialAction()
    {
        $this->dispatch('/info/special');
        $this->assertController('info');
        $this->assertAction('special');
    }
    
    public function testACAction()
    {
        $this->dispatch('/info/ac');
        $this->assertController('info');
        $this->assertAction('ac');
    }
    
    public function testDatabasesAction()
    {
        $this->dispatch('/info/databases');
        $this->assertController('info');
        $this->assertAction('databases');
    }
    
    public function testHierarchyAction()
    {
        $this->dispatch('/info/hierarchy');
        $this->assertController('info');
        $this->assertAction('hierarchy');
    }
    
    public function testCopyrightAction()
    {
        $this->dispatch('/info/copyright');
        $this->assertController('info');
        $this->assertAction('copyright');
    }
    
    public function testCiteAction()
    {
        $this->dispatch('/info/cite');
        $this->assertController('info');
        $this->assertAction('cite');
    }
    
    public function testWebsitesAction()
    {
        $this->dispatch('/info/websites');
        $this->assertController('info');
        $this->assertAction('websites');
    }
    
    public function testContactAction()
    {
        $this->dispatch('/info/contact');
        $this->assertController('info');
        $this->assertAction('contact');
    }
    
    public function testAcknowledgementsAction()
    {
        $this->dispatch('/info/acknowledgements');
        $this->assertController('info');
        $this->assertAction('acknowledgements');
    }
}