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
    protected $_name = 'reference';
    protected $_primary = 'id';
    
    public function getByTaxonId ($taxon_id)
    {
        $select = $this->select(true)
            ->joinRight(
                array('rtt' => 'reference_to_taxon'),
                'rtt.reference_id = reference.id',
                array()
            )
            ->where(
                'rtt.taxon_id = ?', $taxon_id
            );
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll();
        return $this->_filterData($data);
    }
    
    public function getBySynonymId ($synonym_id)
    {
        $select = $this->select(true)
            ->joinRight(
                array('rts' => 'reference_to_synonym'),
                'rts.reference_id = reference.id',
                array()
            )
            ->where(
                'rts.synonym_id = ?', $synonym_id
            );
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll();
        return $this->_filterData($data);
    }
    
    protected function _filterData($data)
    {
        $filteredData = array();
        foreach ($data as $d) {
            $filteredData[] = $d['id'];
        }
        return $filteredData;
    }
}