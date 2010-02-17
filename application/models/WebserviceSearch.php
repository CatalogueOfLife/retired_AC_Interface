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
                'sn_id' => new Zend_Db_Expr(0),
                'record_id' => 'tx.record_id',
                'parent_id' => 'tx.parent_id',
                'name' => 'tx.name',
                'name_html' => 'name_with_italics',
                'name_code' => 'tx.name_code',
                'status' => 'IF(tx.sp2000_status_id = 0, ' .
                    ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME .
                    ', tx.sp2000_status_id)',
                'rank_id' => ACI_Model_Search::getRankDefinition(),
                'rank' => 'tx.taxon',
                'language' => new Zend_Db_Expr('""'),
                'country' => new Zend_Db_Expr('""'),
                'db_name' => 'db.database_name_displayed',
                'db_url' => 'db.web_site',
                'reference_id' => new Zend_Db_Expr(0),
                'sort_order' => 'is_accepted_name'
            )
        )->joinLeft(
            array('db' => 'databases'),
            'tx.database_id = db.record_id',
            array()
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
            $select = $this->_db->select()->union(
                array(
                    $select,
                    $this->_selectCommonNames($searchKey)
                )
            );
        }
        $select->order(
            array(
                new Zend_Db_Expr('sort_order DESC'),
                new Zend_Db_Expr('LOWER(name)')
            )
        )->limit($limit, $offset);
        
        return $select->query()->fetchAll();
    }
    
    protected function _selectCommonNames($searchKey)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('cn' => 'common_names'),
            array(
                'sn_id' => 'sn.record_id',
                'record_id' => 'cn.record_id',
                'parent_id' => new Zend_Db_Expr('""'),
                'common_name' => 'cn.common_name',
                'name_html' => new Zend_Db_Expr('""'),
                'name_code' => 'cn.name_code',
                'status' => new Zend_Db_Expr(
                    ACI_Model_Table_Taxa::STATUS_COMMON_NAME
                ),
                'rank_id' => new Zend_Db_Expr(0),
                'rank' => new Zend_Db_Expr('""'),
                'language' => 'cn.language',
                'country' => 'cn.country',
                'db_name' => 'db.database_name_displayed',
                'db_url' => 'db.web_site',
                'reference_id' => 'cn.reference_id',
                'sort_order' => new Zend_Db_Expr(1)
            )
        )
        ->join(
            array('sn' => 'scientific_names'),
            'cn.name_code = sn.name_code AND sn.is_accepted_name = 1',
            array()
        )->joinLeft(
            array('db' => 'databases'),
            'cn.database_id = db.record_id',
            array()
        );
        
        if (strpos($searchKey, '%') === false) {
            $select->where('cn.common_name = ?', $searchKey);
        } else {
            $select->where('cn.common_name LIKE "' . $searchKey . '"');
        }
        
        return $select;
    }
}