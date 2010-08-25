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
    protected $_name = 'taxon';
    protected $_primary = 'id';
    protected static $_numAcceptedNames;
    protected static $_numScientificNames;
    protected static $_numSpecies;
    protected static $_numInfraspecificTaxa;
    protected static $_numSynonyms;
    
    public function getIdByName($genus, $species, $infraspecies)
    {
        $select = $this->select();
        $select->from($this, array('record_id AS id'));
        if (!is_null($genus)) {
            $select->where('genus = ?', (string)$genus);
        }
        if (!is_null($species)) {
            $select->where('species = ?', (string)$species);
        }
        if (!is_null($infraspecies)) {
            $select->where('infraspecies = ?', (string)$infraspecies);
        }
        $rows = $this->fetchAll($select);
        return $rows;
    }
    
    public function count()
    {
        if (is_null(self::$_numScientificNames)) {
            $select = $this->select();
            $select->from($this, array('COUNT(1) AS total'));
            $rows = $this->fetchAll($select);
            self::$_numScientificNames = $rows[0]->total;
        }
        return self::$_numScientificNames;
    }
    
    public function countSynonyms()
    {
        if (is_null(self::$_numSynonyms)) {
            $select = $this->select();
            $select->from(array('s' => 'synonym'), array('COUNT(1) AS total'));
            $rows = $this->fetchAll($select);
            self::$_numSynonyms = $rows[0]->total;
        }
        return self::$_numSynonyms;
    }
    
    public function countAcceptedNames()
    {
        if (is_null(self::$_numAcceptedNames)) {
            $rows = $this->fetchAll($this->_getAcceptedNamesCount());
            self::$_numAcceptedNames = $rows[0]->total;
        }
        return self::$_numAcceptedNames;
    }
    
    public function countInfraspecificTaxa()
    {
        if (is_null(self::$_numInfraspecificTaxa)) {
            $select = $this->_getAcceptedNamesCount();
            $select->where('LENGTH(TRIM(infraspecies)) > 0');
            $rows = $this->fetchAll($select);
            self::$_numInfraspecificTaxa = $rows[0]->total;
        }
        return self::$_numInfraspecificTaxa;
    }
    
    public function countSpecies()
    {
        if (is_null(self::$_numSpecies)) {
            self::$_numSpecies = $this->countAcceptedNames() -
                $this->countInfraspecificTaxa();
        }
        return self::$_numSpecies;
    }
    
    protected function _getAcceptedNamesCount()
    {
        $select = $this->select();
        $select->from($this, array('COUNT(1) AS total'))->where(
            'sp2000_status_id IN (?)',
            array(
                ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                ACI_Model_Table_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME
            )
        )
        ->where('is_accepted_name = 1');
        
        return $select;
    }
}