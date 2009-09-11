<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Search
 * Search queries builder
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Search
{
    protected $_db;
    protected $_logger;
    
    const API_ROWSET_LIMIT = 1500;
    const ITEMS_PER_PAGE = 20;
    
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_db = $dbAdapter;
        $this->_logger = Zend_Registry::get('logger');
    }
    
    public function commonNames($searchKey, $matchWholeWords, $sort)
    {
        $selectCommon = $this->_selectCommonNames($searchKey, $matchWholeWords)
        ->order(
            array_merge(
                array(
                    ACI_Model_Search::getRightColumnName($sort)
                ),
                array('name')
            )
        );
        $this->_logger->debug($selectCommon->__toString());
        
        return $selectCommon;
    }
    
    public function taxa($searchKey, $matchWholeWords)
    {
        return $this->_selectTaxa($searchKey, $matchWholeWords);
    }
    
    public function all($searchKey, $matchWholeWords, $sort)
    {
        $selectAll = $this->_db->select()->union(
            array(
                $this->_selectTaxa(
                    $searchKey, $matchWholeWords
                )->reset('order'),
                $this->_selectCommonNames(
                    $searchKey, $matchWholeWords
                )->reset('order')
            )
        )
        ->order(
            array_merge(
                array(
                    ACI_Model_Search::getRightColumnName($sort)
                ),
                array('rank','name')
            )
        );
        
        $this->_logger->debug($selectAll->__toString());
        
        return $selectAll;
    }
    
    public static function getRightColumnName($columName)
    {
        $columMap = array(
            'name' => 'name',
            'rank' => 'rank',
            'status' => 'status',
            'db' => 'db_name',
            'scientificName' => 'accepted_species_name',
            'group' => 'kingdom'
        );
        return isset($columMap[$columName]) ?
            $columMap[$columName] : null;
    }
    
    /**
     * Builds the select query to search taxa by name
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @return Zend_Db_Select
     */
    protected function _selectTaxa($searchKey, $matchWholeWords)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $fields = 
            array(
                'id' => 'sn.record_id',
                'taxa_id' => 'tx.record_id',
                'rank' => new Zend_Db_Expr(
                    'CASE tx.taxon ' .
                    'WHEN "Kingdom" THEN ' .
                        ACI_Model_Taxa::RANK_KINGDOM . ' ' .
                    'WHEN "Phylum" THEN ' .
                        ACI_Model_Taxa::RANK_PHYLUM . ' ' .
                    'WHEN "Class" THEN ' .
                        ACI_Model_Taxa::RANK_CLASS . ' ' .
                    'WHEN "Order" THEN ' .
                        ACI_Model_Taxa::RANK_ORDER . ' ' .
                    'WHEN "Supefamily" THEN ' .
                        ACI_Model_Taxa::RANK_SUPERFAMILY . ' ' .
                    'WHEN "Family" THEN ' .
                        ACI_Model_Taxa::RANK_FAMILY . ' ' .
                    'WHEN "Genus" THEN ' .
                        ACI_Model_Taxa::RANK_GENUS . ' ' .
                    'WHEN "Species" THEN ' .
                        ACI_Model_Taxa::RANK_SPECIES . ' ' .
                    'WHEN "Infraspecies" THEN ' .
                        ACI_Model_Taxa::RANK_INFRASPECIES . ' ' .
                    'END'),
                'tx.name',
                'tx.name_code',
                'tx.is_accepted_name',
                'sn.author',
                'language' => new Zend_Db_Expr("''"),
                'accepted_species_id' => 'sna.record_id',
                'accepted_species_name' =>
                    "TRIM(CONCAT(IF(sna.genus IS NULL, '', sna.genus) " .
                    ", ' ', IF(sna.species IS NULL, '', sna.species), ' ', " .
                    "IF(sna.infraspecies IS NULL, '', sna.infraspecies)))",
                'accepted_species_author' => 'sna.author',
                'db_name' => 'db.database_name',
                'db_id' => 'db.record_id',
                'db_thumb' => 
                    'CONCAT(REPLACE(db.database_name, " ", "_"), ".gif")',
                'kingdom' => 'fm.kingdom',
                'status' => 'tx.sp2000_status_id'
            );
        
        if ($matchWholeWords) {
        
            $select->from(
                array(
                    'ss' => 'simple_search'
                ),
                $fields
            )
            ->join(
                array('tx' => 'taxa'),
                'ss.taxa_id = tx.record_id',
                array()
            )
            ->where(
                'ss.words = ? AND ' .
                'tx.is_species_or_nonsynonymic_higher_taxon = 1',
                $searchKey
            );
        } else {
            $select->from(
                array(
                    'tx' => 'taxa'
                ),
                $fields
            )
            ->where(
                'tx.name LIKE "%' . $searchKey . '%" AND ' .
                'tx.is_species_or_nonsynonymic_higher_taxon = 1'
            );
        }
           
        $select
        ->joinLeft(
            array('sn' => 'scientific_names'),
            'tx.name_code = sn.name_code',
            array()
        )
        ->joinLeft(
            array('fm' => 'families'),
            'sn.family_id = fm.record_id',
            array()
        )
        ->joinLeft(
            array('sna' => 'scientific_names'),
            'sna.accepted_name_code = sn.accepted_name_code AND ' .
            'sna.is_accepted_name = 1',
            array()
        )
        ->joinLeft(
            array('db' => 'databases'),
            'tx.database_id = db.record_id',
            array()
        )
        ->order(array('name', 'status'));
         
        return $select;
    }
    
    /**
     * Builds the select query to search common names for the common names
     * search functionality. The query may be also unioned afterwards with
     * scientific names results by the search/all functionality.
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @return Zend_Db_Select
     */
    protected function _selectCommonNames($searchKey, $matchWholeWords)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->distinct()->from(
            array(
                'cn' => 'common_names'
            ),
            array(
                'id' => new Zend_Db_Expr(0),
                'taxa_id' => 'cn.record_id',
                'rank' => new Zend_Db_Expr(
                    'IF(cn.is_infraspecies, "' .
                    ACI_Model_Taxa::RANK_INFRASPECIES . '", "' .
                    ACI_Model_Taxa::RANK_SPECIES . '")'
                ),
                'name' => 'cn.common_name',
                'cn.name_code',
                'is_accepted_name' => new Zend_Db_Expr(0),
                'sn.author',
                'cn.language',
                'accepted_species_id' => 'sn.record_id',
                'accepted_species_name' =>
                    "TRIM(CONCAT(IF(sn.genus IS NULL, '', sn.genus) " .
                    ", ' ', IF(sn.species IS NULL, '', sn.species), ' ', " .
                    "IF(sn.infraspecies IS NULL, '', sn.infraspecies)))",
                'accepted_species_author' => 'sn.author',
                'db_name' => 'db.database_name',
                'db_id' => 'db.record_id',
                'db_thumb' => 
                    'CONCAT(REPLACE(db.database_name, " ", "_"), ".gif")',
                'kingdom' => 'fm.kingdom',
                'status' => new Zend_Db_Expr(ACI_Model_Taxa::STATUS_COMMON_NAME)
            )
        )
        ->joinLeft(
            array('sn' => 'scientific_names'),
            'cn.name_code = sn.name_code',
            array()
        )
        ->joinLeft(
            array('fm' => 'families'),
            'sn.family_id = fm.record_id',
            array()
        )
        ->joinLeft(
            array('db' => 'databases'),
            'cn.database_id = db.record_id',
            array()
        );
         
        if ($matchWholeWords) {
            $select->where(
                'cn.common_name REGEXP "[[:<:]]' . $searchKey . '[[:>:]]"'
            );
        } else {
            $select
                ->where('cn.common_name LIKE "%' . $searchKey . '%"');
        }
        
        $select->group(array('name', 'language', 'accepted_species_id'));
         
        return $select;
    }
    
    /**
     * Returns the all the existing record names of a specific rank only
     * if the total is less than the constant API_ROWSET_LIMIT
     *
     * @return array
     */
    public function getRankEntries($rank, $name)
    {
        if (strlen($name) < 2) {
            return array();
        }
        
        $select = new Zend_Db_Select($this->_db);
        $total = $this->_getRankCount($rank, $name);
        
        $this->_logger->debug("$total results found for $rank \"$name\"");

        if ($total > self::API_ROWSET_LIMIT) {
            return array();
        }
        
        $select->distinct()
               ->from(array('hard_coded_taxon_lists'), array('name'))
               ->where('rank = ? AND name LIKE "%'. $name .'%"', $rank)
               ->order(
                   array(
                       new Zend_Db_Expr('INSTR(name, "' . $name . '")'),
                       'name'
                   )
               );
        return $select->query()->fetchAll();
    }
    
   /**
     * Returns the number of different existing record names of a specific rank
     *
     * @return int
     */
    protected function _getRankCount($rank, $name)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select
            ->from(
                array('hard_coded_taxon_lists'),
                array('total' => new Zend_Db_Expr('COUNT(DISTINCT name)'))
            )
            ->where('rank = ? AND name LIKE "%'. $name .'%"', $rank);
            
        return $select->query()->fetchColumn();
    }
}