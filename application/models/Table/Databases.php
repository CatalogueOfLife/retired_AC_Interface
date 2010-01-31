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
        $rowset = $this->fetchAll(null, $order);
        if (!$rowset) {
            return false;
        }
        $results = array();
        foreach($rowset as $row) {
            $results[] = $this->_decorate($row->toArray());
        }
        unset($rowset);
        return $results;
    }
    
    protected function _getImageFromName ($imageName)
    {
        return '/images/databases/' .
            $this->_getImagenameFromName($imageName) . '.png';
    }

    protected function _getThumbFromName ($imageName)
    {
        return '/images/databases/' .
            $this->_getImagenameFromName($imageName) . '.gif';
    }

    protected function _getUrlFromId ($id)
    {
        return '/details/database/id/' . $id;
    }

    protected function _getImagenameFromName ($imageName)
    {
        return str_replace(' ', '_', $imageName);
    }
    
    protected function _decorate(array $row)
    {
        $row['image'] = $this->_getImageFromName($row['database_name']);
        $row['thumb'] = $this->_getThumbFromName($row['database_name']);
        $row['url'] = $this->_getUrlFromId($row['record_id']);
        return $row;
    }
}