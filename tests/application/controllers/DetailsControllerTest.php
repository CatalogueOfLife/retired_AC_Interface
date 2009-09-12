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
    
    /**
     * When a non-existent action is specified to the details controller,
     * it forwards the request to search/all (default page)
     */
    public function testDefaultDetailsAction()
    {
        $this->dispatch('/details/dummy');
        $this->assertController('search');
        $this->assertAction('all');
    }
    
    /**
     * The species details page correctly retrieves and displays the species 
     * from the given id
     */
    public function testSpeciesDetailsDisplay()
    {
        $id = 4935432;
        $details = new ACI_Model_Details(Zend_Registry::get('db'));
        $details->species($id);
        $this->dispatch('/details/species/id/' . $id);
        $this->assertController('details');
        $this->assertAction('species');
        //Details table
        $this->assertQueryCount('table.details-table', 1);
        //Scientific name
        $this->assertQueryContentContains(
            'table.details-table tr td', 
            '<i>Aa rosei</i> Ames (accepted name)'
        );
        //Source database
        $this->assertQueryContentContains(
            'table.details-table tr td a',
            'World Checklist of Selected Plant Families'
        );
        //LSID 
        $this->assertQueryContentContains(
            'table.details-table tr td p.lsid', 
            'urn:lsid:catalogueoflife.org:taxon:f0c67b16-29c1-102b-9a4a-00304854f820:ac2009'
        );   
    }
    
    /**
     * The database details page correctly retrieves and displays the database 
     * from the given id
     */
    public function testDatabaseDetailsDisplay()
    {        
        $id = 10;
        $database = new ACI_Model_Table_Databases();
        $database->get($id);
        $this->dispatch('/details/database/id/' . $id);
        $this->assertController('details');
        $this->assertAction('database');
        //Details table
        $this->assertQueryCount('table.details-table', 1);
        //Logo
        $this->assertQueryCount('a img.wrapLogo', 1);     
        //Name
        $this->assertQueryContentContains('table tr td', 'FishBase');
        //Link
        $this->assertQueryContentContains(
            'table.details-table tr td', 'http://www.fishbase.org'
        );    
    }
}