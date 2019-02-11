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
    // taxa now fetches common names without the need for a union select
    public function taxa($id, $name, $rank, $limit, $offset)
    {
        $select = new Eti_Db_Select($this->_db);

        $select
        ->sqlCalcFoundRows()
        ->from(
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
                'status' => 'IF(tst.name_status = 0, ' .
                    ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME .
                    ', tst.name_status)',
                'rank_id' => ACI_Model_Search::getRankDefinition(),
                'rank' => 'tst.rank',
                'source_database_id' => 'tst.source_database_id', // Fetch db full name and uri separately
                'sort_order' => 'tst.name_status',
                'is_extinct' => 'tst.is_extinct'
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
            $select->order(
                array(
                    new Zend_Db_Expr('sort_order'),
                    new Zend_Db_Expr('LOWER(tst.name)')
                )
            );
        }
        // by name
        else {

            $searchKey = ACI_Model_Search::wildcardHandling($name);
            $column = (preg_match('/\s/', $searchKey) ? 'name' : 'name_element');
            if ($column == 'name_element' || strstr($searchKey, '%')) {
                $select->where(
                    'tst.' . $column . ' ' . (strstr($searchKey, '%') ? 'LIKE' : '=') . ' ?',
                    $searchKey
                );
            } else {
                $name_elements = explode(' ', $searchKey);
                $shortString = false;
                foreach ($name_elements as $name_element) {
                	if (strlen($name_element) < 3) {
    		            $shortString = true;
                	}
                }
            	if (!strstr($searchKey, '%') && !$shortString) {
    	            $having = '';
    	            foreach ($name_elements as $name_element){
    	                $select->orWhere(
    	                    'tst.name_element = ?',
    	                    $name_element
    	                );
    	                $having .= ' AND `name` LIKE "%' . $name_element . '%"';
    	            }
            	} else {
                    $select->where(
    	                'tst.' . $column.' = "' . $searchKey . '"'
    	            );
    	        }
            }
        	if (!empty($rank)) {
                $select->where(
	                'tst.`rank` = "' . $rank . '"'
	            );
	        }
	        $select->order(
	            self::_getDefaultSortExpression($searchKey)
	        );
        }
        // Disable fossil search if module is switched off
        if ($this->_moduleEnabled('fossils') == 0) {
            $select->where('tst.`is_extinct` = 0');
        }
        $select->group(array('record_id'));
        if (isset($having)) {
            $select->having(
                'COUNT(tst.id) >= ' . count($name_elements) . $having
            );
        }
        $select->limit($limit, $offset);
        return $select->query()->fetchAll();
    }

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
                'rank' => new Zend_Db_Expr('""'),
                'rank_id' => 'IF(ss.infraspecies = "" OR ' .
                    'LENGTH(TRIM(ss.infraspecies)) = 0, ' .
                    ACI_Model_Table_Taxa::RANK_SPECIES . ', ' .
                    ACI_Model_Table_Taxa::RANK_INFRASPECIES . ')',
                'name_status' => new Zend_Db_Expr('""'),
                'status' => 'ss.status',
                'sn_id' => 'ss.accepted_species_id',
                'genus' => 'ss.genus',
                'subgenus' => 'ss.subgenus',
                'species' => 'ss.species',
                'infraspecies_marker' => 'ss.infraspecific_marker',
                'infraspecies' => 'ss.infraspecies',
                'author' => 'ss.author',
                'source_database_id' => 'ss.source_database_id', // Fetch db name and uri separately!
                'record_scrutiny_date' => new Zend_Db_Expr('""'), // Fetch scrutiny separately!
                'online_resource' => new Zend_Db_Expr('""'), // Fetch taxon url separately!
                'is_extinct' => 'ss.is_extinct'
            )
        );
        return $select;
    }

    public function scientificName($id, /*bool*/$acceptedName) {

        $select = $this->_selectScientificName();
        $select->where('ss.id = ?', $id);
        if ($acceptedName) {
            $select->where('ss.accepted_species_id = 0');
        }
        $res = $select->query()->fetchAll();
        return $res ? $res[0] : false;
    }

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
                                $res[0]['subgenus_name'],
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

    public function getChildTaxa($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tt' => '_taxon_tree'),
            array(
                'id' => 'tt.taxon_id',
                'name' => 'tt.name',
                'rank' => 'tt.rank',
                'status' => 'sd.status',
                'genus' => 'sd.genus_name',
                'subgenus' => 'sd.subgenus_name',
                'species' => 'sd.species_name',
                'infraspecies_marker' => 'sd.infraspecific_marker',
                'infraspecies' => 'sd.infraspecies_name',
                'author' => 'sd.author',
                'is_extinct' => 'tt.is_extinct'
            )
        )->joinLeft(
            array('sd' => '_species_details'),
            'tt.taxon_id = sd.taxon_id',
            array()
        )->where(
            'tt.parent_id = ?', $id
        );

        // Disable fossil search if module is switched off
        if ($this->_moduleEnabled('fossils') == 0) {
            $select->where('tt.`is_extinct` = 0');
        }

        $select->order(
            'tt.name'
        );
        $res = $select->query()->fetchAll();

        $childTaxa = array();
        foreach($res as $taxon) {
            // Need to check rank first to catch infraspecies,
            // which are stored as infraspecific markers in_taxon_tree
            $rank = ACI_Model_Webservice::checkRank($taxon['rank']);
            $childTaxa[] = array(
                'id' => $taxon['id'],
                'name' => $taxon['name'],
                'rank' => $rank,
                'name_html' =>
                    strtolower($rank) == 'genus'
                        ?
                        '<i>' . $taxon['name'] . '</i>' :
                    (strstr(strtolower($rank), 'species') === false
                        ?
                        $taxon['name'] :
                        ACI_Model_Table_Taxa::getAcceptedScientificName(
                            $taxon['genus'],
                            $taxon['subgenus'],
                            $taxon['species'],
                            $taxon['infraspecies'],
                            $taxon['infraspecies_marker'],
                            $taxon['author']
                        )
                    ),
                'url' => ACI_Model_Webservice::getTaxaUrl(
                    $taxon['id'],
                    (strstr(strtolower($rank), 'species') === false
                        ?
                        0 :
                        ACI_Model_Table_Taxa::RANK_SPECIES
                    ),
                    (strstr($rank, 'species') === false
                        ?
                        ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME :
                        $taxon['status']
                    )
                ),
                'is_extinct' => $taxon['is_extinct'] == 0 ? 'false' : 'true'
            );
        }
        unset($res);
        return $childTaxa;
    }

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
    
    // Clone from regular search; adapted for webservice query
    protected static function _getDefaultSortExpression($searchKey)
    {
        $regexpSearchKey = strtolower(str_replace('*', '.*', stripslashes($searchKey)));
        $mysqlSearchKey = strtolower(
            str_replace(array('*', '"'), array('%', "'"),
                stripslashes($searchKey))
            );
        
        return array(
            new Zend_Db_Expr(
                'IF(LENGTH(name) > 0, 1, 99)'
            ),
            new Zend_Db_Expr(
                'CONCAT(IF(name_status = 6, "D", "C"), "")'
            ),
            new Zend_Db_Expr(
                'CONCAT(IF(LOWER(name) REGEXP "^[^ ]*' . $regexpSearchKey . '", "E", "F"), "")'
            ),
            new Zend_Db_Expr('CONCAT(IF(LOWER(name) REGEXP
                "[[:<:]]' . $regexpSearchKey . '[[:>:]]", "G", "H"), name)'
            ),
            new Zend_Db_Expr(
                'IF(rank = "Phylum", "E",
        		  IF(rank = "Class", "F",
        		   IF(rank = "Order", "G",
        		    IF(rank = "Superfamily", "H",
        		     IF(rank = "Family", "I",
        		      IF(rank = "Genus", "J","K"
        		))))))')
        );
    }
    
    
    
    
    
    
}