<?php
require_once 'mocks/SearchMock.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_SearchTest
 * Unit tests to the search model
 *
 * @category    ACI
 * @package     tests
 * @subpackage  models
 *
 */
class ACI_Model_SearchTest extends PHPUnit_Framework_TestCase
{
    protected $_model;
    
    public function setUp ()
    {
        $this->_model = new ACI_Model_Search(Zend_Registry::get('db'));
    }

    public function testStringRefersToHigherTaxa()
    {
        $this->assertTrue(
            $this->_model->stringRefersToHigherTaxa('kingdom'),
            'String "kingdom" identified as a reference to higher taxa'
        );
        $this->assertTrue(
            $this->_model->stringRefersToHigherTaxa('phylum'),
            'String "phylum" identified as a reference to higher taxa'
        );
        $this->assertTrue(
            $this->_model->stringRefersToHigherTaxa('class'),
            'String "class" NOT identified as a reference to higher taxa'
        );
        $this->assertTrue(
            $this->_model->stringRefersToHigherTaxa('order'),
            'String "order" NOT identified as a reference to higher taxa'
        );
        $this->assertTrue(
            $this->_model->stringRefersToHigherTaxa('superfamily'),
            'String "superfamily" NOT identified as a reference to higher taxa'
        );
        $this->assertTrue(
            $this->_model->stringRefersToHigherTaxa('family'),
            'String "family" NOT identified as a reference to higher taxa'
        );
        $this->assertFalse(
            $this->_model->stringRefersToHigherTaxa('genus'),
            'String "genus" identified as a reference to higher taxa'
        );
        $this->assertFalse(
            $this->_model->stringRefersToHigherTaxa('species'),
            'String "species" identified as a reference to higher taxa!'
        );
        $this->assertFalse(
            $this->_model->stringRefersToHigherTaxa('infraspecies'),
            'String "infraspecies" identified as a reference to higher taxa'
        );
    }
    
    public function testNormalizeNormalizedRank()
    {
        $rank = 'RANK_GENUS';
        $sm = new ACI_Model_SearchMock();
        $this->assertEquals($rank, $sm->normalizeRank($rank));
    }
    
    public function testNormalizeUnnormalizedRank()
    {
        $rank = 'genus';
        $sm = new ACI_Model_SearchMock();
        $this->assertEquals(
            'RANK_' . strtoupper($rank), $sm->normalizeRank($rank)
        );
    }
    
    public function testMinStrLenForHigherTaxaIsZero()
    {
        $rank = 'order'; //higher taxa
        $sm = new ACI_Model_SearchMock();
        $this->assertEquals($sm->getMinStrLen($rank, array()), 0);
        $this->assertEquals(
            $sm->getMinStrLen($rank, array('genus' => 'bla')), 0
        );
    }
    
    public function testMinStrLenForLowerTaxa()
    {
        $rank = 'species'; //lower taxa
        $sm = new ACI_Model_SearchMock();
        // if empty key must return 2
        $this->assertEquals($sm->getMinStrLen($rank, array()), 2);
        // if key has any value must return 0
        $this->assertEquals(
            $sm->getMinStrLen($rank, array('genus' => 'bla')), 0
        );
    }
    
    public function testGetSortParamsOfUnexistantSearch()
    {
        $this->assertFalse(
            ACI_Model_SearchMock::getSortParams('foo'),
            'An unexistant search must have no sort params'
        );
    }
    
    public function testGetSortParams()
    {
        $this->assertTrue(
            is_array(ACI_Model_SearchMock::getSortParams('scientific'))
        );
        $this->assertTrue(
            is_array(ACI_Model_SearchMock::getSortParams('common'))
        );
        $this->assertTrue(
            is_array(ACI_Model_SearchMock::getSortParams('distribution'))
        );
    }
    
    public function testGetDefaultSortParamOfUnexistantSearch()
    {
        $ms = new ACI_Model_SearchMock();
        $this->assertEquals(
            $ms->getDefaultSortParam('foo'), '',
            'An unexistant search must have no default sort param'
        );
    }
    
    public function testGetDefaultSortParam()
    {
        $ms = new ACI_Model_SearchMock();
        $this->assertGreaterThan(
            0, strlen($ms->getDefaultSortParam('scientific'))
        );
        $this->assertGreaterThan(
            0, strlen($ms->getDefaultSortParam('common'))
        );
        $this->assertGreaterThan(
            0, strlen($ms->getDefaultSortParam('distribution'))
        );
    }
    
    public function testWildcardHandling()
    {
        $ms = new ACI_Model_SearchMock();
        $this->assertEquals($ms->wildcardHandling('%'), '');
        $this->assertEquals($ms->wildcardHandling('*'), '%');
    }
    
    public function testWildcardHandlingInRegExpMatchWholeWords()
    {
        $ms = new ACI_Model_SearchMock();
        $this->assertEquals(
            $ms->wildcardHandlingInRegExp('str', true), '[[:<:]]str[[:>:]]'
        );
    }
    
    public function testWildcardHandlingInRegExpNoMatchWholeWords()
    {
        $ms = new ACI_Model_SearchMock();
        $this->assertEquals($ms->wildcardHandlingInRegExp('%', false), '.*');
    }
}