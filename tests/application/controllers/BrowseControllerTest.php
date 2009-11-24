<?php
/**
 * Annual Checklist Interface
 *
 * Class BrowseControllerTest
 * Unit tests to the browse controller
 *
 * @category    ACI
 * @package     tests
 * @subpackage  controllers
 *
 */
class BrowseControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    /**
     * Browse controller redirects requests with no action to browse/tree
     */
    public function testDefaultBrowsePageIsASuccessfulRequestToBrowseTree()
    {
        $this->dispatch('/browse');
        $this->assertFalse($this->response->isException());
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertController('browse');
        $this->assertAction('tree');
    }
    
    public function testDefaultBrowseAction()
    {
        $this->dispatch('/browse/dummy');
        $this->assertController('browse');
        $this->assertAction('tree');
    }
}