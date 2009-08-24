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
    protected $_adapter;
    protected $_logger;
    const API_ROWSET_LIMIT = 1500;
    
    public function __construct(Zend_Db_Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;
        $this->_logger = Zend_Registry::get('logger');
    }
    
    public function commonNames($searchKey, $matchWholeWords)
    {
        return $this->_selectCommonNames($searchKey, $matchWholeWords);
    }
    
    public function taxa($searchKey, $matchWholeWords)
    {
        return $this->_selectTaxa($searchKey, $matchWholeWords);
    }
    
    public function all($searchKey, $matchWholeWords, $sort)
    {
        $selectAll = $this->_adapter->select()->union(
            array(
                $this->_selectTaxa(
                    $searchKey, $matchWholeWords
                )->reset('order'),
                $this->_selectCommonNamesForUnion(
                    $searchKey, $matchWholeWords
                )
            )
        )->order(array_merge(array($this->_getRightColumnName($sort)),array('name', 'status')));
        
        return $selectAll;
    }
    
    protected function _getRightColumnName($columName)
    {
    	$find = array(
           'name',
           'rank',
           'status',
           'db'
        );
        $replace = array(
           'name',
           'taxon',
           'status',
           'db_name'
        );
        return str_replace($find,$replace,$columName);
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
        $select = new Zend_Db_Select($this->_adapter);
        
        $fields = array('id' => 'tx.record_id',
                        'tx.taxon',
                        'tx.name_code',
                        'tx.name',
                        'tx.is_accepted_name',
                        'db_name' => 'db.database_name',
                        'status' => 'st.sp2000_status');
        
        if($matchWholeWords) {
        
            $select->from(
                array(
                    'ss' => 'simple_search'
                ),
                $fields
            )->join(
                array('tx' => 'taxa'),
                'ss.taxa_id = tx.record_id',
                array()
            )->joinLeft(
                array('st' => 'sp2000_statuses'),
                'tx.sp2000_status_id = st.record_id',
                array()
            )->joinLeft(
                array('db' => 'databases'),
                'tx.database_id = db.record_id',
                array()
            )->where('ss.words = ?', $searchKey);
        }
        else {
            $select->from(
                array(
                    'tx' => 'taxa'
                ),
                $fields
            )->joinLeft(
                array('st' => 'sp2000_statuses'),
                'tx.sp2000_status_id = st.record_id',
                array()
            )->joinLeft(
                array('db' => 'databases'),
                'tx.database_id = db.record_id',
                array()
            )->where('tx.name LIKE "%' . $searchKey . '%"')
             ->where('tx.is_species_or_nonsynonymic_higher_taxon = 1');
        }
           
        $select->order(array('name', 'status'));
         
        return $select;
    }
    
    /**
     * Builds the select query to search common names for the search all
     * functionality. This query is unioned afterwards with scientific names.
     * The common names standalone search is built by the _selectCommonNames
     * method.
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @return Zend_Db_Select
     */
    protected function _selectCommonNamesForUnion($searchKey, $matchWholeWords)
    {
        $select = new Zend_Db_Select($this->_adapter);
        
        $select->distinct()->from(
            array(
                'cn' => 'common_names'
            ),
            array(
                'id' => new Zend_Db_Expr(0),
                'taxon' => new Zend_Db_Expr(
                    'IF(cn.is_infraspecies, "Infraspecies", "Species")'
                ),
                'cn.name_code',
                'name' => 'cn.common_name',
                'is_accepted_name' => new Zend_Db_Expr(1),
                'db_name' => 'db.database_name',
                'status' => new Zend_Db_Expr('"common name"'),
            )
        )->joinLeft(
            array('db' => 'databases'),
            'cn.database_id = db.record_id',
            array()
        );
         
        if($matchWholeWords)
        {
            $select->where(
                'cn.common_name REGEXP "[[:<:]]' . $searchKey . '[[:>:]]"');
        }
        else {
            $select
                ->where('cn.common_name LIKE "%' . $searchKey . '%"');
        }
         
        return $select;
    }
    
    /**
     * Builds the select query to search common names
     *
     * @param string $searchKey
     * @param boolean $matchWholeWords
     * @return Zend_Db_Select
     */
    protected function _selectCommonNames($searchKey, $matchWholeWords)
    {
        $select = new Zend_Db_Select($this->_adapter);
        
        $select->distinct()->from(
            array(
                'cn' => 'common_names'
            ),
            array(
                'name' => 'cn.common_name',
                'cn.name_code',
                'sn.genus',
                'sn.species',
                'sn.infraspecies_marker',
                'sn.infraspecies',
                'sn.author',
                'db_name' => 'db.database_name'
            )
        )->join(
            array('sn' => 'scientific_names'),
            'cn.name_code = sn.name_code AND sn.is_accepted_name = 1',
            array()
        )->joinLeft(
            array('db' => 'databases'),
            'sn.database_id = db.record_id',
            array()
        );
         
        if($matchWholeWords)
        {
            $select->where(
                'cn.common_name REGEXP "[[:<:]]' . $searchKey . '[[:>:]]"');
        }
        else {
            $select
                ->where('cn.common_name LIKE "%' . $searchKey . '%"');
        }
         
        $select->order(
            array(
                'name', 'genus', 'species', 'infraspecies', 'author'
            )
        );
        
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
        $select = new Zend_Db_Select($this->_adapter);
        $total = $this->_getRankCount($rank, $name);
        
        $this->_logger->debug("$total results found for $rank \"$name\"");

        if($total > self::API_ROWSET_LIMIT) {
            return array();
        }
        
        $select->distinct()
               ->from(array('hard_coded_taxon_lists'), array('name'))
               ->where('rank = ? AND name LIKE "'. $name .'%"', $rank)
               ->order(array('name'));
        return $select->query()->fetchAll();
    }
    
   /**
     * Returns the number of different existing record names of a specific rank
     *
     * @return int
     */
    protected function _getRankCount($rank, $name)
    {
        $select = new Zend_Db_Select($this->_adapter);
        
        $select
            ->from(
                array('hard_coded_taxon_lists'),
                array('total' => new Zend_Db_Expr('COUNT(DISTINCT name)'))
            )
            ->where('rank = ? AND name LIKE "'. $name .'%"', $rank);
            
        return $select->query()->fetchColumn();
    }
}