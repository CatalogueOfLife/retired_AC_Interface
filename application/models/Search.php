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
    
    public function __construct(Zend_Db_Adapter_Abstract $adapter)
    {
        $this->_adapter = $adapter;
    }
    public function commonNames($searchKey, $matchWholeWords)
    {
        return $this->_selectCommonNames($searchKey, $matchWholeWords);
    }
    
    public function taxa($searchKey, $matchWholeWords)
    {
        return $this->_selectTaxa($searchKey, $matchWholeWords);
    }
    
    public function all($searchKey, $matchWholeWords)
    {
        $selectAll = $this->_adapter->select()->union(
            array(
                '(' . $this->_selectTaxa($searchKey, $matchWholeWords)->reset('order') . ')',
                '(' . $this->_selectCommonNames($searchKey, $matchWholeWords)->reset('order') . ')'
            )
        )->order(array('name', 'status'));
        
        return $selectAll;
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
        
        $select->order(array('name', 'status'));
         
        return $select;
    }
}