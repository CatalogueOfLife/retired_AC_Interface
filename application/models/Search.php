<?php
class AC_Model_Search
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
                '(' . $this->_selectTaxa($searchKey, $matchWholeWords) . ')',
                '(' . $this->_selectCommonNames($searchKey, $matchWholeWords) . ')'
            )
        )->order(array('name', 'status'));
        
        return $selectAll;
    }
    
    protected function _selectTaxa($searchKey, $matchWholeWords)
    {
        $selectTaxa = new Zend_Db_Select($this->_adapter);
        
        $selectTaxa->from(
            array(
                'ss' => 'simple_search'
            ),
            array(
                'id' => 'tx.record_id',
                'tx.taxon',
                'tx.name_code',
                'tx.name',
                'tx.is_accepted_name',
                'db_name' => 'db.database_name',
                'status' => 'st.sp2000_status'
            )
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
        )->where('ss.words = ?', $searchKey)
         ->order(array('name', 'status'));
         
        return $selectTaxa;
    }
    
    protected function _selectCommonNames($searchKey, $matchWholeWords)
    {
        $selectCommonNames = new Zend_Db_Select($this->_adapter);
        
        $selectCommonNames
        ->from(
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
        )->where('cn.common_name LIKE CONCAT("%", ?, "%")', $searchKey)
         ->order(array('name', 'status'));
         
         return $selectCommonNames;
    }
}