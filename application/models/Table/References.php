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
    protected $_name = 'references';
    protected $_primary = 'record_id';
    
    public function getByNameCode($nameCode)
    {
        $snr = new ACI_Model_Table_ScientificNameReferences();
        $refIds = $snr->get($nameCode);
        if(empty($refIds)) {
            return array();
        }
        $select = $this->select(true)->where(
           'record_id IN (' . implode(',', $refIds) . ')'
        );
        
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