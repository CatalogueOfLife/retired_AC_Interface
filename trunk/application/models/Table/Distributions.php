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
class ACI_Model_Table_Distributions extends Zend_Db_Table_Abstract
{
    protected $_name = 'distribution';
    protected $_primary = array('taxon_detail_id','region_id');
    
    public function getRegionsByTaxonId($taxonId,$rank)
    {
    	$select = $this->select();
    	$select
    	->from(
            $this,
            array('region_id')
        )->where(
        	'dsd.'.($rank == '' ? 'kingdom' : $rank).'_id = ?', $taxonId
        )->joinLeft(
        	array('dsd' => '_species_details'),
			'distribution.taxon_detail_id = dsd.taxon_id',
            array()
        );
        $stmt = $this->_db->query($select);
        $rows = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        $temp = array();
        foreach($rows as $row) {
        	$temp[$row['region_id']] = $row['region_id'];
        }
        ksort($temp);
        $data = array();
        foreach($temp as $row) {
        	$data[] = array('region_id' => $row);
        }
        return $data;
    }
}