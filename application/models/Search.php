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
    const API_ROWSET_LIMIT = 1000;
    
    // Sort fields that are always added as the first of the list
    protected static $_prioritarySortParams = array(
        'all' => array('rank')
    );
    // Default sort params, also added after the custom sort fields
    protected static $_defaultSortParams = array(
        'common' => array('name'),
        'scientific' => array('name', 'status'),
        'all' => array('name'),
        'distribution' => array('distribution'),
        'classification' => array('name', 'status')
    );
    
    protected static function _getSortParams($action)
    {
        if (!isset(self::$_defaultSortParams[$action])) {
            return false;
        }
        $params = self::$_defaultSortParams[$action];
        if (isset(self::$_prioritarySortParams[$action])) {
            $params = array_merge(
                self::$_prioritarySortParams[$action], $params
            );
        }
        return $params;
    }
    
    public static function getDefaultSortParam($action)
    {
        $params = self::_getSortParams($action);
        return $params ? current($params) : '';
    }
    
    /**
     * Returns the final query (sorted) to search for common names
     *
     * @param string $searchKey
     * @param string $sort
     * @return Zend_Db_Select
     */
    public function commonNames($searchKey, $matchWholeWords, $sort = null, $order = null)
    {
        return $this->_selectCommonNames($searchKey, $matchWholeWords)
        ->order(
            $sort ?
            array_merge(
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortOrder($order)
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
     * @param string $sort
     * @return Zend_Db_Select
     */
    public function scientificNames(array $key, $matchWholeWords, $sort = null, $order = null)
    {
        return $this->_selectScientificNames($key, $matchWholeWords)
        ->order(
            $sort ?
            array_merge(
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortOrder($order)
                ),
                self::_getSortParams('scientific')
            ) : self::_getSortParams('scientific')
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
    public function distributions($searchKey, $matchWholeWords, $sort = null, $order = null)
    {
        $searchKey = $this->_wildcardHandling($searchKey);
        return $this->_selectDistributions($searchKey, $matchWholeWords)
        ->order(
            $sort ?
            array_merge(
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortOrder($order)
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
     * @param string $sort
     * @return Zend_Db_Select
     */
    public function all($searchKey, $matchWholeWords, $sort = null, $order = null)
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
            $sort ?
            
                array(
                    self::getRightColumnName($sort) .
                    self::getRightSortOrder($order)
            ) : self::_getSortParams('all')
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
    
    public static function getRightSortOrder($sortOrder)
    {
        $sortOptions = array(
            'asc' => ' ASC',
            'desc' => ' DESC'
        );
        return isset($sortOptions[$sortOrder]) ?
            $sortOptions[$sortOrder] : null;
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
        
        $replacedSearchKey = $this->_wildcardHandlingInRegExpression(
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
                'rank' => 'IF(sn.infraspecies_marker, ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ')',
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
                'rank' => 'IF(sn.infraspecies_marker, ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ')',
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
    protected function _getRankDefinition()
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
        $searchKey = $this->_wildcardHandling($searchKey);
        $select = new Zend_Db_Select($this->_db);
        
        $select->from(
            array('tx' => 'taxa'),
            array(
                'id' => 'sn.record_id',
                'taxa_id' => 'tx.record_id',
                'rank' => $this->_getRankDefinition(),
                'tx.name',
                'tx.name_code',
                'tx.is_accepted_name',
                'sn.author',
                'language' => new Zend_Db_Expr("''"),
                'sn.accepted_name_code',
                /*
                'accepted_species_id' => 'snt.record_id',
                'accepted_species_name' =>
                    "TRIM(CONCAT(IF(snt.genus IS NULL, '', snt.genus) " .
                    ", ' ', IF(snt.species IS NULL, '', snt.species), ' ', " .
                    "IF(snt.infraspecies IS NULL, '', snt.infraspecies)))",
                'accepted_species_author' => 'snt.author',
                */
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
        /*
        ->joinLeft(
            array('snt' => 'scientific_names'),
            'sn.accepted_name_code = snt.name_code',
            array()
        )*/
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
        $searchKey = $this->_wildcardHandling($searchKey);
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
            $replacedSearchKey = $this->_wildcardHandlingInRegExpression(
                $searchKey, 1
            );
            $select->where(
                'cn.common_name REGEXP "' . $replacedSearchKey . '" = 1'
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
                $searchKey = $this->_wildcardHandling($name);
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
        if (strlen($cleanStr) < $this->_getMinStrLen($rank, $key)) {
            return array('error' => 1);
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
        $total = count($res);
        if ($total > self::API_ROWSET_LIMIT) {
            return array('error' => 2);
        }
        return array_merge(array('error' => 0), $res);
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
        $select->where("$field IS NOT NULL AND sn.is_accepted_name = 1");
        $select->order(
            array(new Zend_Db_Expr("INSTR(`$rank`, \"$str\")"), $rank)
        );
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
               ->where('rank = ? AND name LIKE "' . $qStr .
                '"  AND accepted_names_only = 1', $rank)
               ->order(
                   array(
                       new Zend_Db_Expr('INSTR(name, "' . $str . '")'),
                       'name'
                   )
               );
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
        $rankId = array_search(
            $this->normalizeRank($rank), ACI_Model_Table_Taxa::getRanks()
        );
        return $rankId < ACI_Model_Table_Taxa::RANK_GENUS;
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
        $ranks = ACI_Model_Table_Taxa::getRanks();
        
        // no limit for higher taxa
        if ($this->stringRefersToHigherTaxa($rank)) {
            return 0;
        } else if (empty($key)) { // if no other keys exist, require 2 chars min
            return 2;
        }
        // Genus
        if ($this->normalizeRank($rank)
            == $ranks[ACI_Model_Table_Taxa::RANK_GENUS]) {
            return 0;
        }
        // Species and infraspecies
        return isset($key['kingdom']) ? (count($key) > 1 ? 0 : 1) : 0;
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
    protected function normalizeRank($rank)
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
                'lsid' => 'tx.lsid',
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
        ->where('tx.name = ?', $name);
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
                'accepted_species_author' => 'sn.author'
            )
        )
        ->where('sn.name_code = ? AND is_accepted_name = 1', $nameCode);
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
    
    protected function _wildcardHandling($searchString)
    {
        return str_replace(array('%','*'), array('','%'), $searchString);
    }
    
    protected function _wildcardHandlingInRegExpression($searchString,
        $matchWholeWords=true)
    {
        if ($matchWholeWords == true) {
            return str_replace(
                '%', '[^ \.\"\'\(\),;:-]*', '[[:<:]]' .
                $searchString .
                '[[:>:]]'
            );
        } else {
            return str_replace('%', '.*', $searchString);
        }
    }
}