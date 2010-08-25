<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_Totals
 * totals table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_Totals extends Zend_Db_Table_Abstract
{
    protected $_name = 'totals';
    protected $_primary = 'description';
    protected static $_numCommonNames;
    protected static $_numAcceptedNames;
    protected static $_numScientificNames;
    protected static $_numSpecies;
    protected static $_numInfraspecificTaxa;
    protected static $_numSynonyms;
    protected static $_numSourceDatabases;
    protected static $_numNewSourceDatabases;
    
    public function countTotals()
    {
        if (is_null(self::$_numCommonNames)) {
            $select = $this->select();
            $select->from($this);
            $rows = $this->fetchAll($select);
            foreach($rows as $row) {
                switch($row->description) {
                    case "common_names":
                        self::$_numCommonNames = $row->total;
                        break;
                    case "infraspecies":
                        self::$_numInfraspecificTaxa = $row->total;
                        break;
                    case "species":
                        self::$_numSpecies = $row->total;
                        break;
                    case "scientific_names":
                        self::$_numScientificNames = $row->total;
                        break;
                    case "synonyms":
                        self::$_numSynonyms = $row->total;
                        break;
                    case "taxon":
                        self::$_numAcceptedNames = $row->total;
                        break;
                    case "source_databases":
                        self::$_numSourceDatabases = $row->total;
                        break;
                    case "new_source_databases":
                        self::$_numNewSourceDatabases = $row->total;
                        break;
                }
            }
        }
    }
    
    public function getNumCommonNames()
    {
        if (is_null(self::$_numCommonNames)) {
            $this->countTotals();
        }
        return self::$_numCommonNames;
    }

    public function getNumAcceptedNames()
    {
        if (is_null(self::$_numCommonNames)) {
            $this->countTotals();
        }
        return self::$_numAcceptedNames;
    }

    public function getNumScientificNames()
    {
        if (is_null(self::$_numCommonNames)) {
            $this->countTotals();
        }
        return self::$_numScientificNames;
    }

    public function getNumSpecies()
    {
        if (is_null(self::$_numCommonNames)) {
            $this->countTotals();
        }
        return self::$_numSpecies;
    }

    public function getNumInfraspecificTaxa()
    {
        if (is_null(self::$_numCommonNames)) {
            $this->countTotals();
        }
        return self::$_numInfraspecificTaxa;
    }

    public function getNumSynonyms()
    {
        if (is_null(self::$_numCommonNames)) {
            $this->countTotals();
        }
        return self::$_numSynonyms;
    }

    public function getNumSourceDatabases()
    {
        if (is_null(self::$_numSourceDatabases)) {
            $this->countTotals();
        }
        return self::$_numSourceDatabases;
    }

    public function getNumNewSourceDatabases()
    {
        if (is_null(self::$_numNewSourceDatabases)) {
            $this->countTotals();
        }
        return self::$_numNewSourceDatabases;
    }
}