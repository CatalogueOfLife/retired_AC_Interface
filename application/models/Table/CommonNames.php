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
    protected static $_numCommonNames;
    
    public function count()
    {
        if (is_null(self::$_numCommonNames)) {
            $select = $this->select();
            $select->from($this, array('COUNT(1) AS total'));
            $rows = $this->fetchAll($select);
            self::$_numCommonNames = $rows[0]->total;
        }
        return self::$_numCommonNames;
    }
}