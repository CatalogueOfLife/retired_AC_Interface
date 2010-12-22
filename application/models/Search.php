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
    protected static function _getDefaultSortParams($action='all',$direction='asc')
    {
    	switch ($action) {
    		case 'scientific':
    			return array(
		        	'name',
		            'author'.self::getRightSortDirection($direction),
		            'name_status'.self::getRightSortDirection($direction),
		            'accepted_species_name'.self::getRightSortDirection($direction),
		            'accepted_species_author'.self::getRightSortDirection($direction)
    			);
    			break;
    		case 'distribution':
    			return array(
    				'distribution',
					self::_getSortRank($direction)
    			);
    			break;
    		case 'all':
    		case 'common':
    		default:
				return array(
		    		'name',
		            'name_suffix'.self::getRightSortDirection($direction),
		            'name_status'.self::getRightSortDirection($direction),
		            'name_status_suffix'.self::getRightSortDirection($direction),
		            'name_status_suffix_suffix'.self::getRightSortDirection($direction),
					self::_getSortRank($direction)
				);
    			break;
    	}
    }
    
    protected static function _getSortRank($direction)
    {
    	return($direction != 'desc' ?
		new Zend_Db_Expr(
		'IF(rank = "Phylum", "E",
		  IF(rank = "Class", "F",
		   IF(rank = "Order", "G",
		    IF(rank = "Superfamily", "H",
		     IF(rank = "Family", "I",
		      IF(rank = "Genus", "J","K"
		))))))') :
		new Zend_Db_Expr(
		'IF(rank = "Phylum", "K",
		  IF(rank = "Class", "J",
		   IF(rank = "Order", "I",
		    IF(rank = "Superfamily", "H",
		     IF(rank = "Family", "G",
		      IF(rank = "Genus", "F","E"
		))))))')
	);
    
    }
    
    protected static function _getSortParams($action,$direction='asc')
    {
        if (!self::_getDefaultSortParams($action,$direction)) {
            return false;
        }
        $params = self::_getDefaultSortParams($action,$direction);
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
        if (is_array($searchKey)) {
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
                'IF(LENGTH(species) > 0, 1, 99)'
            ),
            new Zend_Db_Expr(
                'CONCAT(IF(name_status = 6, "D", "C"), "")'
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
    
    private function getTrueMatchWholeWords($matchWholeWords, $searchWord)
    {
        if($matchWholeWords == 1)
        {
            return 1;
        } elseif ($this->_strposInarray('%',$searchWord))
        {
            return 2;
        } else {
            return 0;
        }
    }

    private function _strposInarray ($strpos, $searchWord)
    {
    	if(is_array($searchWord))
    	{
	        foreach ($searchWord as $key => $value) {
	            if (strpos($value, $strpos) === false) {
	                return true;
	            }
	        }
    	} elseif(strpos($searchWord,$strpos) === false) {
    		return true;
    	}
		return false;
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
        $matchWholeWords = $this->getTrueMatchWholeWords($matchWholeWords, $searchKey);
        $this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
        return $this->_selectCommonNames($searchKey, $matchWholeWords)
        ->reset('order')
        ->order(
            ($sort ?
                ($sort == 'scientificName' ?
                	array_merge(
			            array(
		                    'name_status' . self::getRightSortDirection($direction),
	                    	'name_status_suffix' . self::getRightSortDirection($direction),
	                    ),
	                    self::_getSortParams('common',$direction)
                    ) :
	                array_merge(
	                	array(
		                    self::getRightColumnName($sort) . self::getRightSortDirection($direction)
	                	),
	                    self::_getSortParams('common',$direction)
                	)
            	) : array_merge(
            		$this->_getDefaultSortExpression($searchKey, $matchWholeWords),
            		self::_getSortParams('common',$direction)
            	)
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
        $matchWholeWords = $this->getTrueMatchWholeWords($matchWholeWords, $searchKey);
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
        $direction = null, $action)
    {
        $matchWholeWords = $this->getTrueMatchWholeWords($matchWholeWords, $key);
    	$this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
        return $this->_selectScientificNames($key, $matchWholeWords, $action)
        ->order(
	        ($sort ?
	        	($sort == 'status' ?
			        array_merge(
		                array(
		                    self::getRightColumnName($sort) . self::getRightSortDirection($direction),
		                    'accepted_species_name' . self::getRightSortDirection($direction)
		                ),
		                self::_getSortParams('scientific',$direction)
	                ) :
	                array_merge(
	                	array(
	                		self::getRightColumnName($sort) . self::getRightSortDirection($direction)
	                	),
	                	self::_getSortParams('scientific',$direction)
	                )
            	) : array_merge(
            		$this->_getDefaultSortExpression($key, $matchWholeWords),
            		self::_getSortParams('scientific',$direction)
            	)
            )
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
        $matchWholeWords = $this->getTrueMatchWholeWords($matchWholeWords, $searchKey);
        $this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
        $searchKey = self::wildcardHandling($searchKey);
        return $this->_selectDistributions($searchKey, $matchWholeWords)
        ->order(
	        ($sort ?
	        	($sort == 'name' ?
			        array_merge(
		                array(
		                    self::getRightColumnName($sort) . self::getRightSortDirection($direction),
		                    'author' . self::getRightSortDirection($direction)
		                ),
		                self::_getSortParams('distribution',$direction)
	                ) :
	                array_merge(
	                	array(
	                		self::getRightColumnName($sort) . self::getRightSortDirection($direction)
	                	),
	                	self::_getSortParams('distribution',$direction)
	                )
            	) : array_merge(
            		self::_getSortParams('distribution',$direction)
            	)
            )
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
        $matchWholeWords = $this->getTrueMatchWholeWords($matchWholeWords, $searchKey);
        $this->_logger->debug(__METHOD__);
        $this->_logger->debug(func_get_args());
        return $this->_db->select()->union(
            array(
                $this->_selectTaxa(
                    $searchKey, $matchWholeWords
                )->reset('order')/*,
                $this->_selectCommonNames(
                    $searchKey, $matchWholeWords
                )->reset('order')*/
            )
        )
        ->order(
            ($sort ?
                ($sort == 'status' ?
                	array_merge(
			            array(
		                    self::getRightColumnName($sort) . self::getRightSortDirection($direction),
	                    	'name_status_suffix' . self::getRightSortDirection($direction),
	                    	'name_status_suffix_suffix' . self::getRightSortDirection($direction),
	                    ),
	                    self::_getSortParams('all',$direction)
                    ) :
	                array_merge(
	                	array(
		                    self::getRightColumnName($sort) . self::getRightSortDirection($direction)
	                	),
	                    self::_getSortParams('all',$direction)
                	)
            	) : array_merge(
            		$this->_getDefaultSortExpression($searchKey, $matchWholeWords),
            		self::_getSortParams('all',$direction)
            	)
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
            'status' => 'name_status',
            'db' => 'source_database_name',
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
            array('dsd' => '_search_distribution'),
                array(
                    '*',
                    'status' => new Zend_Db_Expr(1),
                    'id' => 'dsd.accepted_species_id'
                )
        );
/*        ->where('dsd.distribution LIKE ?', '%' . $searchKey . '%');
        
        $replacedSearchKey = self::wildcardHandlingInRegExp(
            $searchKey, $matchWholeWords
        );*/
        if($matchWholeWords == 0)
        {
            $select->where(
                'dsd.distribution LIKE "%'.$searchKey.'%"'
            );
        } else {
            $select->where(
                'MATCH (dsd.distribution) AGAINST ("'.$searchKey.($matchWholeWords == 1 ? '"' : '*" IN BOOLEAN MODE').')'
            );
        }
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
                    'LENGTH(TRIM(sn.infraspecies)) = 0, ' .
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
                    'LENGTH(TRIM(sn.infraspecies)) = 0, ' .
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
            'CASE LOWER(tst.rank) ' .
            'WHEN "kingdom" THEN ' .
                ACI_Model_Table_Taxa::RANK_KINGDOM . ' ' .
            'WHEN "phylum" THEN ' .
                ACI_Model_Table_Taxa::RANK_PHYLUM . ' ' .
            'WHEN "class" THEN ' .
                ACI_Model_Table_Taxa::RANK_CLASS . ' ' .
            'WHEN "order" THEN ' .
                ACI_Model_Table_Taxa::RANK_ORDER . ' ' .
            'WHEN "superfamily" THEN ' .
                ACI_Model_Table_Taxa::RANK_SUPERFAMILY . ' ' .
            'WHEN "family" THEN ' .
                ACI_Model_Table_Taxa::RANK_FAMILY . ' ' .
            'WHEN "genus" THEN ' .
                ACI_Model_Table_Taxa::RANK_GENUS . ' ' .
            'WHEN "species" THEN ' .
                ACI_Model_Table_Taxa::RANK_SPECIES . ' ' .
            'ELSE ' .
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
            array('tst' => '_search_all'),
            array(
                'id' => 'tst.id',
                'taxa_id' => 'tst.accepted_taxon_id',
                'rank' => self::getRankDefinition(),
                'tst.name',
                'species' => 'tst.name',
                'author' => 'name_suffix',
                'language' => new Zend_Db_Expr("''"),
                'accepted_species_id' => 'tst.accepted_taxon_id',
                'accepted_species_name' => 'tst.name_status_suffix',
                'accepted_species_author' => 'tst.name_status_suffix_suffix',
                'db_name' => 'tst.source_database_name',
                'db_id' => 'tst.source_database_id',
                'db_thumb' =>
                    'CONCAT(REPLACE(tst.source_database_name, " ", "_"), ".gif")',
                'kingdom' => 'tst.group',
                'status' => 'tst.name_status'
            )
        );
        
        $column = (preg_match('/\s/',$searchKey) ? 'name' : 'name_element');
        if ($matchWholeWords && ($column == 'name_element' || strstr($searchKey, '%'))) {
            $key = ($matchWholeWords == 1 ? $searchKey : $searchKey . '%');
            $select->where(
                'tst.'.$column.' ' . (strstr($key, '%') ? 'LIKE' : '=') . ' ? ',
                $key
            );
        } elseif ($matchWholeWords && !strstr($searchKey, '%')) {
            $name_elements = explode(' ',$searchKey);
            $having = '';
            foreach($name_elements as $name_element)
            {
                $key = ($matchWholeWords == 1 ? $name_element : $name_element . '%');
                $select->orWhere(
                    'tst.name_element ' . (strstr($key, '%') ? 'LIKE' : '=') . ' ?',
                    $key
                );
                $having .= ' AND `name` LIKE "%' . $name_element . '%"';
            }
        } else {
            $select->where(
                'tst.'.$column.' LIKE "%' . $searchKey . '%"'
            );
        }
           
        // Prevent multiple selection of the same taxon (cased by duplicated
        // name codes)
        $select->group(array('tst.id'));
        if(isset($having)) {
            $select->having(
                'COUNT(tst.id) >= ' . count($name_elements) . $having
            );
        }
        $select->order(array('`name`', '`status`'));
         
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
                'tst' => '_search_all'
            ),
            array(
                'id' => 'tst.id',
                'taxa_id' => 'tst.accepted_taxon_id',
                'rank' => self::getRankDefinition(),
                'name' => 'tst.name',
                'is_accepted_name' => new Zend_Db_Expr(0),
                'author' => 'tst.name_suffix',
                'accepted_species_name' => 'tst.name_status_suffix',
                'accepted_species_author' => 'tst.name_status_suffix_suffix',
                'db_name' => 'tst.source_database_name',
                'db_id' => 'tst.source_database_id',
                'db_thumb' =>
                    'CONCAT(REPLACE(tst.source_database_name, " ", "_"), ".gif")',
                'kingdom' => 'tst.group',
                'status' => 'tst.name_status'
            )
        );
        $select->where(
            'name_status = 6'
        );
        $column = (preg_match('/\s/',$searchKey) ? 'name' : 'name_element');
        if ($matchWholeWords && ($column == 'name_element' || strstr($searchKey, '%'))) {
            $key = ($matchWholeWords == 1 ? $searchKey : $searchKey . '%');
            $select->where(
                'tst.'.$column.' ' . (strstr($key, '%') ? 'LIKE' : '=') . ' ? ',
                $key
            );
        } elseif ($matchWholeWords && !strstr($searchKey, '%')) {
            $name_elements = explode(' ',$searchKey);
            $having = '';
            foreach($name_elements as $name_element)
            {
                $key = ($matchWholeWords == 1 ? $name_element : $name_element . '%');
                if($having == '')
                {
                    $select->where(
                        'tst.name_element ' . (strstr($key, '%') ? 'LIKE' : '=') . ' ?',
                        $key
                    );
                } else {
                    $select->orWhere(
                        'tst.name_element ' . (strstr($key, '%') ? 'LIKE' : '=') . ' ?',
                        $key
                    );
                }
                $having .= ' AND `name` LIKE "%' . $name_element . '%"';
            }
        } else {
            $select->where(
                'tst.'.$column.' LIKE "%' . $searchKey . '%"'
            );
        }
        
        // Prevent multiple selection of the same taxon (cased by duplicated
        // name codes)
        $select->group(array('tst.id'));
        if(isset($having)) {
            $select->having(
                'COUNT(tst.id) >= ' . count($name_elements) . $having
            );
        }
        $select->order(array('name'));
        
        return $select;
    }
    
    /**
     * Search for scientific names query
     *
     * @param array $key
     * @param boolean $matchWholeWords
     * @return Eti_Db_Select
     */
    protected function _selectScientificNames(array $key, $matchWholeWords, $action='scientific')
    {
        $select = new Eti_Db_Select($this->_db);

        foreach ($key as $rank => $name) {
            $matchWholeWords = $this->getTrueMatchWholeWords($matchWholeWords, $name);
            if (trim($name) != '') {
                $searchKey = self::wildcardHandling($name);
                if ($matchWholeWords != 0) {
                    $searchKey = $searchKey . ($matchWholeWords == 2 ? '%' : '');
                    $select->where(
                        'dss.`'.$rank.'` ' .
                        (strstr($searchKey, '%') ? 'LIKE' : '=') . ' ?',
                        $searchKey
                    );
                } else {
                    $select->where('dss.`'.$rank.'` LIKE "%' . $searchKey . '%"');
                }
                if($action != 'scientific') {
                	$select->where('dss.`species` != "" AND dss.`accepted_species_id` = 0');
                }
            }
        }
        $select->from(
            array('dss' => '_search_scientific'),
            array('*',
                'name' => 'CONCAT_WS(" ",genus,species,infraspecific_marker,infraspecies)',
                'name_status' => 'status'
            )
        );
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
        if ($cache) {
            // Try to load cached results
            try {
                $res = $cache->load($cacheKey);
            } catch(Zend_Cache_Exception $zce) {
                // An exception may be thrown if the cache key is not valid
                // In that case, the cache is not used
                $cache = false;
            }
        }
        if (!$res) {
            if (strlen($cleanStr) < $this->_getMinStrLen($rank, $key)) {
                if(!in_array($rank,array('kingdom','phylum','class','order',
                  'superfamily','family')))
                {
                    return array('error' => true);
                }
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
            if ($cache) {
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
        $field = $rank;
        if(($rank == 'kingdom' || $rank == 'phylum' || $rank == 'class' ||
          $rank == 'order' || $rank == 'superfamily' || $rank == 'family') &&
          !array_key_exists('genus',$key) && !array_key_exists('species',$key) &&
          !array_key_exists('infraspecies',$key) ) {
            $from = '_search_family';
        } else {
            $from = '_search_scientific';
        }
        
        $select->distinct()
        ->from(
            array('dss' => $from),
            array('name' => $field)
        );
        if ($str) {
            $select->where("`$rank` LIKE \"" . $str . "%\"");
        }
        foreach ($key as $p => $v) {
            $select->where(
                "dss.$p = ?", $v
            );
        }
        
        $rankId = $this->getRankIdFromString($rank);
        if ($rankId == ACI_Model_Table_Taxa::RANK_SPECIES) {
/*            $select->where(
                'LENGTH(TRIM(dss.infraspecies)) = 0'
            );*/
        }
        else if ($rankId == ACI_Model_Table_Taxa::RANK_INFRASPECIES) {
            $select->where(
                'LENGTH(dss.infraspecies) > 0'
            );
        }
                
        $select->where("LENGTH(TRIM(`$field`)) > 0");
        if(($rank == 'kingdom' || $rank == 'phylum' || $rank == 'class' ||
          $rank == 'order' || $rank == 'superfamily' || $rank == 'family') &&
          !array_key_exists('genus',$key) && !array_key_exists('species',$key) &&
          !array_key_exists('infraspecies',$key) ) {
              
        } else {
            $select->where('dss.accepted_species_id = 0');
        }
        $select->order($rank)
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
//        $rank = $this->getRankFromId($rankId);
        $select->distinct();
        $where = '';
        if($rank == 'kingdom' || $rank == 'phylum' || $rank == 'class' ||
          $rank == 'order' || $rank == 'superfamily' || $rank == 'family') {
            $select->from(array('_search_family'), array('name' => $rank));
        } else {
            $select->from(array('_search_scientific'), array('name' => $rank));
            $select->where('accepted_species_id = 0');
        }
        $select->where(
            "`$rank` NOT IN('', 'Not assigned') AND " .
            $where .
            "`$rank` LIKE \"" . $str . "%\""
        )->order($rank);
        
        // Search for higher taxa in families
/*        if ($this->stringRefersToHigherTaxa($rank)) {
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
        }*/
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
//        if ($this->stringRefersToHigherTaxa($rank)) {
            $select->from(
                array(($rank == 'kingdom' || $rank == 'phylum' || $rank == 'class' ||
                  $rank == 'order' || $rank == 'superfamily' || $rank == 'family' ?
                  '_search_family' : '_search_scientific')),
                array('total' => new Zend_Db_Expr('COUNT(*)'))
            )->where("`$rank` = ?", $name);
/*        } else { // Genus, species, infraspecies
            $select->from(
                array('hard_coded_taxon_lists'),
                array('total' => new Zend_Db_Expr('COUNT(*)'))
            )->where('rank = ? AND name = ?');*/
//            $select->bind(array($rank, $name));
//        }
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
            array('ttt' => '_taxon_tree'),
            array(
                'id' => 'ttt.taxon_id',
                'snId' => new Zend_Db_Expr('""'),
                'name' => 'ttt.name',
                'type' => 'ttt.rank',
                'parentId' => 'ttt.parent_id',
                'numChildren' => 'ttt.number_of_children'
            )
        )
        ->where('ttt.parent_id = ?', $parentId)
        ->group(array('ttt.parent_id', 'ttt.name'))
        ->order(
            array(
                new Zend_Db_Expr('ttt.rank <> "superfamily"'),
                new Zend_Db_Expr('INSTR(ttt.`name`, "Not assigned")'),
                'ttt.name'
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
    
    public function getRankAndNameFromRecordId($taxonId)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tx' => '_taxon_tree'),
            array(
                'id' => 'tx.taxon_id',
                'rank',
                'name'
            )
        )
        ->where('tx.taxon_id = ?', $taxonId);
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