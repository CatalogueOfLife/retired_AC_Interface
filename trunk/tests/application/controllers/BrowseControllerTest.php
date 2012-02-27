<?php
require_once 'mocks/BrowseControllerMock.php';
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
    
    public function testBrowseTreeAction()
    {
        $this->dispatch('/browse/tree');
        $this->assertController('browse');
        $this->assertAction('tree');
    }
    
    public function testBrowseClassificationAction()
    {
        $this->dispatch('/browse/classification');
        $this->assertController('browse');
        $this->assertAction('classification');
    }
    
    public function testBrowseClassificationContainsTheNeededFormElements()
    {
        $this->dispatch('/browse/classification');
        $this->assertQueryCount('form#browseClassificationForm', 1);
        $this->assertQueryCount('input#kingdom', 1);
        $this->assertQueryCount('input#phylum', 1);
        $this->assertQueryCount('input#order', 1);
        $this->assertQueryCount('input#class', 1);
        $this->assertQueryCount('input#superfamily', 1);
        $this->assertQueryCount('input#family', 1);
        $this->assertQueryCount('input#genus', 1);
        $this->assertQueryCount('input#species', 1);
        $this->assertQueryCount('input#infraspecies', 1);
        $this->assertQueryCount('input#match', 1);
        $this->assertQueryCount('input#search', 1);
    }
    
    public function testTreePersistanceEnabled()
    {
        // Set session
        $storedId = 300;
        $sess = $this->_getSession();
        $sess->set('tree_id', $storedId, false);
        $bc = $this->_getBrowseController();
        $bc->setTreePersistance(true);
        $id = null;
        $bc->persistTree($id);
        // $id has to be reset to the stored value
        $this->assertEquals($storedId, $id);
    }
    
    public function testTreePersistanceStored()
    {
        // Set session
        $storedId = 400;
        $sess = $this->_getSession();
        $sess->set('tree_id', $storedId, false);
        $bc = $this->_getBrowseController();
        $bc->setTreePersistance(true);
        $id = 200;
        $bc->persistTree($id);
        // $id remains the same
        $this->assertEquals($id, 200);
        // and it has been stored in session
        $this->assertEquals($sess->get('tree_id', false), $id);
    }
    
    public function testTreePersistanceDisabled()
    {
        // Clear session
        $sess = $this->_getSession();
        $bc = $this->_getBrowseController();
        $bc->setTreePersistance(false);
        $id = 100;
        $bc->persistTree($id);
        $this->assertEquals($id, 100);
        // No tree id set in session (null)
        $this->assertNull($sess->get('tree_id', false));
    }
    
    protected function _getSession()
    {
        $sess = new ACI_Helper_SessionHandler();        
        $sess->init();
        return $sess;
    }
    
    protected function _getBrowseController()
    {
        $bc = new BrowseControllerMock(
            $this->getRequest(), $this->getResponse()
        );
        return $bc;
    }
    
    public function tearDown()
    {
        // Clear session
        $sess = $this->_getSession();
        $sess->clear();
    }
}