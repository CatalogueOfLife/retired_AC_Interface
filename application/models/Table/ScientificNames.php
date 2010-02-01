<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_ScientificNames
 * scientific_names table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_ScientificNames extends Zend_Db_Table_Abstract
{
    protected $_name = 'scientific_names';
    protected $_primary = 'record_id';
    
    public function count()
    {
        $select = $this->select();
        $select->from($this, array('COUNT(*) AS total'));
        $rows = $this->fetchAll($select);
        return($rows[0]->total);
    }
    
    public function countSynonyms()
    {
        $select = $this->select();
        $select->from($this, array('COUNT(*) AS total'))->where(
            'sp2000_status_id IN (?, ?, ?)',
            array(
                ACI_Model_Table_Taxa::STATUS_AMBIGUOUS_SYNONYM, 
                ACI_Model_Table_Taxa::STATUS_SYNONYM,
                ACI_Model_Table_Taxa::STATUS_MISAPPLIED_NAME
            )
        );
        $rows = $this->fetchAll($select);
        return($rows[0]->total);
    }
    
    public function countAcceptedNames()
    {   
        $rows = $this->fetchAll($this->_getAcceptedNamesCount());
        return($rows[0]->total);
    }
    
    public function countInfraspecificTaxa()
    {
        $select = $this->_getAcceptedNamesCount();
        $select->where('LENGTH(infraspecies) > 0');
        $rows = $this->fetchAll($select);
        return($rows[0]->total);
    }
    
    protected function _getAcceptedNamesCount()
    {
        $select = $this->select();
        $select->from($this, array('COUNT(*) AS total'))->where(
            'sp2000_status_id IN (?, ?)',
            array(
                ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME, 
                ACI_Model_Table_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME
            )
        )
        ->where('is_accepted_name = 1');
        
        return $select;        
    }
}