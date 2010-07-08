<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_ScientificNameReferences
 * Scientific names - references relationship table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_ScientificNameReferences extends Zend_Db_Table_Abstract
{
    protected $_name = 'scientific_name_references';
    protected $_primary = 'record_id';
    
    public function get ($nameCode)
    {
        $select = $this->select(true)
            ->where(
                'name_code = ? AND (reference_type <> "ComNameRef" OR reference_type IS NULL)', $nameCode
            );
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll();
        return $this->_filterData($data);
    }
    
    protected function _filterData($data)
    {
        $filteredData = array();
        foreach ($data as $d) {
            $filteredData[] = $d['reference_id'];
        }
        return $filteredData;
    }
}