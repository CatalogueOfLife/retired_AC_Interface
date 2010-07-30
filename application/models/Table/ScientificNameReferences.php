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
    protected $_name = 'reference_to_taxon';
    protected $_primary = 'taxon_id';
    
    public function get ($taxon_id)
    {
        $select = $this->select(true)
            ->joinRight(
                array('r' => 'reference'),
                'reference_id = r.id',
                array()
            )
            ->where(
                'taxon_id = ?', $taxon_id
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