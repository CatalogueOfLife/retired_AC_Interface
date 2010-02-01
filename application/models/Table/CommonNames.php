<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_CommonNames
 * common_names table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_CommonNames extends Zend_Db_Table_Abstract
{
    protected $_name = 'common_names';
    protected $_primary = 'record_id';
    
    public function count()
    {
        $select = $this->select();
        $select->from($this, array('COUNT(*) AS total'));
        $rows = $this->fetchAll($select);
        return($rows[0]->total);
    }
}