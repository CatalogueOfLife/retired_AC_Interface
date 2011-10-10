<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_Regions
 * Regions table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_Regions extends Zend_Db_Table_Abstract
{
    protected $_name = 'region';
    protected $_primary = 'id';
    
    public function getRegion($id)
    {
        $select = $this->select();
        $select->from(
            $this,
            array('id', 'region_standard_id', 'name', 'polygon')
        )->where('region.id = ?', $id);

        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        return $data[0];
    }

    public function getRegions($regionStandardId)
    {
        $select = $this->select();
        $select->from(
            $this,
            array('id', 'region_standard_id', 'name')
        )->where('region.region_standard_id = ?', $regionStandardId)
        ->order('region.name');

        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        return $data;
    }
}