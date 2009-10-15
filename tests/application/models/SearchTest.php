<?php
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
}