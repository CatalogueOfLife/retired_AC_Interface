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
    protected $_name = '_totals';
    protected $_primary = 'description';
    protected static $_numCommonNames;
    protected static $_numAcceptedNames;
    protected static $_numScientificNames;
    protected static $_numSpecies;
    protected static $_numInfraspecificTaxa;
    protected static $_numSynonyms;
    protected static $_numSourceDatabases;
    protected static $_numNewSourceDatabases;
    protected static $_numExtinctSpecies;
    protected static $_numExtinctInfraspecificTaxa;
    protected static $_numGenera;
    protected static $_numFamilies;
    protected static $_numOrders;
    protected static $_numClasses;
    protected static $_numPhyla;
    protected static $_numKingdoms;

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
                    case "extinct_species":
                        self::$_numExtinctSpecies = $row->total;
                        break;
                    case "extinct_infraspecies":
                        self::$_numExtinctInfraspecificTaxa = $row->total;
                        break;
                    case "genera":
			self::$_numGenera = $row->total;
                        break;
                    case "families":
                        self::$_numFamilies = $row->total;
                        break;
                    case "orders":
                        self::$_numOrders = $row->total;
                        break;
                    case "classes":
                        self::$_numClasses = $row->total;
                        break;
                    case "phyla":
                        self::$_numPhyla = $row->total;
                        break;
                    case "kingdoms":
                        self::$_numKingdoms = $row->total;
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
        if (is_null(self::$_numAcceptedNames)) {
            $this->countTotals();
        }
        return self::$_numAcceptedNames;
    }

    public function getNumScientificNames()
    {
        if (is_null(self::$_numScientificNames)) {
            $this->countTotals();
        }
        return self::$_numScientificNames;
    }

    public function getNumSpecies()
    {
        if (is_null(self::$_numSpecies)) {
            $this->countTotals();
        }
        return self::$_numSpecies;
    }

    public function getNumInfraspecificTaxa()
    {
        if (is_null(self::$_numInfraspecificTaxa)) {
            $this->countTotals();
        }
        return self::$_numInfraspecificTaxa;
    }

    public function getNumSynonyms()
    {
        if (is_null(self::$_numSynonyms)) {
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

    public function getNumExtinctSpecies()
    {
        if (is_null(self::$_numExtinctSpecies)) {
            $this->countTotals();
        }
        return self::$_numExtinctSpecies;
    }

    public function getNumExtinctInfraspecificTaxa()
    {
        if (is_null(self::$_numExtinctInfraspecificTaxa)) {
            $this->countTotals();
        }
        return self::$_numExtinctInfraspecificTaxa;
    }

    public function getNumGenera()
    {
        if (is_null(self::$_numGenera)) {
            $this->countTotals();
        }
        return self::$_numGenera;
    }

    public function getNumFamilies()
    {
        if (is_null(self::$_numFamilies)) {
            $this->countTotals();
        }
        return self::$_numFamilies;
    }

    public function getNumOrders()
    {
        if (is_null(self::$_numOrders)) {
            $this->countTotals();
        }
        return self::$_numOrders;
    }

    public function getNumClasses()
    {
        if (is_null(self::$_numClasses)) {
            $this->countTotals();
        }
        return self::$_numClasses;
    }

    public function getNumPhyla()
    {
        if (is_null(self::$_numPhyla)) {
            $this->countTotals();
        }
        return self::$_numPhyla;
    }

    public function getNumKingdoms()
    {
        if (is_null(self::$_numKingdoms)) {
            $this->countTotals();
        }
        return self::$_numKingdoms;
    }

}
