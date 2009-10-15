<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_Databases
 * Databases table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_Databases extends Zend_Db_Table_Abstract
{
    protected $_name = 'databases';
    protected $_primary = 'record_id';
    
    public function get ($id)
    {
        $dbDetails = $this->find((int)$id);
        $res = $dbDetails->current();
        if (!$res) {
            return false;
        }
        return $this->_decorate($res->toArray());
    }
    
    public function getAll ($order = null)
    {
        $rowset = parent::fetchAll(null, $order);
        if (!$rowset) {
            return false;
        }
        $results = array();
        foreach ($rowset as $row) {
            $results[] = $this->_decorate($row->toArray());
        }
        return $results;
    }
    
    public function getImageFromName ($imageName)
    {
        return '/images/databases/' . 
            $this->getImagenameFromName($imageName) . '.jpg';
    }

    public function getThumbFromName ($imageName)
    {
        return '/images/databases/' . 
            $this->getImagenameFromName($imageName) . '.gif';
    }

    public function getUrlFromId ($id)
    {
        return '/details/database/id/' . $id;
    }    

    private function getImagenameFromName ($imageName)
    {
        return str_replace(' ', '_', $imageName);
    }
    
    protected function _decorate(array $row)
    {
        $row['image'] = $this->getImageFromName($row['database_name']);
        $row['thumb'] = $this->getThumbFromName($row['database_name']);
        $row['url'] = $this->getUrlFromId($row['record_id']);
        
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