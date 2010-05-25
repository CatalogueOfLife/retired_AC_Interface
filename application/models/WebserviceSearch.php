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
                'source_database' => 'db.database_name_displayed',
                'source_database_url' => 'db.web_site',
                'reference_id' => new Zend_Db_Expr(0),
                'sort_order' => 'is_accepted_name'
            )
        )->joinLeft(
            array('db' => 'databases'),
            'tx.database_id = db.record_id',
            array()
        );
        // by id
        if (Zend_Validate::is($id, 'Digits')) {
            if ($id == 0) {
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
                'source_database' => 'db.database_name_displayed',
                'source_database_url' => 'db.web_site',
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
    
    public function _selectScientificName()
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('sn' => 'scientific_names'),
            array(
                'id' => 'sn.record_id',
                'name' =>
                    "TRIM(CONCAT(IF(sn.genus IS NULL, '', sn.genus) " .
                    ", ' ', IF(sn.species IS NULL, '', sn.species), ' ', " .
                    "IF(sn.infraspecies IS NULL, '', sn.infraspecies)))",
                'name_code' => 'sn.name_code',
                'rank' => new Zend_Db_Expr('""'),
                'rank_id' => 'IF(sn.infraspecies IS NULL OR ' .
                    'LENGTH(TRIM(sn.infraspecies)) = 0, ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ')',
                'name_status' => new Zend_Db_Expr('""'),
                'status' => 'sn.sp2000_status_id',
                'name_html' => new Zend_Db_Expr('""'),
                'genus' => 'sn.genus',
                'species' => 'sn.species',
                'infraspecies_marker' => 'sn.infraspecies_marker',
                'infraspecies' => 'sn.infraspecies',
                'author' => 'sn.author',
                'additional_data' => 'sn.comment',
                'distribution' => new Zend_Db_Expr('""'),
                'url' => new Zend_Db_Expr('""'),
                'source_database' => 'db.database_name_displayed',
                'source_database_url' => 'db.web_site',
                'record_scrutiny_date' => 'sn.scrutiny_date',
                'online_resource' => 'sn.web_site'
            )
        )->joinLeft(
            array('db' => 'databases'),
            'sn.database_id = db.record_id',
            array()
        );
        return $select;
        
    }
    
    public function scientificName($nameCode, /*bool*/$acceptedName) {
        
        $select = $this->_selectScientificName();
        $select->where('sn.name_code = ?', $nameCode);
        if ($acceptedName) {
            $select->where('sn.is_accepted_name = 1');
        }
        $res = $select->query()->fetchAll();
        
        return $res ? $res[0] : false;
        
    }
    
    public function synonyms($nameCode)
    {
        $select = $this->_selectScientificName();
        $select->where(
            'sn.accepted_name_code = ? AND sn.is_accepted_name = 0', $nameCode
        )
        ->group('sn.name_code')
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $res = $select->query()->fetchAll();
        
        return $res;
    }
    
    protected function _selectClassification()
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tx' => 'taxa'),
            array(
                'id' => 'tx.record_id',
                'tx.parent_id',
                'tx.name',
                'rank_id' => ACI_Model_Search::getRankDefinition(),
                'rank' => 'tx.taxon',
                'status' => 'tx.sp2000_status_id',
                'genus' => 'sn.genus',
                'species' => 'sn.species',
                'infraspecies_marker' => 'sn.infraspecies_marker',
                'infraspecies' => 'sn.infraspecies',
                'author' => 'sn.author'
            )
        )->joinLeft(
            array('sn' => 'scientific_names'),
            'tx.name_code = sn.name_code',
            array()
        );
            
        return $select;
    }
    
    public function classification($id)
    {
        $select = $this->_selectClassification();
        $select->where('tx.record_id = ?');
        
        $classification = array();
        
        do {
            $select->bind(array($id));
            $res = $select->query()->fetchAll();
            if (!count($res)) {
                break;
            }
            if ($res[0] > 0) {
                $classification[] = array(
                    'id' => $res[0]['id'],
                    'name' => $res[0]['name'],
                    'rank' => $res[0]['rank'],
                    'name_html' =>
                        $res[0]['rank_id'] == ACI_Model_Table_Taxa::RANK_GENUS
                            ?
                            '<i>' . $res[0]['name'] . '</i>' :
                        ($res[0]['rank_id'] < ACI_Model_Table_Taxa::RANK_SPECIES
                            ?
                            $res[0]['name'] :
                            ACI_Model_Table_Taxa::getAcceptedScientificName(
                                $res[0]['genus'],
                                $res[0]['species'],
                                $res[0]['infraspecies'],
                                $res[0]['infraspecies_marker'],
                                $res[0]['author']
                            )
                        ),
                    'url' => ACI_Model_Webservice::getTaxaUrl(
                        $res[0]['id'], $res[0]['rank_id'], $res[0]['status']
                    )
                );
            }
            $id = $res[0]['parent_id'];
            unset($res);
        } while ($id > 0);
        
        // remove the taxon itself
        unset($classification[0]);
        // return top to bottom hierarchy
        return array_reverse($classification);
    }
    
    public function childTaxa($id)
    {
        $select = $this->_selectClassification();
        $select->where('tx.parent_id = ?', $id);
        
        $res = $select->query()->fetchAll();
        $childTaxa = array();
        
        foreach($res as $taxon) {
            $childTaxa[] = array(
                'id' => $taxon['id'],
                'name' => $taxon['name'],
                'rank' => $taxon['rank'],
                'name_html' =>
                    $taxon['rank_id'] == ACI_Model_Table_Taxa::RANK_GENUS
                        ?
                        '<i>' . $taxon['name'] . '</i>' :
                    ($taxon['rank_id'] < ACI_Model_Table_Taxa::RANK_SPECIES
                        ?
                        $taxon['name'] :
                        ACI_Model_Table_Taxa::getAcceptedScientificName(
                            $taxon['genus'],
                            $taxon['species'],
                            $taxon['infraspecies'],
                            $taxon['infraspecies_marker'],
                            $taxon['author']
                        )
                    ),
                'url' => ACI_Model_Webservice::getTaxaUrl(
                    $taxon['id'], $taxon['rank_id'], $taxon['status']
                )
            );
        }
        
        unset($res);
        return $childTaxa;
    }
    
    public function getAcceptedNameCodeFromId($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('sn' => 'scientific_names'),
            array('name_code' => 'sn.accepted_name_code')
        )->where('sn.record_id = ?', $id);
        return $select->query()->fetchColumn(0);
    }
}