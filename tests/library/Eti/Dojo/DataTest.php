<?php
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * Annual Checklist Interface
 *
 * Class Eti_Dojo_DataTest
 * Unit tests to the Eti_Dojo_Data class
 *
 * @category    ACI
 * @package     tests
 * @subpackage  library/Eti
 *
 */
class Eti_Dojo_DataTest extends PHPUnit_Framework_TestCase
{
    protected $_etiDojoData;
    
    /**
     * Set up: initializes the Eti_Dojo_Data object
     */
    protected function setUp() {
        $this->_etiDojoData = new Eti_Dojo_Data('id');
    }
    
    /**
     * Asserts that the addItem method inserts an item into the object
     */
    public function testAddItem()
    {
        $item = array(
            $this->_etiDojoData->getIdentifier() => 'unique_id',
            'data' => 'item data'
        );
        $this->_etiDojoData->addItem($item);
        $this->assertEquals(count($this->_etiDojoData), 1);
        $this->assertEquals(
            $this->_etiDojoData->getItem(
                $item[$this->_etiDojoData->getIdentifier()]
            ), $item
        );
    }
    
    /**
     * Asserts that the data is encoded to UTF-8 before stored in the object
     * Note that this source code file is encoded with ISO-8859-1
     */
    public function testAddNonUtf8EncodedItem()
    {
        $item = array(
            $this->_etiDojoData->getIdentifier() => 'unique_id',
            'data' => 'non-utf8 data: ï'
        );
        $this->_etiDojoData->addItem($item);
        $insertedItem = $this->_etiDojoData->getItem(
            $item[$this->_etiDojoData->getIdentifier()]
        );
        $this->assertEquals(
            utf8_encode($item['data']), $insertedItem['data']
        );
    }
    
    /**
     * Asserts that an item with a null identifier is silently not inserted into
     * the object
     */
    public function testAddItemWithNullIdentifier()
    {
        $item = array(
            $this->_etiDojoData->getIdentifier() => null,
            'data' => 'item data'
        );
        $this->_etiDojoData->addItem($item);
        $this->assertEquals(
            count($this->_etiDojoData),
            0,
            "Items with null identifier must not be added!"
        );
    }
    
    /**
     * Tear down: resets the Eti_Dojo_Data object
     */
    public function tearDown() {
        $this->_etiDojoData = null;
    }
}