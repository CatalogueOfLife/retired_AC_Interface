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
    protected $_name = 'source_database';
    protected $_primary = 'id';
    protected static $_numDatabases;
    protected static $_numDatabasesNew;
    protected static $_numDatabasesWithAcceptedNames;
    
    public function get($id)
    {
        $dbDetails = $this->find((int)$id);
        
        $res = $dbDetails->current();
        if (!$res) {
            return false;
        }
        return $this->_decorate($res->toArray());
    }
    
    public function getAll($order = null)
    {
        $rowset = $this->fetchAll(null, $order);
        if (!$rowset) {
            return false;
        }
        $results = array();
        foreach ($rowset as $row) {
            $results[] = $this->_decorate($row->toArray());
        }
        unset($rowset);
        return $results;
    }
    
    public function count()
    {
        if (is_null(self::$_numDatabases)) {
            $select = $this->select();
            $select->from($this, array('COUNT(1) AS total'));
            $rows = $this->fetchAll($select);
            self::$_numDatabases = $rows[0]->total;
        }
        return self::$_numDatabases;
    }
    
    public function countNew()
    {
        if (is_null(self::$_numDatabasesNew)) {
            $select = $this->select();
            $select->from(
                $this, array('COUNT(1) AS total')
            )->where('is_new');
            $rows = $this->fetchAll($select);
            self::$_numDatabasesNew = $rows[0]->total;
        }
        return self::$_numDatabasesNew;
    }
    
    public function countWithAcceptedNames()
    {
        if (is_null(self::$_numDatabasesWithAcceptedNames)) {
            $select = $this->select();
            $select->from(
                $this, array('COUNT(1) AS total')
            )->where('accepted_species_names > 0');
            $rows = $this->fetchAll($select);
            self::$_numDatabasesWithAcceptedNames = $rows[0]->total;
        }
        return self::$_numDatabasesWithAcceptedNames;
    }
    
    protected function _getImageFromName($imageName)
    {
        return '/images/databases/' .
            $this->_getImagenameFromName($imageName) . '.png';
    }

    protected function _getThumbFromName($imageName)
    {
        return '/images/databases/' .
            $this->_getImagenameFromName($imageName) . '.gif';
    }

    protected function _getUrlFromId ($id)
    {
        return '/details/database/id/' . $id;
    }

    protected function _getImagenameFromName($imageName)
    {
        return str_replace(' ', '_', $imageName);
    }
    
    protected function _decorate(array $row)
    {
        $row['image'] = $this->_getImageFromName($row['abbreviated_name']);
        $row['thumb'] = $this->_getThumbFromName($row['abbreviated_name']);
        $row['url'] = $this->_getUrlFromId($row['id']);
        return $row;
    }
}