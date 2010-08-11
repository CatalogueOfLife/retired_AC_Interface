<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_References
 * Reference table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_References extends Zend_Db_Table_Abstract
{
    protected $_name = 'reference';
    protected $_primary = 'id';
    
    public function getByTaxonId($taxon_id)
    {
        $snr = new ACI_Model_Table_ScientificNameReferences();
        $refIds = $snr->getByTaxonId($taxon_id);
        if (empty($refIds)) {
            return array();
        }
        $select = $this->select(true)->where(
            'id IN (' . implode(',', $refIds) . ')'
        )
        ->joinRight(
            array('rtt' => 'reference_to_taxon'),
            'reference.id = rtt.reference_id',
            array()
        )
        ->where('rtt.taxon_id = ?', $taxon_id);
        
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        return $data;
    }
    
    public function getBySynonymId($synonym_id)
    {
        $snr = new ACI_Model_Table_ScientificNameReferences();
        $refIds = $snr->getBySynonymId($synonym_id);
        if (empty($refIds)) {
            return array();
        }
        $select = $this->select(true)
        ->joinRight(
            array('rts' => 'reference_to_synonym'),
            'reference.id = rts.reference_id',
            array()
        )
        ->where(
            'id IN (' . implode(',', $refIds) . ') AND rts.synonym_id = ?', $synonym_id);
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        return $data;
    }
    
    public function get($id)
    {
        $res = $this->find((int)$id);
        $firstRef = $res->current();
        return $firstRef ? $firstRef->toArray() : false;
    }
}