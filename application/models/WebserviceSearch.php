<?php
require_once 'AModel.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_WebserviceSearch
 * Search queries builder for web services
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_WebserviceSearch extends AModel
{
    public function taxa($id, $name, $limit, $offset)
    {
        $select = new Eti_Db_Select($this->_db);
        
        $select->sqlCalcFoundRows()->from(
            array('tx' => 'taxa'),
            array(
                'record_id' => 'tx.record_id',
                'parent_id' => 'tx.parent_id',
                'name' => 'tx.name',
                'name_html' => 'name_with_italics',
                'unique_identifier' => 'tx.name_code',
                'name_status' => new Zend_Db_Expr('"scientific name"'),
                'rank' => 'tx.taxon',
                'sort_order' => 'is_accepted_name'
            )
        );
        // by id
        if(Zend_Validate::is($id, 'Digits')) {
            if($id == 0) {
                $select->where('tx.parent_id = 0');
            }
            else {
                $select->where('tx.record_id = ?', $id);
            }
        }
        // by name
        else {
            $searchKey = ACI_Model_Search::wildcardHandling($name);
            $select->where('tx.name != "Not assigned"')
                   ->where('is_species_or_nonsynonymic_higher_taxon = 1');
            if (strpos($searchKey, '%') === false) {
                $select->where('tx.name = ?', $searchKey);
            } else {
                $select->where('tx.name LIKE "' . $searchKey . '"');
            }
            $select->union($this->_selectCommonNames($searchKey));
        }
        $select->order(
            array(
                new Zend_Db_Expr('sort_order DESC'),
                new Zend_Db_Expr('LOWER(name)')
            )
        )->limit($limit, $offset);
        
        return $select->query()->fetchAll();
    }
    
    public function getFoundRows()
    {        
        $select = new Eti_Db_Select($this->_db);
        $select->from(null, new Zend_Db_Expr('FOUND_ROWS()'));
        return $select->query()->fetchColumn(0);
    }
    
    protected function _selectCommonNames($searchKey)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('cn' => 'common_names'),
            array(                
                'record_id' => 'cn.record_id',
                'parent_id' => new Zend_Db_Expr(''),
                'common_name' => 'cn.name',
                'name_html' => new Zend_Db_Expr(''),
                'unique_identifier' => 'cn.name_code',
                'name_status' => new Zend_Db_Expr('"common name"'),
                'rank' => new Zend_Db_Expr(''),
                'sort_order' => new Zend_Db_Expr(1)
            )
        );
        if (strpos($searchKey, '%') === false) {
            $select->where('tx.name = ?', $searchKey);
        } else {
            $select->where('tx.name LIKE "' . $searchKey . '"');
        }
        return $select;
    }
}