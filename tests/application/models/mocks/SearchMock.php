<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_SearchMock
 * Mock of the ACI_Model_Search that provides with public access to protected 
 * methods for testing purposes
 *
 * @category    ACI
 * @package     tests
 * @subpackage  mocks
 *
 */
class ACI_Model_SearchMock extends ACI_Model_Search
{
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
        $this->_logger = Zend_Registry::get('logger');
    }
    public function normalizeRank($rank)
    {
        return $this->_normalizeRank($rank);
    }
    
    public function getMinStrLen($rank, array $key)
    {
        return $this->_getMinStrLen($rank, $key);
    }
    
    public static function getSortParams($action)
    {
        return parent::_getSortParams($action);
    }
    
    public function wildcardHandling($searchString)
    {
        return $this->_wildcardHandling($searchString);
    }
    
    public function wildcardHandlingInRegExp($searchString,
        $matchWholeWords = true)
    {
        return $this->_wildcardHandlingInRegExp(
            $searchString, $matchWholeWords
        );  
    }
}