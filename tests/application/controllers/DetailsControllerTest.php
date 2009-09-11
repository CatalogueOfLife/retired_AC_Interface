<?php
/**
 * Annual Checklist Interface
 *
 * Class DetailsControllerTest
 * Unit tests to the details controller
 *
 * @category    ACI
 * @package     tests
 * @subpackage  controllers
 *
 */
class DetailsControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function setUp ()
    {
        parent::setUp();
        $this->frontController
             ->addControllerDirectory(APPLICATION_PATH . '/controllers');
    }
    
    /**
     * Details controller redirects requests with no action to search/all
     * (default page)
     */
    public function testDefaultDetailsPageIsASuccessfulRequestToSearchAll()
    {
        $this->dispatch('/details');
        $this->assertFalse($this->response->isException());
        $this->assertResponseCode(200);
        $this->assertNotRedirect();
        $this->assertController('search');
        $this->assertAction('all');
    }
    
    public function testDefaultDetailsAction()
    {
        $this->dispatch('/details/dummy');
        $this->assertController('search');
        $this->assertAction('all');
    }
}