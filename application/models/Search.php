<?php
require_once 'AModel.php';
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
class ACI_Model_Search extends AModel
{
    const ITEMS_PER_PAGE = 20;
    const API_ROWSET_LIMIT = 2000;
    protected static $_apiMinStrLen = array(
        'kingdom' => 0,
        'genus' => 1,
        'species' => 1,
        'infraspecies' => 1
    );
    
    /**
     * Returns the final query (sorted) to search for common names
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @param string $sort
     * @return Zend_Db_Select
     */
    public function commonNames($searchKey, $matchWholeWords, $sort)
    {
        return $this->_selectCommonNames($searchKey, $matchWholeWords)
        ->order(
            array_merge(
                array(
                    self::getRightColumnName($sort)
                ),
                array('name')
            )
        );
    }
    
    /**
     * Returns the final query to search for taxa
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @return Zend_Db_Select
     */
    public function taxa($searchKey, $matchWholeWords)
    {
        return $this->_selectTaxa($searchKey, $matchWholeWords);
    }
    
    /**
     * Returns the final query (sorted) to search for scientific names
     *
     * @param string $searchKey
     * @param string $sort
     * @return Zend_Db_Select
     */
    public function scientificNames(array $key, $sort)
    {
        return $this->_selectScientificNames($key)
        ->order(
            array_merge(
                array(
                    self::getRightColumnName($sort)
                ),
                array('name')
            )
        );
    }
    
    /**
     * Returns the final query (sorted) to search for distributions
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @param string $sort
     * @return Zend_Db_Select
     */
    public function distributions($searchKey, $matchWholeWords, $sort)
    {
        return $this->_selectDistributions($searchKey, $matchWholeWords)
        ->order(
            array_merge(
                array(
                    self::getRightColumnName($sort)
                ),
                array('distribution')
            )
        );
    }
    
    /**
     * Returnes a Zend_Db_Select object joining and sorting the scientific and
     * common names search queries
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @param string $sort
     * @return Zend_Db_Select
     */
    public function all($searchKey, $matchWholeWords, $sort)
    {
        return $this->_db->select()->union(
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
                    self::getRightColumnName($sort)
                ),
                array('rank','name')
            )
        );
    }
    
    /**
     * Maps the sorting parameters to the real field names in the database
     *
     * @param string $columName
     * @return string | null
     */
    public static function getRightColumnName($columName)
    {
        $columMap = array(
            'name' => 'name',
            'rank' => 'rank',
            'status' => 'status',
            'db' => 'db_name',
            'scientificName' => 'accepted_species_name',
            'group' => 'kingdom',
            'distribution' => 'distribution'
        );
        return isset($columMap[$columName]) ?
            $columMap[$columName] : null;
    }
    
    /**
     * Search by distribution query
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @return Zend_Db_Select
     */
    protected function _selectDistributions($searchKey, $matchWholeWords)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->from(
            array('ds' => 'distribution'),
            array('ds.distribution')
        )
        ->join(
            array('sn' => 'scientific_names'),
            'sn.name_code = ds.name_code',
            array(
                'sn.genus',
                'sn.species',
                'sn.infraspecies_marker',
                'sn.infraspecies',
                'sn.author'
            )
        )
        ->join(
            array('fm' => 'families'),
            'sn.family_id = fm.record_id',
            array('fm.kingdom')
        )
        ->join(
            array('db' => 'databases'),
            'sn.database_id = db.record_id',
            array(
                'db_name' => 'db.database_name',
                'db_id' => 'db.record_id',
                'db_thumb' =>
                    'CONCAT(REPLACE(db.database_name, " ", "_"), ".gif")'
            )
        )
        ->where('ds.distribution LIKE ?', '%' . $searchKey . '%');
        
        if ($matchWholeWords) {
            $wordDelimiterChars = '[ \.\"\'\(\),;:-]';
            $select->where(
                'ds.distribution = ?
                OR ds.distribution REGEXP "^' . $searchKey .
                    $wordDelimiterChars . '+.*$" = 1
                OR ds.distribution REGEXP "^.*' . $wordDelimiterChars . '+' .
                    $searchKey . '$" = 1
                OR ds.distribution REGEXP "^.*' . $wordDelimiterChars . '+' .
                    $searchKey . $wordDelimiterChars . '+.*$" = 1',
                $searchKey
            );
        }
        return $select;
    }
    
    /**
     * Returns the fields needed to display the results of the main search
     * queries (common names, scientific names and the combination of both)
     *
     * @return array
     */
    protected function _getFields() {
        
        $fields =
            array(
                'id' => 'sn.record_id',
                'taxa_id' => 'tx.record_id',
                'rank' => new Zend_Db_Expr(
                    'CASE tx.taxon ' .
                    'WHEN "Kingdom" THEN ' .
                        ACI_Model_Table_Taxa::RANK_KINGDOM . ' ' .
                    'WHEN "Phylum" THEN ' .
                        ACI_Model_Table_Taxa::RANK_PHYLUM . ' ' .
                    'WHEN "Class" THEN ' .
                        ACI_Model_Table_Taxa::RANK_CLASS . ' ' .
                    'WHEN "Order" THEN ' .
                        ACI_Model_Table_Taxa::RANK_ORDER . ' ' .
                    'WHEN "Supefamily" THEN ' .
                        ACI_Model_Table_Taxa::RANK_SUPERFAMILY . ' ' .
                    'WHEN "Family" THEN ' .
                        ACI_Model_Table_Taxa::RANK_FAMILY . ' ' .
                    'WHEN "Genus" THEN ' .
                        ACI_Model_Table_Taxa::RANK_GENUS . ' ' .
                    'WHEN "Species" THEN ' .
                        ACI_Model_Table_Taxa::RANK_SPECIES . ' ' .
                    'WHEN "Infraspecies" THEN ' .
                        ACI_Model_Table_Taxa::RANK_INFRASPECIES . ' ' .
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
            
        return $fields;
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
        
        if ($matchWholeWords) {
        
            $select->from(
                array(
                    'ss' => 'simple_search'
                ),
                $this->_getFields()
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
                $this->_getFields()
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
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . '", "' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . '")'
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
                'status' => new Zend_Db_Expr(
                    ACI_Model_Table_Taxa::STATUS_COMMON_NAME
                )
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
     * Search for scientific names query
     *
     * @param array $key
     * @return Zend_Db_Select
     */
    protected function _selectScientificNames(array $key)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->from(
                array(
                    'sn' => 'scientific_names'
                ),
                $this->_getFields()
            )
        ->where('tx.is_species_or_nonsynonymic_higher_taxon = 1');
            
        foreach($key as $rank => $name) {
            if(trim($name) != '') {
                if($this->taxaExists($rank, $name)) {
                    $select->where('sn.' . $rank . ' = ?', $name);
                }
                else {
                    $select->where('sn.' . $rank . ' LIKE "%' . $name . '%"');
                }
            }
        }
           
        $select
        ->joinLeft(
            array('tx' => 'taxa'),
            'sn.name_code = tx.name_code',
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
            'sn.database_id = db.record_id',
            array()
        )
        ->order(array('name', 'status'));
        
        return $select;
    }
    
    /**
     * Returns the all the existing record names of a specific rank only
     * if the total is less than the constant API_ROWSET_LIMIT and the
     * search string is shorter than the minimum per rank (@see _getMinStrLen)
     *
     * @param string $rank
     * @param string $query
     * @param array $key
     * @return array
     */
    public function fetchTaxaByRank($rank, $query, array $key)
    {
        $substr = trim(str_replace('*', '', $query));
        if (strlen($substr) < $this->_getMinStrLen($rank, $key)) {
            return array();
        }
        $qSubstr = trim(str_replace('*', '%', $query));
        
        $select = empty($key) ?
            $this->_getTaxaNameQuery($rank, $qSubstr, $substr) :
            $this->_getTaxaNameFilteredQuery($rank, $qSubstr, $substr, $key);
        
        $res = $select->query()->fetchAll();
        $total = count($res);
        $this->_logger->debug("$total results found for $rank \"$substr\"");
        if ($total > self::API_ROWSET_LIMIT) {
            return array();
        }
        return $res;
    }
    
    /**
     *
     * @param string $rank
     * @param string $str
     * @param array $key
     * @return Zend_Db_Select
     */
    protected function _getTaxaNameFilteredQuery($rank, $qStr, $str, array $key)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(array('scientific_names'), array('name' => $rank))
               ->where($rank . ' LIKE "' . $qStr . '"');
        foreach($key as $p => $v) {
            $select->where($p . ' = ?', $v);
        }
        $select->order(
                   array(
                       new Zend_Db_Expr(
                           'INSTR(' . $rank . ', "' . $str . '")'
                       ), $rank
                   )
               );
        return $select;
    }
    
    /**
     *
     * @param string $rank
     * @param string $str
     * @return Zend_Db_Select
     */
    protected function _getTaxaNameQuery($rank, $qStr, $str)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->distinct()
               ->from(array('hard_coded_taxon_lists'), array('name'))
               ->where('rank = ? AND name LIKE "' . $qStr . '"', $rank)
               ->order(
                   array(
                       new Zend_Db_Expr('INSTR(name, "' . $str . '")'),
                       'name'
                   )
               );
        return $select;
    }
    
    /**
     * Check whether a taxa name exists for the given rank
     *
     * @param string $rank
     * @param string $name
     * @return boolean
     */
    public function taxaExists($rank, $name) {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('hard_coded_taxon_lists'),
            array('total' => new Zend_Db_Expr('COUNT(*)'))
        )->where('rank = ? AND name = ? AND accepted_names_only = 1');
        $select->bind(array($rank, $name));
        return (bool)$select->query()->fetchColumn(0);
    }
    
    /**
     * Gets the minimum query length when searching for taxa based on the rank
     *
     * @param string $rank
     * @param array $key
     * @return int
     */
    protected function _getMinStrLen($rank, array $key)
    {
        $min = isset(self::$_apiMinStrLen[$rank]) ?
            self::$_apiMinStrLen[$rank] : 1;
            
        switch($rank) {
            case 'species':
                if(isset($key['genus'])) {
                    $min = 0;
                }
            break;
            case 'infraspecies':
                if(!empty($key)) {
                    $min = 0;
                }
            break;
        }
        return $min;
    }
    
    /**
     * Gets the children of a given taxon
     * Used to unfold the browse tree branches
     *
     * @param int $parentId
     * @return array
     */
    public function getTaxonChildren($parentId)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tx' => 'taxa'),
            array(
                'id' => 'tx.record_id',
                'snId' => 'sn.record_id',
                'name' => 'tx.name',
                'type' => 'tx.taxon',
                'parentId' => 'tx.parent_id',
                'lsid' => 'tx.lsid',
                'numChildren' => new Zend_Db_Expr('COUNT(txc.record_id)')
            )
        )
        ->joinLeft(
            array('txc' => 'taxa'),
            'tx.record_id = txc.parent_id',
            array()
        )
        ->joinLeft(
            array('sn' => 'scientific_names'),
            'tx.name_code = sn.name_code',
            array()
        )
        ->where('tx.parent_id = ? AND tx.is_accepted_name = 1', $parentId)
        ->group(array('tx.parent_id', 'tx.name'))
        ->order(
            array(
                new Zend_Db_Expr('tx.taxon <> "Superfamily"'),
                new Zend_Db_Expr('INSTR(tx.name, "Not assigned")'),
                'tx.name'
            )
        );
        $res = $select->query()->fetchAll();
        $total = count($res);
        $this->_logger->debug("$total children of $parentId");
        return $res;
    }
}