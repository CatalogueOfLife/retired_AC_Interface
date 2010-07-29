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
                'name_code' => 'td.taxon_id',
                'accepted_name_code' => 'td.taxon_id',
                'author' => 'as.string',
                'comment' => 'td.additional_data',
                'web_site' => 'td.taxon_id',//'uri.resource_identifier',
                'scrutiny_date' => 'sc.scrutiny_date',
                'status' => 'td.scientific_name_status_id',
                'specialist_name' => 'sp.name',
                'db_id' => 't.source_database_id',
                'sn_taxa_id' => 't.id',
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
        var_dump($species);
        if (!$species instanceof ACI_Model_Table_Taxa) {
            return false;
        }
        
        $db = new ACI_Model_Table_Databases();
        $dbDetails = $db->get($species->dbId);
        
        $species->dbImage   = $dbDetails['image'];
        $species->dbName    = $dbDetails['database_name'];
        $species->dbVersion = $dbDetails['version'];
        
        $species->hierarchy    = $this->speciesHierarchy($species->snTaxaId);
        $species->synonyms     = $this->synonyms($species->nameCode);
        $species->commonNames = $this->commonNames($species->nameCode);
        $species->infraspecies =
            $this->infraspecies($species->genus, $species->species);
        $species->references   = $this->references($species->nameCode);
        $species->distribution = $this->distributions($species->nameCode);
        
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
    public function synonyms($nameCode)
    {
        $select = new Zend_Db_Select($this->_db);
        
        //TODO: Retrieve also the reference information
        $select->distinct()
        ->from(
            array('sn' => 'scientific_names'),
            array(
                'id' => 'sn.record_id',
                'sn.name_code',
                'status' => 'sn.sp2000_status_id',
                'sn.genus',
                'sn.species',
                'sn.infraspecies_marker',
                'sn.infraspecies',
                'sn.author',
                'num_references' => new Zend_Db_Expr(
                    'IF(snr.name_code IS NULL, 0, COUNT(*))'
                )
            )
        )->joinLeft(
            array('snr' => 'scientific_name_references'),
            'sn.name_code = snr.name_code',
            array()
        )
        ->where(
            'sn.accepted_name_code = ? AND sn.is_accepted_name = ?'
        )
        ->group('sn.name_code')
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $select->bind(array($nameCode, 0));
        
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
    public function commonNames ($nameCode)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->distinct()
        ->from(
            array('cn' => 'common_names'),
            array(
                'id' => 'cn.record_id',
                'cn.common_name',
                'cn.language',
                'cn.country',
                'num_references' => 'IF(reference_id IS NULL OR ' .
                    'reference_id = "", SUM(0), SUM(1))',
                'references' => 'GROUP_CONCAT(reference_id)'
            )
        )
        ->joinLeft(
            array('r' => 'references'),
            'cn.reference_id = r.record_id',
            array()
        )
        ->where('cn.name_code = ?', $nameCode)
        ->group(array('cn.common_name', 'cn.language', 'cn.country'))
        ->order(array('cn.language', 'cn.common_name', 'cn.country'));
        
        return $select->query()->fetchAll();
    }
    
    public function infraspecies($genus, $species)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select
        ->from(
            array('sn' => 'scientific_names'),
            array(
                'id' => 'sn.record_id',
                'sn.infraspecies',
                'sn.infraspecies_marker',
                'sn.author',
                'name' =>
                    "TRIM(CONCAT(IF(sn.genus IS NULL, '', sn.genus) " .
                    ", ' ', IF(sn.species IS NULL, '', sn.species)))"
            )
        )
        ->where(
            'sn.genus = ? AND sn.species = ? AND ' .
            '(sn.infraspecies IS NOT NULL AND sn.infraspecies != "") AND ' . 
            'sn.is_accepted_name = ?'
        )
        ->order(array('infraspecies', 'infraspecies_marker'));
        
        $select->bind(array($genus, $species, 1));
        
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
    public function distributions ($nameCode)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select
        ->from(
            array('d' => 'distribution'),
            array(
                'd.distribution'
            )
        )
        ->where('d.name_code = ?', $nameCode)
        ->order('d.distribution');
        
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