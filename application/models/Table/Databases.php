<?php
class ACI_Model_Table_Databases extends Zend_Db_Table_Abstract
{
    protected $_name = 'databases';
    protected $_primary = 'record_id';
    
    public function get ($id)
    {
        $dbDetails = $this->find((int)$id);
        $res = $dbDetails->current();
        if(!$res) {
            return false;
        }
        return $this->_decorate($res->toArray());
    }
    
    public function getAll ($order = null)
    {
        $rowset = parent::fetchAll(null, $order);
        if(!$rowset) {
            return false;
        }
        $results = array();
        foreach($rowset as $row)
        {
            $results[] = $this->_decorate($row->toArray());
        }
        return $results;
    }
    
    protected function _decorate(array $row)
    {
        $imageName = str_replace(' ', '_', $row['database_name']);
        $row['image'] = '/images/databases/' . $imageName . '.jpg';
        $row['thumb'] = '/images/databases/' . $imageName . '.gif';
        $row['url'] = '/details/database/id/' . $row['record_id'];
        $row['name'] = $row['database_name_displayed'];
        $row['label'] = $row['database_name'];
        $row['accepted_species_names'] =
            number_format($row['accepted_species_names']);
        $row['accepted_infraspecies_names'] =
            number_format($row['accepted_infraspecies_names']);
        $row['common_names'] =
            number_format($row['common_names']);
        $row['total_names'] =
            number_format($row['total_names']);
        return $row;
    }
}