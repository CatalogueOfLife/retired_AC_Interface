<?php
require_once 'AModel.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Details
 * Detail data handler
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Details extends AModel
{
    /**
     * Gets all the details of a species
     *
     * @param int $id
     * @param string $fromType common or taxa
     * @param int $fromId id of the common name or taxa of reference
     * @return ACI_Model_Table_Taxa
     */
    public function species($id, $fromType = null, $fromId = null)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $this->_logger->debug(__METHOD__ . " $id, $fromType, $fromId");
        
        $fields =
            array(
                'id' => 'td.taxon_id',
                'family_id' => 'tax_f.taxon_id',
                'kingdom' => 'taxn_k.name_element',
                'genus' => 'taxn_g.name_element',
                'species' => 'taxn_s.name_element',
                'infra_marker' => 'td.taxon_id',
                'infra' => 'taxn_i.name_element',
                'author' => 'as.string',
                'comment' => 'td.additional_data',
                'web_site' => 'td.taxon_id',//'uri.resource_identifier',
                'scrutiny_date' => 'sc.scrutiny_date',
                'status' => 'td.scientific_name_status_id',
                'specialist_name' => 'sp.name',
                'db_id' => 't.source_database_id',
                'lsid' => 'td.taxon_id',//'lsid.resource_identifier',
                'rank' => 't.taxonomic_rank_id'/*new Zend_Db_Expr(
                            'IF(t.taxon = "Infraspecies", ' .
                                ACI_Model_Table_Taxa::RANK_INFRASPECIES . ', ' .
                                ACI_Model_Table_Taxa::RANK_SPECIES . ')')*/
            );
            
        switch ($fromType) {
            case 'common':
                $extraFields = array(
                    'taxa_id' => 'cn.record_id',
                    'taxa_name' => 'cn.common_name',
                    'taxa_language' => 'cn.language',
                    'taxa_status' =>
                        new Zend_Db_Expr(
                            ACI_Model_Table_Taxa::STATUS_COMMON_NAME
                        )
                );
                $joinLeft = array(
                    array(
                        'name' => array('cn' => 'common_names'),
                        'cond' => 'cn.record_id = ' . (int)$fromId,
                        'columns' => array()
                    )
                );
                break;
            case 'taxa':
                $extraFields = array(
                    'taxa_id' => 'tx.record_id',
                    'taxa_name' => 'tx.name',
                    'taxa_status' => 'tx.sp2000_status_id',
                    'taxa_author' => 'snt.author'
                );
                $joinLeft = array(
                    array(
                        'name' => array('tx' => 'taxa'),
                        'cond' => 'tx.record_id = ' . (int)$fromId,
                        'columns' => array()
                    ),
                    array(
                        'name' => array('snt' => 'scientific_names'),
                        'cond' => 'tx.name_code = snt.name_code',
                        'columns' => array()
                    )
                );
                break;
            default:
                $extraFields = $joinLeft = array();
                break;
        }
        
        $select->from(
            array('td' => 'taxon_detail'),
            array_merge($fields, $extraFields)
        )
        ->joinRight(
            array('t' => 'taxon'),
            'td.taxon_id = t.id',
            array()
        )
        ->joinLeft(
            array('tax_i' => 'taxon_name_element'),
            '(t.taxonomic_rank_id != ' . ACI_Model_Table_Taxa::RANK_SPECIES . ' ' .
            'AND t.id = tax_i.taxon_id)',
            array()
        )
        ->joinLeft(
            array('taxn_i' => 'scientific_name_element'),
            'tax_i.scientific_name_element_id = taxn_i.id',
            array()
        )
        ->joinLeft(
            array('tax_s' => 'taxon_name_element'),
            '(t.taxonomic_rank_id = ' . ACI_Model_Table_Taxa::RANK_SPECIES . ' ' .
            'AND t.id = tax_s.taxon_id) OR tax_i.parent_id = tax_s.taxon_id',
            array()
        )
        ->joinRight(
            array('taxn_s' => 'scientific_name_element'),
            'tax_s.scientific_name_element_id = taxn_s.id',
            array()
        )
        ->joinLeft(
            array('tax_sg' => 'taxon_name_element'),
            'tax_s.parent_id = tax_sg.taxon_id',
            array()
        )
        ->joinLeft(
            array('tax_sg_t' => 'taxon'),
            'tax_sg.taxon_id = tax_sg_t.id AND tax_sg_t.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_SUBGENUS,
            array()
        )
        ->joinLeft(
            array('tax_g' => 'taxon_name_element'),
            '(tax_s.parent_id = tax_g.taxon_id AND tax_sg_t.id IS NULL) OR ' .
            '(tax_sg.parent_id = tax_g.taxon_id AND tax_sg_t.id IS NOT NULL)',
            array()
        )
        ->joinRight(
            array('taxn_g' => 'scientific_name_element'),
            'tax_g.scientific_name_element_id = taxn_g.id',
            array()
        )
        ->joinLeft(
            array('tax_f' => 'taxon_name_element'),
            'tax_g.parent_id = tax_f.taxon_id',
            array()
        )
        ->joinLeft(
            array('tax_sf' => 'taxon_name_element'),
            'tax_f.parent_id = tax_sf.taxon_id',
            array()
        )
        ->joinLeft(
            array('tax_sf_t' => 'taxon'),
            'tax_sf.taxon_id = tax_sf_t.id AND tax_sf_t.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_SUPERFAMILY,
            array()
        )
        ->joinLeft(
            array('tax_o' => 'taxon_name_element'),
            '(tax_f.parent_id = tax_o.taxon_id AND tax_sf_t.id IS NULL) OR ' .
            '(tax_sf.parent_id = tax_o.taxon_id AND tax_sf_t.id IS NOT NULL)',
            array()
        )
        ->joinLeft(
            array('tax_c' => 'taxon_name_element'),
            'tax_o.parent_id = tax_c.taxon_id',
            array()
        )
        ->joinLeft(
            array('tax_p' => 'taxon_name_element'),
            'tax_c.parent_id = tax_p.taxon_id',
            array()
        )
        ->joinLeft(
            array('tax_k' => 'taxon_name_element'),
            'tax_p.parent_id = tax_k.taxon_id',
            array()
        )
        ->joinRight(
            array('taxn_k' => 'scientific_name_element'),
            'tax_k.scientific_name_element_id = taxn_k.id',
            array()
        )
        ->joinRight(
            array('as' => 'author_string'),
            'td.author_string_id = as.id',
            array()
        )
        ->joinRight(
            array('sc' => 'scrutiny'),
            'td.scrutiny_id = sc.id',
            array()
        )
        ->joinLeft(
            array('sp' => 'specialist'),
            'sc.specialist_id = sp.id',
            array()
        )
        ->where('td.taxon_id = ?', (int)$id);
        
        foreach ($joinLeft as $jl) {
            $select->joinLeft($jl['name'], $jl['cond'], $jl['columns']);
        }
        
        $species = $select->query()->fetchObject('ACI_Model_Table_Taxa');
        
        if (!$species instanceof ACI_Model_Table_Taxa) {
            return false;
        }
        
        $db = new ACI_Model_Table_Databases();
        $dbDetails = $db->get($species->dbId);
        
        $species->dbImage   = $dbDetails['image'];
        $species->dbName    = $dbDetails['name'];
        $species->dbVersion = $dbDetails['version'];
        
        $species->hierarchy    = $this->speciesHierarchy($species->id);
        $species->synonyms     = $this->synonyms($species->id);
        $species->commonNames = $this->commonNames($species->id);
        $species->infraspecies = $this->infraspecies($species->id);
        $species->references   = $this->references($species->id);
        $species->distribution = $this->distributions($species->id);
        
        return $species;
    }
    
    /**
     * Returns the status of a scientific name
     *
     * @param int $id
     * @return int $status
     */
    public function speciesStatus($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('td' => 'taxon_detail'),
            array('status' => 'scientific_name_status_id')
        )->where('td.taxon_id = ?', (int)$id);
        return (int)$select->query()->fetchColumn(0);
    }
    
    /**
     * Returns the accepted name id and the taxa id for synonyms
     *
     * @param int $id
     * @return array $id
     */
    public function synonymLinks($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('sn' => 'scientific_names'),
            array('id' => 'sna.record_id', 'taxa_id' => 'tx.record_id')
        )->joinLeft(
            array('sna' => 'scientific_names'),
            'sn.accepted_name_code = sna.name_code AND ' .
            'sna.is_accepted_name = 1',
            array()
        )->joinLeft(
            array('tx' => 'taxa'),
            'tx.name_code = sn.name_code',
            array()
        )->where('sn.record_id = ?', (int)$id);
        $links = $select->query()->fetchAll();
        if (isset($links[0])) {
            return $links[0];
        }
        return array($id, null);
    }
    
    /**
     * Gets the species hierarchy by iterating the taxa table through
     * parent ids
     *
     * @param int $id
     * @return array Hierarchy data ordered by classification level,
     * from top to bottom
     */
    public function speciesHierarchy($id)
    {
        $cacheKey = $id . '_hierarchy';
        $cache = Zend_Registry::get('cache');
        // Try to load cached results
        $res = $cache ? $cache->load($cacheKey) : false;
        if (!$res) {
            $select = new Zend_Db_Select($this->_db);
            $select->from(
                array('tree' => 'temp_taxon_tree'),
                array(
                    'record_id' => 'tree.taxon_id',
                    'parent_id' => 'tree.parent_id',
                    'name' => 'tree.name',
                    'taxon' => 'tree.rank',
                    'LSID' => 'tree.lsid'
                )
            )->where('tree.taxon_id = ?');
                
            $hierarchy = array();
            
            do {
                $select->bind(array($id));
                $res = $select->query()->fetchAll();
                if (!count($res)) {
                    break;
                }
                if ($res[0] > 0) {
                    $hierarchy[] = $res[0];
                }
                $id = $res[0]['parent_id'];
            } while ($id > 0);

            $res = array_reverse($hierarchy);
            if ($cache) {
                $cache->save($res, $cacheKey);
            }
        }
        return $res;
    }
    
    /**
     * Gets the list of synonyms of a species and their details
     *
     * @param string $nameCode
     * @return array
     */
    public function synonyms($taxon_id)
    {
        $select = new Zend_Db_Select($this->_db);
        
        //TODO: Retrieve also the reference information
        $select->distinct()
        ->from(
            array('sn' => 'synonym'),
            array(
                'id' => 'sn.id',
                'name_code' => 'sn.id',
                'status' => 'sn.scientific_name_status_id',
                'genus' => 'snen_g.name_element',
                'species' => 'snen_s.name_element',
                'infraspecies_marker' => 'sn.id',
                'infraspecies' => 'snen_i.name_element',
                'author' => 'as.string',
                'num_references' => '(SELECT COUNT(*) FROM
                    reference_to_synonym WHERE synonym_id = sn.id)'
            )
        )->joinLeft(
            array('sne_g' => 'synonym_name_element'),
            'sn.id = sne_g.synonym_id AND sne_g.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_GENUS,
            array()
        )->joinLeft(
            array('snen_g' => 'scientific_name_element'),
            'sne_g.scientific_name_element_id = snen_g.id',
            array()
        )->joinLeft(
            array('sne_sg' => 'synonym_name_element'),
            'sn.id = sne_sg.synonym_id AND sne_sg.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_SUBGENUS,
            array()
        )->joinLeft(
            array('snen_sg' => 'scientific_name_element'),
            'sne_sg.scientific_name_element_id = snen_sg.id',
            array()
        )->joinLeft(
            array('sne_s' => 'synonym_name_element'),
            'sn.id = sne_s.synonym_id AND sne_s.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_SPECIES,
            array()
        )->joinLeft(
            array('snen_s' => 'scientific_name_element'),
            'sne_s.scientific_name_element_id = snen_s.id',
            array()
        )->joinLeft(
            array('sne_i' => 'synonym_name_element'),
            'sn.id = sne_i.synonym_id AND sne_i.taxonomic_rank_id != ' .
            ACI_Model_Table_Taxa::RANK_GENUS . ' AND sne_i.taxonomic_rank_id != ' .
            ACI_Model_Table_Taxa::RANK_SUBGENUS . ' AND sne_i.taxonomic_rank_id != ' .
            ACI_Model_Table_Taxa::RANK_SPECIES,
            array()
        )->joinLeft(
            array('snen_i' => 'scientific_name_element'),
            'sne_i.scientific_name_element_id = snen_i.id',
            array()
        )->joinLeft(
            array('as' => 'author_string'),
            'sn.author_string_id = as.id',
            array()
        )
        ->where(
            'sn.taxon_id = ?'
        )
        ->group('sn.id')
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $select->bind(array($taxon_id));
        
        $synonyms = $select->query()->fetchAll();
        
        foreach ($synonyms as &$synonym) {
            $synonym['name'] =
                ACI_Model_Table_Taxa::getAcceptedScientificName(
                    $synonym['genus'],
                    $synonym['species'],
                    $synonym['infraspecies'],
                    $synonym['infraspecies_marker'],
                    $synonym['author']
                );
            $synonym['status'] =
                ACI_Model_Table_Taxa::getStatusString(
                    $synonym['status'], false
                );
        }
        
        return $synonyms;
    }
    
    /**
     * Gets the list of common names of a species and their details
     *
     * @param string $nameCode
     * @return array
     */
    public function commonNames ($taxon_id)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->distinct()
        ->from(
            array('cn' => 'common_name'),
            array(
                'id' => 'cn.id',
                'common_name' => 'cne.name',
                'language' => 'l.name',
                'country' => 'c.name',
                'num_references' => '(SELECT COUNT(*) FROM
                    reference_to_common_name WHERE common_name_id = cn.id)',
                'references' => 'GROUP_CONCAT(r.reference_id)'
            )
        )
        ->joinRight(
            array('cne' => 'common_name_element'),
            'cn.common_name_element_id = cne.id',
            array()
        )->joinLeft(
            array('r' => 'reference_to_common_name'),
            'cn.id = r.common_name_id',
            array()
        )->joinLeft(
            array('l' => 'language'),
            'cn.language_iso = l.iso',
            array()
        )->joinLeft(
            array('c' => 'country'),
            'cn.country_iso = c.iso',
            array()
        )
        ->where('cn.taxon_id = ?', $taxon_id)
        ->group(array('common_name', 'language', 'country'))
        ->order(array('language', 'common_name', 'country'));
        
        return $select->query()->fetchAll();
    }
    
    public function infraspecies($taxon_id)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select
        ->from(
            array('t' => 'taxon'),
            array(
                'id' => 't.id',
                'infraspecies' => 'sne_i.name_element',
                'infraspecies_marker' => 't.id',
                'author' => 'as.string',
                'name' =>
                    "TRIM(CONCAT(IF(sne_g.name_element IS NULL, '', sne_g.name_element) " .
                    ", ' ', IF(sne_s.name_element IS NULL, '', sne_s.name_element)))"
            )
        )
        ->joinRight(
            array('tne_s' => 'taxon_name_element'),
            't.id = tne_s.taxon_id',
            array()
        )->joinRight(
            array('sne_s' => 'scientific_name_element'),
            'tne_s.scientific_name_element_id = sne_s.id',
            array()
        )->joinRight(
            array('tne_i' => 'taxon_name_element'),
            'tne_s.taxon_id = tne_i.parent_id',
            array()
        )->joinRight(
            array('sne_i' => 'scientific_name_element'),
            'tne_i.scientific_name_element_id = sne_i.id',
            array()
        )->joinRight(
            array('tne_g' => 'taxon_name_element'),
            'tne_g.taxon_id = tne_s.parent_id',
            array()
        )->joinRight(
            array('sne_g' => 'scientific_name_element'),
            'tne_g.scientific_name_element_id = sne_g.id',
            array()
        )->joinRight(
            array('td_i' => 'taxon_detail'),
            'tne_i.taxon_id = td_i.taxon_id',
            array()
        )->joinRight(
            array('as' => 'author_string'),
            'td_i.author_string_id = as.id',
            array()
        )
        ->where('t.id = ?')
        ->order(array('infraspecies', 'infraspecies_marker'));
        
        $select->bind(array($taxon_id));
        
        $rowSet = $select->query()->fetchAll();
        
        $infraspecies = array();
        $i = 0;
        foreach ($rowSet as $row) {
            $infraspecies[$i]['id'] = $row['id'];
            $infraspecies[$i]['name'] =
                ACI_Model_Table_Taxa::getAcceptedScientificName(
                    $genus, $species, $row['infraspecies'],
                    $row['infraspecies_marker'], $row['author']
                );
            $infraspecies[$i]['url'] = '/details/species/id/' . $row['id'];
            $i++;
        }
        return $infraspecies;
    }
    
    /**
     * Gets all the ditributions of a particular name code
     *
     * @param string $nameCode
     * @return array
     */
    public function distributions ($taxon_id)
    {
        $distribtion = new Zend_Db_Select($this->_db);
        $distribution_free_text = new Zend_Db_Select($this->_db);
        $select = new Zend_Db_Select($this->_db);
        
        $distribtion
        ->from(
            array('d' => 'distribution'),
            array(
                'distribution' => 'r.name'
            )
        )
        ->joinRight(
            array('r' => 'region'),
            'd.region_id = r.id',
            array()
        )
        ->where('d.taxon_detail_id = ?', $taxon_id);
        
        $distribution_free_text
        ->from(
            array('d' => 'distribution_free_text'),
            array(
                'distribution' => 'd.free_text'
            )
        )
        ->where('d.taxon_detail_id = ?', $taxon_id);
        
        $select->union(array($distribtion,$distribution_free_text))
        ->order('distribution');
        
        $rowSet = $select->query()->fetchAll();
        
        $dist = array();
        foreach ($rowSet as $row) {
            $dist[] = $row['distribution'];
        }
        return $dist;
    }
    
    /**
     * Alias of getReferencesByNameCode
     * @param $nameCode
     * @return array $references
     */
    public function references($nameCode)
    {
        return $this->getReferencesByNameCode($nameCode);
    }
    
    public function getReferenceById($id)
    {
        $modelRef = new ACI_Model_Table_References();
        return $modelRef->get($id);
    }
    
    public function getReferencesByNameCode($nameCode)
    {
        $modelRef = new ACI_Model_Table_References();
        return $modelRef->getByNameCode($nameCode);
    }
    
    public function getScientificName($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('sn' => 'scientific_names'),
            array(
                'sn.genus',
                'sn.species',
                'sn.infraspecies',
                'sn.infraspecies_marker',
                'sn.author',
                'sn.name_code'
            )
        )
        ->where('sn.record_id = ?', $id);
        $species = $select->query()->fetchObject('ACI_Model_Table_Taxa');
        return $species;
    }
}