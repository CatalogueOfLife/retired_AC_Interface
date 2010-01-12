<?php
require_once 'mocks/FormLoaderMock.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_FormLoaderTest
 * Unit tests to the ACI_Helper_FormLoader class
 *
 * @category    ACI
 * @package     tests
 * @subpackage  controllers/helpers
 *
 */
class ACI_Helper_FormLoaderTest extends PHPUnit_Framework_TestCase
{   
    public function testGetSearchFormForSearchAll()
    {
        $formLoader = new ACI_Helper_FormLoaderMock('search', 'all');
        $this->assertTrue(
            $formLoader->getSearchForm() instanceof ACI_Form_Dojo_Search
        );
    }
    
    public function testGetSearchFormForSearchScientific()
    {
        $formLoader = new ACI_Helper_FormLoaderMock('search', 'scientific');
        $this->assertTrue(
            $formLoader->getSearchForm() instanceof 
            ACI_Form_Dojo_SearchScientific
        );
    }
    
    public function testGetSearchFormForSearchCommon()
    {
        $formLoader = new ACI_Helper_FormLoaderMock('search', 'common');
        $this->assertTrue(
            $formLoader->getSearchForm() instanceof ACI_Form_Dojo_Search
        );
    }
    
    public function testGetSearchFormForSearchDistribution()
    {
        $formLoader = new ACI_Helper_FormLoaderMock('search', 'distribution');
        $this->assertTrue(
            $formLoader->getSearchForm() instanceof ACI_Form_Dojo_Search
        );
    }
    
    public function testGetSearchFormForBrowseClassification()
    {
        $formLoader = new ACI_Helper_FormLoaderMock('browse', 'classification');
        $this->assertTrue(
            $formLoader->getSearchForm() instanceof 
            ACI_Form_Dojo_BrowseClassification
        );
    }
    
    public function testGetNullForm()
    {
        $formLoader = new ACI_Helper_FormLoaderMock();
        $this->assertNull($formLoader->getSearchForm());
    }
    
    public function testGetExportForm()
    {
        $formLoader = new ACI_Helper_FormLoaderMock();
        $this->assertTrue(
            $formLoader->getExportForm() instanceof 
            ACI_Form_Dojo_Export
        );
    }
    
    public function testGetItemsPerPageForm()
    {
        $formLoader = new ACI_Helper_FormLoaderMock();
        $this->assertTrue(
            // match and key are elements existing in the form
            $formLoader->getItemsForm(array('match', 'key'), '10') instanceof 
            ACI_Form_Dojo_ItemsPerPage
        );
    }
}