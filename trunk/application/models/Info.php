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
            'names' => 'number_of_species'
        );
        return isset($columMap[$columName]) ?
            $columMap[$columName] : null;
    }
    
    public function getSourceDatabases($order =  'source', $direction = 'asc')
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('dsdd' => '_source_database_details'),
            array(
                'id' => 'dsdd.id',
                'name' => 'dsdd.full_name',
                'abbreviation' => 'dsdd.short_name',
                'taxa' => 'dsdd.english_name',
                'total_species' => 'dsdd.number_of_species',
                'is_new' => 'dsdd.is_new'
            )
        )
        ->order(
            array(
                'dsdd.' . $this->getRightColumnName($order) . ' ' .
                    strtoupper($direction)
            )
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
        $stats['databases'] = number_format($totals->getNumSourceDatabases());
        // Number of new databases
        $stats['new_databases'] = number_format($totals->getNumNewSourceDatabases());
        // Number of common names
        $stats['common_names'] = number_format($totals->getNumCommonNames());
        // Number of synonyms
        $stats['synonyms'] = number_format($totals->getNumSynonyms());
        // Number of infraspecific taxa
        $stats['infraspecific_taxa'] = 
            number_format($totals->getNumInfraspecificTaxa());
        // Number of accepted names
        $stats['species'] = number_format($totals->getNumSpecies());
            
        return $stats;
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
                    't2.total_species',
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
}