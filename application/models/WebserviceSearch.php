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
    /*public function taxa($id, $name, $limit, $offset)
    {
        $select = new Eti_Db_Select($this->_db);
        
        $select->sqlCalcFoundRows()->from(
            array('tst' => 'taxa'),
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
    }*/

    // taxa now fetches common names without the need for a union select
    public function taxa($id, $name, $limit, $offset)
    {
        $select = new Eti_Db_Select($this->_db);
        
        $select->distinct()->sqlCalcFoundRows()->from(
            array('tst' => '_search_all'),
            array(
                'sn_id' => 'tst.accepted_taxon_id',
                'record_id' => 'tst.id',
                'parent_id' => new Zend_Db_Expr('""'),
                'name' => 'tst.name',
                'name_html' => 
                    'IF(
                        tst.name_status != '.ACI_Model_Table_Taxa::STATUS_COMMON_NAME.', '. 
                        'IF(
                            tst.rank = "genus", 
                            CONCAT("<i>", tst.name, "</i>"), 
                            tst.name
                        ), '.
                        new Zend_Db_Expr('""').
                    ')',
                //'name_code' => new Zend_Db_Expr('""'),
                'status' => 'IF(tst.name_status = 0, ' .
                    ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME .
                    ', tst.name_status)',
                'rank_id' => ACI_Model_Search::getRankDefinition(),
                'rank' => 'tst.rank',
                //'language' => new Zend_Db_Expr('""'), // Fetch separately for common names only
                //'country' => new Zend_Db_Expr('""'), // Fetch separately for common names only
                'source_database_id' => 'tst.source_database_id', // Fetch db full name and uri separately
                //'reference_id' => new Zend_Db_Expr(0),
                'sort_order' => 'tst.name_status'
            )
        );
        // by id
        
        
        if (Zend_Validate::is($id, 'Digits')) {
            if ($id == 0) {
                $select->where('tst.`rank` = "kingdom"');
            }
            else {
                $select->where('tst.`id` = ?', $id);
            }
        }
        // by name
        else {
            $searchKey = ACI_Model_Search::wildcardHandling($name);
            $select->where('tst.`name` != "Not assigned"');
            if (strpos($searchKey, '%') === false) {
                $select->where('tst.`name` = ?', $searchKey);
            } else {
                $select->where('tst.`name` LIKE "' . $searchKey . '"');
            }
        }
        $select->order(
            array(
                new Zend_Db_Expr('sort_order'),
                new Zend_Db_Expr('LOWER(tst.name)')
            )
        )->limit($limit, $offset);
        
        //echo $select->__toString(); die();
        
        return $select->query()->fetchAll();
    }
    
/*    protected function _selectCommonNames($searchKey)
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
*/    
/*    public function _selectScientificName()
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
                'record_scrutiny_date' => 'sn.original_scrutiny_date',
                'online_resource' => 'sn.web_site'
            )
        )->joinLeft(
            array('db' => 'databases'),
            'sn.database_id = db.record_id',
            array()
        );
        return $select;
        
    }
*/    
    public function _selectScientificName()
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('ss' => '_search_scientific'),
            array(
                'id' => 'ss.id',
                'name' =>
                    "TRIM(CONCAT(IF(ss.genus = '', '', ss.genus) " .
                    ", ' ', IF(ss.species = '', '', ss.species), ' ', " .
                    "IF(ss.infraspecies = '', '', ss.infraspecies)))",
                //'name_code' => new Zend_Db_Expr('""'),
                'rank' => new Zend_Db_Expr('""'),
                'rank_id' => 'IF(ss.infraspecies = "" OR ' .
                    'LENGTH(TRIM(ss.infraspecies)) = 0, ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ')',
                'name_status' => new Zend_Db_Expr('""'),
                'status' => 'ss.status',
                //'name_html' => new Zend_Db_Expr('""'),
                'genus' => 'ss.genus',
                'species' => 'ss.species',
                'infraspecies_marker' => 'ss.infraspecific_marker',
                'infraspecies' => 'ss.infraspecies',
                'author' => 'ss.author',
                //'additional_data' => new Zend_Db_Expr('""'),
                //'distribution' => new Zend_Db_Expr('""'),
                //'url' => new Zend_Db_Expr('""'),
                'source_database_id' => 'ss.source_database_id', // Fetch db name and uri separately!
                'record_scrutiny_date' => new Zend_Db_Expr('""'), // Fetch scrutiny separately!
                'online_resource' => new Zend_Db_Expr('""') // Fetch taxon url separately!
                )
            );
            return $select;
    }
/*
    public function scientificName($nameCode, $acceptedName) {
        
        $select = $this->_selectScientificName();
        $select->where('sn.name_code = ?', $nameCode);
        if ($acceptedName) {
            $select->where('sn.is_accepted_name = 1');
        }
        $res = $select->query()->fetchAll();
        
        return $res ? $res[0] : false;
        
    }
*/    
    public function scientificName($id, /*bool*/$acceptedName) {
        
        $select = $this->_selectScientificName();
        $select->where('ss.id = ?', $id);
        if ($acceptedName) {
            $select->where('ss.accepted_species_id = 0');
        }

        
 //echo $select->__toString(); die();
            
        $res = $select->query()->fetchAll();
        
        return $res ? $res[0] : false;
        
    }
/*    
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
*/    
    public function synonyms($id)
    {
        $select = $this->_selectScientificName();
        $select->where(
            'ss.accepted_species_id = ?', $id
        )
        ->group('ss.id')
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $res = $select->query()->fetchAll();
        
        return $res;
    }
/*    
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
*/
/*    public function classification($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from('_species_details')->where('taxon_id = ?', $id);
        $res = $select->query()->fetchAll();
        // Species; single row query in _species_details
        if ($res) {
            return $this->_speciesClassification($res);
        }
        // Higher taxon; transverse _taxon_tree
        return $this->_higherTaxonClassification($id);
    }
*/    
    public function getSpeciesClassification ($id) {
        $select = new Zend_Db_Select($this->_db);
        $select->from('_species_details')->where('taxon_id = ?', $id);
        $res = $select->query()->fetchAll();
        
        $classification = array();
        // Taxa used for classification are specified in Webservice model
        // already ordered from top to bottom
        foreach (ACI_Model_Webservice::$classificationRanks as $rank) {
            if ($res[0]["{$rank}_id"] > 0) {
                $classification[] = array(
                    'id' => $res[0]["{$rank}_id"],
                    'name' => $res[0]["{$rank}_name"],
                    'rank' => ACI_Model_Webservice::checkRank($rank),
                    'name_html' =>
                        $rank == 'genus'
                            ?
                            '<i>' . $res[0]["{$rank}_name"] . '</i>' :
                        (strstr($rank, 'species') === false 
                            ?
                            $res[0]["{$rank}_name"] :
                            ACI_Model_Table_Taxa::getAcceptedScientificName(
                                $res[0]['genus_name'],
                                $res[0]['species_name'],
                                $res[0]['infraspecies_name'],
                                $res[0]['infraspecific_marker'],
                                $res[0]['author']
                            )
                        ),
                    'url' => ACI_Model_Webservice::getTaxaUrl(
                        $res[0]["{$rank}_id"], 
                        (strstr($rank, 'species') === false 
                            ? 
                            0 : 
                            ACI_Model_Table_Taxa::RANK_SPECIES
                        ), 
                        $res[0]['status']
                    )
                );
            }
        }
        // Return the taxon itself
        array_pop($classification);
        return $classification;
    }
    
    public function getHigherTaxonClassification ($id) {
        $select = new Zend_Db_Select($this->_db);
        $select->from('_taxon_tree')->where('taxon_id = ?');

        $classification = array();
        do {
            $select->bind(array($id));
            $res = $select->query()->fetchAll();
            if (!count($res)) {
                break;
            }
            if ($res[0] > 0) {
                $classification[] = array(
                    'id' => $res[0]['taxon_id'],
                    'name' => $res[0]['name'],
                    'rank' => ACI_Model_Webservice::checkRank($res[0]['rank']),
                    'name_html' => $res[0]['name'],
                    'url' => ACI_Model_Webservice::getTaxaUrl(
                        $res[0]['taxon_id'], 0, ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME
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
    
/*    
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
*/    
    
    public function getChildTaxa($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tt' => '_taxon_tree'),
            array(
                'id' => 'sd.taxon_id',
                'name' => 'tt.name',
                'rank' => 'tt.rank',
                'status' => 'sd.status',
                'genus' => 'sd.genus_name',
                'species' => 'sd.species_name',
                'infraspecies_marker' => 'sd.infraspecific_marker',
                'infraspecies' => 'sd.infraspecies_name',
                'author' => 'sd.author'
            )
        )->joinLeft(
            array('sd' => '_species_details'),
            'tt.taxon_id = sd.taxon_id',
            array()
        )->where(
            'tt.parent_id = ?', $id
        )->order(
            'tt.name'
        );
        $res = $select->query()->fetchAll();
        
        $childTaxa = array();
        foreach($res as $taxon) {
            // Need to check rank first to catch infraspecies, 
            // which are stored with their infraspecific marker in_taxon_tree
            $rank = ACI_Model_Webservice::checkRank($taxon['rank']);
            $childTaxa[] = array(
                'id' => $taxon['id'],
                'name' => $taxon['name'],
                'rank' => $rank,
                'name_html' =>
                    $rank == 'Genus'
                        ?
                        '<i>' . $taxon['name'] . '</i>' :
                    ($rank != 'Species' && $rank != 'Infraspecies'
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
                    $taxon['id'], 
                    (strstr($rank, 'species') === false 
                        ? 
                        0 : 
                        ACI_Model_Table_Taxa::RANK_SPECIES
                    ), 
                    $taxon['status']
                )
            );
        }
        unset($res);
        return $childTaxa;
    }
     
/*    
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
*/    
/*    public function getAcceptedNameCodeFromId($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('sn' => 'scientific_names'),
            array('name_code' => 'sn.accepted_name_code')
        )->where('sn.record_id = ?', $id);
        return $select->query()->fetchColumn(0);
    }
*/    
    public function getLanguageAndCountry($id) {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('t1' => 'common_name'),
            array('language' => 't2.name', 'country' => 't3.name')
        )->joinLeft(
            array('t2' => 'language'),
            't1.language_iso = t2.iso',
            array()
        )->joinLeft(
            array('t3' => 'country'),
            't1.country_iso = t3.iso',
            array()
        )->where('t1.id = ?', $id);
        $res = $select->query()->fetchAll();
        return $res ? $res[0] : array('language' => '', 'country' => '');
    }
    
    public function getScrutinyDate($id) {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('t1' => 'scrutiny'),
            array('scrutiny' => 'original_scrutiny_date')
        )->joinLeft(
            array('t2' => 'taxon_detail'),
            't1.id = t2.scrutiny_id',
            array()
        )->where('t2.taxon_id = ?', $id);
        $res = $select->query()->fetchAll();
        return $res ? $res[0] : false;
    }
}