<?php
require_once 'AModel.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Info
 * Search queries builder
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Info extends AModel
{
    /**
     * Maps the interface column names to the real names in the database
     *
     * @param string $columName
     * @return string
     */
    public static function getRightColumnName($columName)
    {
        $columMap = array(
            'source' => 'full_name',
            'group' => 'english_name',
            'extant' => 'number_of_species',
            'extinct' => 'number_of_extinct_species'
        );
        return isset($columMap[$columName]) ?
            $columMap[$columName] : null;
    }

    public function getSourceDatabases($order = 'source', $direction = 'asc')
    {
        $sortOrder = $this->getRightColumnName($order);
        $numberOfSpecies = 'number_of_species';
        // Ruud 13-02-15: subtract extinct species if fossils has been disabled
        if ($this->_moduleEnabled('fossils')) {
            $numberOfSpecies = '(number_of_species - number_of_extinct_species)';
            $sortOrder = ($sortOrder == 'number_of_species') ? $numberOfSpecies : $sortOrder;
        }

        $select = new Zend_Db_Select($this->_db);
        $select->from(
            '_source_database_details',
            array(
                'id' => 'id',
                'name' => 'full_name',
                'abbreviation' => 'short_name',
                'taxa' => 'english_name',
                'total_species' => $numberOfSpecies,
                'total_extinct_species' => 'number_of_extinct_species',
                'is_new' => 'is_new'
            )
        )
        ->order(
            array($sortOrder . ' ' . strtoupper($direction))
        );
        $res = $select->query()->fetchAll();
        $total = count($res);
        $this->_logger->debug("$total source databases");
        return $res;
    }

    /**
     * Returns the totals used as statistics in the info pages either from the
     * database or from the cache
     *
     * @return array
     */
    public function getStatistics()
    {
        $cacheKey = 'statistics';
        $res = $this->_fetchFromCache($cacheKey);
        if (!$res) {
            $res = $this->_calculateStatistics();
            $this->_storeInCache($res, $cacheKey);
        }
        return $res;
    }

    /**
     * Queries the database to collect the following statistics:
     * databases => Total count of source databases
     * common_names => Total count of common names
     * synonyms => Total count of synonyms
     * infraspecific_taxa => Total count of infraspecific taxa
     * species => Total count of species
     *
     * @return array
     */
    protected function _calculateStatistics()
    {
        $stats = array();

        //Totals
        $totals = new ACI_Model_Table_Totals();
        $totals->countTotals();
        // Number of databases
        $stats['databases'] = $totals->getNumSourceDatabases();
        // Number of new databases
        $stats['new_databases'] = $totals->getNumNewSourceDatabases();
        // Number of common names
        $stats['common_names'] = $totals->getNumCommonNames();
        // Number of synonyms
        $stats['synonyms'] = $totals->getNumSynonyms();
        // Number of infraspecific taxa
        $stats['infraspecific_taxa'] = $totals->getNumInfraspecificTaxa();
        // Number of accepted names
        $stats['species'] = $totals->getNumSpecies();
        // Number of genera
        $stats['genera'] = $totals->getNumGenera();
        // Number of families
        $stats['families'] = $totals->getNumFamilies();
        // Number of orders
        $stats['orders'] = $totals->getNumOrders();
        // Number of classes
        $stats['classes'] = $totals->getNumClasses();
        // Number of phyla
        $stats['phyla'] = $totals->getNumPhyla();
        // Number of kingdoms
        $stats['kingdoms'] = $totals->getNumKingdoms();

        // Fossils
        $stats['extinct_infraspecific_taxa'] = $totals->getNumExtinctInfraspecificTaxa();
        $stats['extinct_species'] = $totals->getNumExtinctSpecies();
        $stats['living_species'] = $stats['species'] - $stats['extinct_species'];
        $stats['living_infraspecific_taxa'] = $stats['infraspecific_taxa'] -
            $stats['extinct_infraspecific_taxa'];

        return array_map('number_format', $stats);
    }

    public function getSpeciesTotals() {
        $cacheKey = 'totals';
        $res = false;
        $res = $this->_fetchFromCache($cacheKey);
        if (!$res) {
            $select = new Zend_Db_Select($this->_db);
            $select->from(
                array('t1' => '_taxon_tree'),
                array(
                    't2.taxon_id',
                    't2.rank',
                    'name' => 't2.name',
                    'kingdom' => 't1.name',
                    't2.total_species_estimation',
                    'total_species' => 't2.total_species_extant',
                    'source' => 't2.estimate_source'
                )
            )
            ->joinLeft(
                array('t2' => '_taxon_tree'),
                't2.parent_id = t1.taxon_id',
                array()
                )
            ->where('t2.rank = ?', 'phylum')
            ->order(
                array(
                    'kingdom', 'name'
                )
            );
            $res = $select->query()->fetchAll();
            $this->_storeInCache($res, $cacheKey);
        }
        return $res;
    }

    public function getCitations ()
    {
        $select = new Zend_Db_Select($this->_db);
        $stmt = $this->_db->query($select->from('_credits'));
        $credits = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

        foreach ($credits as $i => $credit) {
            $find = array('[year]', '[edition]');
            $replace = array(date("Y"), $credit['edition']);
            $credits[$i]['citation'] = str_replace($find, $replace, $credit['citation']);
        }

        $select = new Zend_Db_Select($this->_db);
        $select->from('_source_database_details')->order('short_name');
        $stmt = $this->_db->query($select);
        $gsds = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

        return array(
            'credits' => $credits,
            'gsds' => $gsds
        );
    }

}
