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
    const API_ROWSET_LIMIT = 500;

    // Default sort params, also added after the custom sort fields
    protected static $_defaultSortParams = array(
        'scientific' => array('name'),
        'common' => array('name'),
        'distribution' => array('distribution')
    );
    
    protected static function _getSortParams($action)
    {
        if (!isset(self::$_defaultSortParams[$action])) {
            return false;
        }
        $params = self::$_defaultSortParams[$action];
        return $params;
    }
    
    public static function getDefaultSortParam($action)
    {
        $params = self::_getSortParams($action);
        return $params ? current($params) : '';
    }
    
    protected static function _getDefaultSortExpression($searchKey,
        $matchWholeWords)
    {
        if(is_array($searchKey)) {
            // Scientific search, multiple fields
            $searchKey = trim(
                (isset($searchKey['genus']) ? $searchKey['genus'] : '') .
                    ' ' .
                (isset($searchKey['species']) ? $searchKey['species'] : '') .
                    ' ' .
                (isset($searchKey['infraspecies']) ?
                    $searchKey['infraspecies'] : '')
                );
        }
        $regexpSearchKey = strtolower(str_replace('*', '.*', $searchKey));
        $mysqlSearchKey = strtolower(str_replace('*', '%', $searchKey));
        
        return array(
            new Zend_Db_Expr(
                'IF(rank < '. ACI_Model_Table_Taxa::RANK_SPECIES . ', rank, 99)'
            ),
            new Zend_Db_Expr(
                'CONCAT(IF(status = '.
                ACI_Model_Table_Taxa::STATUS_COMMON_NAME . ', "D", "C"), "")'
            ),
            new Zend_Db_Expr(
                'CONCAT(IF(' .
                ($matchWholeWords == 0 ?
                    'LOWER(name) REGEXP "^[^ ]*' . $regexpSearchKey . '"' :
                    'INSTR(LOWER(name), "' . $mysqlSearchKey . '") = 1' ) .
                    ', "E", "F"), "")'
            ),
            ($matchWholeWords == 0 ?
                new Zend_Db_Expr('CONCAT(IF(LOWER(name) REGEXP
                    "[[:<:]]' . $regexpSearchKey . '[[:>:]]", "G", "H"), name)'
                ) : 'name'
             )
        );
    }
    
    /**
     * Returns the final query (sorted) to search for common names
     *
     * @param string $searchKey
     * @param string $sort sort field
     * @param string $direction sort direction (asc, desc)
     * @return Zend_Db_Select
     */
    public function commonNames($searchKey, $matchWholeWords, $sort = null,
        $direction = null)
    {
        $this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
        return $this->_selectCommonNames($searchKey, $matchWholeWords)
        ->order(
            $sort ?
            array_merge(
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortDirection($direction)
                ),
                self::_getSortParams('common')
            ) : self::_getSortParams('common')
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
     * @param string $sort sort field
     * @param string $direction sort direction (asc, desc)
     * @return Zend_Db_Select
     */
    public function scientificNames(array $key, $matchWholeWords, $sort = null,
        $direction = null)
    {
        $this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
        return $this->_selectScientificNames($key, $matchWholeWords)
        ->order(
            $sort ?
            array_merge(
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortDirection($direction)
                ),
                self::_getSortParams('scientific')
            ) : self::_getDefaultSortExpression($key, $matchWholeWords)
        );
    }
    
    /**
     * Returns the final query (sorted) to search for distributions
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @param string $sort sort field
     * @param string $direction sort direction (asc, desc)
     * @return Zend_Db_Select
     */
    public function distributions($searchKey, $matchWholeWords, $sort = null,
        $direction = null)
    {
        $this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
        $searchKey = self::wildcardHandling($searchKey);
        return $this->_selectDistributions($searchKey, $matchWholeWords)
        ->order(
            $sort ?
            array_merge(
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortDirection($direction)
                ),
                self::_getSortParams('distribution')
            ) : self::_getSortParams('distribution')
        );
    }
    
    /**
     * Returnes a Zend_Db_Select object joining and sorting the scientific and
     * common names search queries
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @param string $sort sort field
     * @param string $direction sort direction (asc, desc)
     * @return Zend_Db_Select
     */
    public function all($searchKey, $matchWholeWords, $sort = null,
        $direction = null)
    {
        $this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
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
            $sort ?
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortDirection($direction)
            ) : self::_getDefaultSortExpression($searchKey, $matchWholeWords)
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
     * Returns the SQL valid value for the sort direction string
     *
     * @param string $direction
     * @return string, null
     */
    public static function getRightSortDirection($direction)
    {
        $sortOptions = array(
            'asc' => ' ASC',
            'desc' => ' DESC'
        );
        return isset($sortOptions[$direction]) ?
            $sortOptions[$direction] : null;
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
            array_merge(
                $this->_getScientificSearchFields(),
                array('ds.distribution')
            )
        )
        ->joinLeft(
            array('sn' => 'scientific_names'),
            'sn.name_code = ds.name_code',
            array()
        )
        ->joinLeft(
            array('sna' => 'scientific_names'),
            'sna.accepted_name_code = sn.accepted_name_code AND ' .
            'sna.is_accepted_name = 1',
            array()
        )
        ->joinLeft(
            array('fm' => 'families'),
            'sn.family_id = fm.record_id',
            array()
        )
        ->joinLeft(
            array('db' => 'databases'),
            'sn.database_id = db.record_id',
            array()
        )
        ->where('ds.distribution LIKE ?', '%' . $searchKey . '%');
        
        $replacedSearchKey = self::wildcardHandlingInRegExp(
            $searchKey, $matchWholeWords
        );
        $select->where(
            ' ds.distribution REGEXP "' . $replacedSearchKey . '" = 1'
        );
        return $select;
    }
    
    /**
     * Returns the fields needed to display the results of the scientific search
     * queries (require a join from sn to sn for synonyms)
     *
     * @return array
     */
    protected function _getScientificSearchFields()
    {
        $fields =
            array(
                'id' => 'sn.record_id',
                'rank' => 'IF(sn.infraspecies IS NULL OR ' .
                    'LENGTH(sn.infraspecies) = 0, ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ')',
                'name' =>
                    "TRIM(CONCAT(IF(sn.genus IS NULL, '', sn.genus) " .
                    ", ' ', IF(sn.species IS NULL, '', sn.species), ' ', " .
                    "IF(sn.infraspecies IS NULL, '', sn.infraspecies)))",
                'sn.is_accepted_name',
                'sn.author',
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
                'status' => 'sn.sp2000_status_id'
            );
            
        return $fields;
    }
    
    /**
     * Fields for a query where only accepted names are fetched (no join from
     * sn to sn)
     *
     * @return array
     */
    protected function _getStrictScientificSearchFields()
    {
        $fields =
            array(
                'id' => 'sn.record_id',
                'taxa_id' => new Zend_Db_Expr(0),
                'rank' => 'IF(sn.infraspecies IS NULL OR ' .
                    'LENGTH(sn.infraspecies) = 0, ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ')',
                'name' =>
                    "TRIM(CONCAT(IF(sn.genus IS NULL, '', sn.genus) " .
                    ", ' ', IF(sn.species IS NULL, '', sn.species), ' ', " .
                    "IF(sn.infraspecies IS NULL, '', sn.infraspecies)))",
                'sn.name_code',
                'is_accepted_name' => new Zend_Db_Expr(1),
                'sn.author',
                'language' => new Zend_Db_Expr("''"),
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
                'status' =>
                    new Zend_Db_Expr(ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME)
            );
            
        return $fields;
        
    }
    
    /**
     * Gets the part of the query that maps the rank names to internal integer
     * constants
     *
     * @return Zend_Db_Expr
     */
    public static function getRankDefinition()
    {
        return new Zend_Db_Expr(
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
            'END'
        );
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
        $searchKey = self::wildcardHandling($searchKey);
        $select = new Zend_Db_Select($this->_db);
        
        $select->from(
            array('tx' => 'taxa'),
            array(
                'id' => 'sn.record_id',
                'taxa_id' => 'tx.record_id',
                'rank' => self::getRankDefinition(),
                'tx.name',
                'tx.name_code',
                'tx.is_accepted_name',
                'sn.author',
                'language' => new Zend_Db_Expr("''"),
                'sn.accepted_name_code',
                // Joining again to scientific_names produces a killing query
                // The accepted species data is retrieved afterwards
                'accepted_species_id' => new Zend_Db_Expr(0),
                'accepted_species_name' => new Zend_Db_Expr("''"),
                'accepted_species_author' => new Zend_Db_Expr("''"),
                //----------
                'db_name' => 'db.database_name',
                'db_id' => 'db.record_id',
                'db_thumb' =>
                    'CONCAT(REPLACE(db.database_name, " ", "_"), ".gif")',
                'kingdom' => 'fm.kingdom',
                'status' => 'tx.sp2000_status_id'
            )
        );
        
        if($matchWholeWords) {
            $select->join(
                array('ss' => 'simple_search'),
                'ss.taxa_id = tx.record_id',
                array()
            )
            ->where(
                'ss.words ' . (strstr($searchKey, '%') ? 'LIKE' : '=') . ' ? ' .
                'AND tx.is_species_or_nonsynonymic_higher_taxon = 1',
                $searchKey
            );
        }
        else {
            $select->where(
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
            array('db' => 'databases'),
            'tx.database_id = db.record_id',
            array()
        )
        // Prevent multiple selection of the same taxon (cased by duplicated
        // name codes)
        ->group(array('tx.record_id'))
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
        $searchKey = self::wildcardHandling($searchKey);
        $select = new Zend_Db_Select($this->_db);
        
        $select->from(
            array(
                'cn' => 'common_names'
            ),
            array(
                'id' => new Zend_Db_Expr(0),
                'taxa_id' => 'cn.record_id',
                'rank' => new Zend_Db_Expr('IF(cn.is_infraspecies, ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ')'),
                'name' => 'cn.common_name',
                'cn.name_code',
                'is_accepted_name' => new Zend_Db_Expr(0),
                'sn.author',
                'cn.language',
                'accepted_name_code' => 'sn.name_code',
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
        if($matchWholeWords) {
            $replacedSearchKey = self::wildcardHandlingInRegExp(
                $searchKey, 1
            );
            // When non alphabetic characters are used, this first filtering
            // will allow to match single words equal to the search key
            $select->where(
                'cn.common_name = "' . $searchKey . '"'
            );
            $select->orWhere(
                'cn.common_name LIKE "%&#32;' . $searchKey . '"'
            );
            $select->orWhere(
                'cn.common_name LIKE "' . $searchKey . '&#32;%"'
            );
            $select->orWhere(
                'cn.common_name LIKE "%&#32;' . $searchKey . '&#32;%"'
            );
            $select->orWhere(
                'cn.common_name REGEXP "' . $replacedSearchKey . '"'
            );
        }
        else {
            $select->where('cn.common_name LIKE "%' . $searchKey . '%"');
        }
        $select->group(
            array('name', 'language', 'accepted_species_name', 'db.record_id')
        );
        return $select;
    }
    
    /**
     * Search for scientific names query
     *
     * @param array $key
     * @param boolean $matchWholeWords
     * @return Eti_Db_Select
     */
    protected function _selectScientificNames(array $key, $matchWholeWords)
    {
        $select = new Eti_Db_Select($this->_db);
        $joinSn = true;

        foreach ($key as $rank => $name) {
            if ($this->stringRefersToHigherTaxa($rank)) {
                $field = "fm.$rank";
                $joinSn = false;
            } else {
                $field = "sn.$rank";
            }
            if (trim($name) != '') {
                $searchKey = self::wildcardHandling($name);
                if ($matchWholeWords) {
                    $select->where(
                        $field . ' ' .
                        (strstr($searchKey, '%') ? 'LIKE' : '=') . ' ?',
                        $searchKey
                    );
                } else {
                    $select->where($field . ' LIKE "%' . $searchKey . '%"');
                }
            }
        }
        $select->from(
            array('sn' => 'scientific_names'),
            $joinSn ?
                $this->_getScientificSearchFields() :
                $this->_getStrictScientificSearchFields()
        )
        ->joinLeft(
            array('fm' => 'families'),
            'sn.family_id = fm.record_id',
            array()
        )
        ->joinLeft(
            array('db' => 'databases'),
            'sn.database_id = db.record_id',
            array()
        );
        if ($joinSn) {
            $select
            ->joinLeft(
                array('sna' => 'scientific_names'),
                'sna.accepted_name_code = sn.accepted_name_code
                AND sna.is_accepted_name = 1',
                array()
            );
        } else {
            $select->where('sn.is_accepted_name = 1');
        }
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
        $cleanStr = trim(str_replace('*', '', $query));
        $cache = Zend_Registry::get('cache');
        $cacheKey = $rank . '_' . $cleanStr . '_' . implode('_', $key);
        $res = false;
        if($cache) {
            // Try to load cached results
            try {
                $res = $cache->load($cacheKey);
            } catch(Zend_Cache_Exception $zce) {
                // An exception may be thrown if the cache key is not valid
                // In that case, the cache is not used
                $cache = false;
            }
        }
        if(!$res) {
            if (strlen($cleanStr) < $this->_getMinStrLen($rank, $key)) {
                return array('error' => true);
            }
            $substr = explode('*', $query);
            $orderSubstr = $substr[0] ? $substr[0] : $substr[1];
            $qSubstr = trim(str_replace('*', '%', $query));
            $select = empty($key) ?
                // No other fields have been filled in
                $this->_getTaxaNameQuery($rank, $qSubstr, $orderSubstr) :
                // At least another filed has been filled in - must use it as a
                // filter
                $this->_getTaxaNameFilteredQuery(
                    $rank, $qSubstr, $orderSubstr, $key
                );
            $res = $select->query()->fetchAll();
            if($cache) {
                $cache->save($res, $cacheKey);
            }
        }
        return array_merge(array('error' => false), $res);
    }
    
    /**
     * Returns the query to get all the names matching the given string query
     * for a specific rank and base on what may be entered for the other ranks
     *
     * @param string $rank
     * @param string $qStr
     * @param string $str
     * @param array $key
     *
     * @return Zend_Db_Select
     */
    protected function _getTaxaNameFilteredQuery($rank, $qStr, $str, array $key)
    {
        $select = new Zend_Db_Select($this->_db);
        $field = $this->stringRefersToHigherTaxa($rank) ?
            "f.$rank" : "sn.$rank";
        $select->distinct()
        ->from(
            array('sn' => 'scientific_names'),
            array('name' => $field)
        )->join(
            array('f' => 'families'),
            'f.record_id = sn.family_id',
            array()
        );
        if ($str) {
            $select->where("`$rank` LIKE \"" . $qStr . "\"");
        }
        foreach ($key as $p => $v) {
            $select->where(
                $this->stringRefersToHigherTaxa($p) ?
                "f.$p = ?" : "sn.$p = ?", $v
            );
        }
        
        $rankId = $this->getRankIdFromString($rank);
        if($rankId == ACI_Model_Table_Taxa::RANK_SPECIES) {
            $select->where(
                'sn.infraspecies IS NULL OR LENGTH(infraspecies) = 0'
            );
        }
        else if($rankId == ACI_Model_Table_Taxa::RANK_INFRASPECIES) {
            $select->where(
                'sn.infraspecies IS NOT NULL OR LENGTH(TRIM(infraspecies)) > 0'
            );
        }
                
        $select
            ->where("LENGTH(TRIM($field)) > 0")
            ->where("sn.is_accepted_name = 1")
            ->order(
                array(new Zend_Db_Expr("INSTR(`$rank`, \"$str\")"), $rank)
            )
            ->limit(self::API_ROWSET_LIMIT + 1);
            
        return $select;
    }
    
    /**
     * Returns the query to get all the names matching the given string query
     * for a specific rank
     *
     * @param string $rank
     * @param string $str
     * @return Zend_Db_Select
     */
    protected function _getTaxaNameQuery($rank, $qStr, $str)
    {
        $select = new Zend_Db_Select($this->_db);
        // Search for higher taxa in families
        if ($this->stringRefersToHigherTaxa($rank)) {
            $select->distinct()
               ->from(array('families'), array('name' => $rank))
               ->where(
                   "`$rank` NOT IN('', 'Not assigned') AND " .
                   "is_accepted_name = 1 AND " .
                   "`$rank` LIKE \"" . $qStr . "\""
               )
               ->order(
                   array(new Zend_Db_Expr("INSTR(`$rank`, \"$str\")"), $rank)
               );
        } else { // Search for species in hard_coded_taxon_lists
            $select->distinct()
               ->from(array('hard_coded_taxon_lists'), array('name'))
               ->where('rank = ?', $rank)
               ->where('name LIKE "' . $qStr . '"')
               ->where('accepted_names_only = 1')
               ->order(
                   array(
                       new Zend_Db_Expr('INSTR(name, "' . $str . '")'),
                       'name'
                   )
               )
               ->limit(self::API_ROWSET_LIMIT + 1);
        }
        return $select;
    }
     
    /**
     * Check whether a taxa name exists for the given rank
     *
     * @param string $rank
     * @param string $name
     * @return boolean
     */
    public function taxaExists($rank, $name)
    {
        $select = new Zend_Db_Select($this->_db);
        // Higher taxa
        if ($this->stringRefersToHigherTaxa($rank)) {
            $select->from(
                array('families'),
                array('total' => new Zend_Db_Expr('COUNT(*)'))
            )->where("`$rank` = ?", $name);
        } else { // Genus, species, infraspecies
            $select->from(
                array('hard_coded_taxon_lists'),
                array('total' => new Zend_Db_Expr('COUNT(*)'))
            )->where('rank = ? AND name = ?');
            $select->bind(array($rank, $name));
        }
        return (bool)$select->query()->fetchColumn(0);
    }
    
    public function stringRefersToHigherTaxa($rank)
    {
        return $this->getRankIdFromString($rank) <
            ACI_Model_Table_Taxa::RANK_GENUS;
    }
    
    public function getRankIdFromString($rank) {
        $rankId = array_search(
            $this->_normalizeRank($rank), ACI_Model_Table_Taxa::getRanks()
        );
        return $rankId;
    }
    
    /**
     * Rules to decide the minimum string length of a field in what refers
     * to search hinting
     *
     * @param string $rank
     * @param array $key
     * @return int
     */
    protected function _getMinStrLen($rank, array $key)
    {
        // No limit for higher taxa
        if ($this->stringRefersToHigherTaxa($rank)) {
            return 0;
        } else if (empty($key)) { // if no other keys exist, require 2 chars min
            return 2;
        }
        // Other fields filled in
        return 0;
    }
    
    /**
     * Returns the normalized name of a rank from the simple one, ex:
     * phylum -> RANK_PHYLUM
     * genus -> RANK_GENUS
     *
     * @see ACI_Model_Table_Taxa::getRanks
     *
     * @param string $rank
     * @return string
     */
    protected function _normalizeRank($rank)
    {
        $prefix = 'RANK_';
        $rank = strtoupper($rank);
        if (strpos($rank, $prefix) === 0) {
            return $rank;
        }
        return $prefix . $rank;
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
                'numChildren' => new Zend_Db_Expr('COUNT(txc.record_id)')
            )
        )
        ->joinLeft(
            array('txc' => 'taxa'),
            'tx.record_id = txc.parent_id AND txc.is_accepted_name = 1',
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
    
    /**
     * Gets the taxa rank and id of the given name
     * Only accepted names are fetched
     *
     * @param string $name
     * @return Zend_Db_Select
     */
    public function getRecordIdFromName($name)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tx' => 'taxa'),
            array(
                'id' => 'tx.record_id',
                'rank' => 'tx.taxon'
            )
        )
        ->where('tx.name = ?', $name)
        ->where('tx.is_accepted_name = 1');
        return $select->query()->fetchAll();
    }
    
    public function getAcceptedSpeciesByNameCode($nameCode)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('sn' => 'scientific_names'),
            array(
                'accepted_species_id' => 'sn.record_id',
                'accepted_species_name' =>
                    "TRIM(CONCAT(IF(sn.genus IS NULL, '', sn.genus) " .
                    ", ' ', IF(sn.species IS NULL, '', sn.species), ' ', " .
                    "IF(sn.infraspecies IS NULL, '', sn.infraspecies)))",
                'accepted_species_author' => 'sn.author',
                'kingdom' => 'fm.kingdom'
            )
        )
        ->joinLeft(
            array('fm' => 'families'),
            'sn.family_id = fm.record_id',
            array()
        )
        ->where('sn.name_code = ?', $nameCode)
        ->where('sn.is_accepted_name = 1');
        
        return $select->query()->fetch();
    }
    
    public function getTaxaFromSpeciesId($speciesId)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tx' => 'taxa'),
            array('id' => 'tx.record_id')
        )
        ->join(
            array('sn' => 'scientific_names'),
            'sn.name_code = tx.name_code',
            array()
        )
        ->where('sn.record_id = ?', $speciesId);
        return $select->query()->fetchColumn(0);
    }
    
    public function getRankAndNameFromRecordId($recordId)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tx' => 'taxa'),
            array(
                'id' => 'tx.record_id',
                'rank' => 'tx.taxon',
                'name' => 'tx.name'
            )
        )
        ->where('tx.record_id = ?', $recordId);
        return $select->query()->fetchAll();
    }
    
    public static function wildcardHandling($searchString)
    {
        return str_replace(array('%', '*'), array('', '%'), $searchString);
    }
    
    public static function wildcardHandlingInRegExp($searchString,
        $matchWholeWords = true)
    {
        if ($matchWholeWords == true) {
            return str_replace(
                '%', '[^ \.\"\'\(\),;:-]*', '[[:<:]]' .
                addslashes(preg_quote($searchString)) .
                '[[:>:]]'
            );
        } else {
            return str_replace('%', '.*', $searchString);
        }
    }
}