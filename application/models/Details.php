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
                'id' => 'taxon_id',
                'kingdom' => 'kingdom_name',
                'phylum' => 'phylum_name',
                'class' => 'class_name',
                'order' => 'order_name',
                'superfamily' => 'superfamily_name',
                'family' => 'family_name',
                'genus' => 'genus_name',
                'subgenus' => 'subgenus_name',
                'species' => 'species_name',
                'infra' => 'infraspecies_name',
                'kingdom_id' => 'kingdom_id',
                'phylum_id' => 'phylum_id',
                'class_id' => 'class_id',
                'order_id' => 'order_id',
                'superfamily_id' => 'superfamily_id',
                'family_id' => 'family_id',
                'genus_id' => 'genus_id',
                'subgenus_id' => 'subgenus_id',
                'species_id' => 'species_id',
                'infra_id' => 'infraspecies_id',
                'kingdom_lsid' => 'kingdom_lsid',
                'phylum_lsid' => 'phylum_lsid',
                'class_lsid' => 'class_lsid',
                'order_lsid' => 'order_lsid',
                'superfamily_lsid' => 'superfamily_lsid',
                'family_lsid' => 'family_lsid',
                'genus_lsid' => 'genus_lsid',
                'subgenus_lsid' => 'subgenus_lsid',
                'species_lsid' => 'species_lsid',
                'infra_lsid' => 'infraspecies_lsid',
            	'infraspecific_marker' => 'infraspecific_marker',
                'author',
                'status',
                'comment' => 'additional_data',
                'dbId' => 'source_database_id',
                'dbName' => 'source_database_short_name',
                'dbVersion' => 'source_database_release_date',
                'scrutinyDate' => 'scrutiny_date',
                'specialistName' => 'specialist',
                'pointOfAttachmentId' => 'point_of_attachment_id',
                'is_extinct' => 'is_extinct'
            );
            
        switch ($fromType) {
            case 'common':
                $extraFields = array(
                    'taxa_id' => 'cn.id',
                    'taxa_name' => 'cne.name',
                    'taxa_language' => 'l.name',
                    'taxa_status' =>
                        new Zend_Db_Expr(
                            ACI_Model_Table_Taxa::STATUS_COMMON_NAME
                        )
                );
                $joinLeft = array(
                    array(
                        'name' => array('cn' => 'common_name'),
                        'cond' => 'cn.id = ' . (int)$fromId,
                        'columns' => array()
                    ),
                    array(
                        'name' => array('cne' => 'common_name_element'),
                        'cond' => 'cn.common_name_element_id = cne.id',
                        'columns' => array()
                    ),
                    array(
                        'name' => array('l' => 'language'),
                        'cond' => 'cn.language_iso = l.iso',
                        'columns' => array()
                    )
                );
                break;
            case 'taxa':
                $extraFields = array(
                    'taxa_id' => 'sn.id',
                    'taxa_name' => 'CONCAT(snen_g.name_element,'.
                        'IF(snen_sg.name_element IS NOT NULL,'.
                            'CONCAT(" ",snen_sg.name_element),""'.
                        ')," ",snen_s.name_element,'.
                        'IF(snen_ss.name_element IS NOT NULL,CONCAT('.
                'IF(kingdom_name != "animalia" AND tr.marker_displayed IS NOT NULL,
                   CONCAT(" ",tr.marker_displayed),""),'.
                            '" ",snen_ss.name_element'.
                        '),"")'.
                    ')',
                    'taxa_author' => 'ass.string',
                    'taxa_status' => 'sn.scientific_name_status_id'
                );
                $joinLeft = array(
                    array(
                        'name' => array('sn' => 'synonym'),
                        'cond' => 'sn.id = ' . (int)$fromId,
                        'columns' => array()
                    ),
                    array(
                        'name' => array('sne_g' => 'synonym_name_element'),
                        'cond' => 'sn.id = sne_g.synonym_id AND sne_g.taxonomic_rank_id = ' .
                            ACI_Model_Table_Taxa::RANK_GENUS,
                        'columns' => array()
                    ),
                    array(
                        'name' => array('snen_g' => 'scientific_name_element'),
                        'cond' => 'sne_g.scientific_name_element_id = snen_g.id',
                        'columns' => array()
                    ),
                    array(
                        'name' => array('sne_sg' => 'synonym_name_element'),
                        'cond' => 'sn.id = sne_sg.synonym_id AND sne_sg.taxonomic_rank_id = ' .
                            ACI_Model_Table_Taxa::RANK_SUBGENUS,
                        'columns' => array()
                    ),
                    array(
                        'name' => array('snen_sg' => 'scientific_name_element'),
                        'cond' => 'sne_sg.scientific_name_element_id = snen_sg.id',
                        'columns' => array()
                    ),
                    array(
                        'name' => array('sne_s' => 'synonym_name_element'),
                        'cond' => 'sn.id = sne_s.synonym_id AND sne_s.taxonomic_rank_id = ' .
                            ACI_Model_Table_Taxa::RANK_SPECIES,
                        'columns' => array()
                    ),
                    array(
                        'name' => array('snen_s' => 'scientific_name_element'),
                        'cond' => 'sne_s.scientific_name_element_id = snen_s.id',
                        'columns' => array()
                    ),
                    array(
                        'name' => array('sne_ss' => 'synonym_name_element'),
                        'cond' => 'sn.id = sne_ss.synonym_id AND sne_ss.taxonomic_rank_id NOT IN (' .
                            ACI_Model_Table_Taxa::RANK_GENUS . ',' .
                            ACI_Model_Table_Taxa::RANK_SUBGENUS . ',' .
                            ACI_Model_Table_taxa::RANK_SPECIES . ')',
                        'columns' => array()
                    ),
                    array(
                        'name' => array('snen_ss' => 'scientific_name_element'),
                        'cond' => 'sne_ss.scientific_name_element_id = snen_ss.id',
                        'columns' => array()
                    ),
                    array(
                        'name' => array('ass' => 'author_string'),
                        'cond' => 'sn.author_string_id = ass.id',
                        'columns' => array()
                    ),
                    array(
                        'name' => array('tr' => 'taxonomic_rank'),
                        'cond' => 'sne_ss.taxonomic_rank_id = tr.id',
                        'columns' => array()
                    )
                );
                break;
            default:
                $extraFields = $joinLeft = array();
                break;
        }
        
        $select->from(
            array('dsd' => '_species_details'),
            array_merge($fields, $extraFields)
        )
        ->where('dsd.taxon_id = ?', (int)$id);
        
        foreach ($joinLeft as $jl) {
            $select->joinLeft($jl['name'], $jl['cond'], $jl['columns']);
        }
        
        $species = $select->query()->fetchObject('ACI_Model_Table_Taxa');
        
        if (!$species instanceof ACI_Model_Table_Taxa) {
            return false;
        }
        $species->lsid      = ($species->id == $species->species_id ?
            $species->species_lsid : $species->infra_lsid);
        $species->urls      = $this->getUrls($species->id);
        
        $species->dbImage      = '/images/databases/' .
            str_replace(' ','_',$species->dbName) . '.png';
        $species->hierarchy    = $this->getHierachyFromSpecies($species);
        $species->commonNames = $this->commonNames($species->id);
        $species->lifezones = $this->lifezones($species->id);
        $species->references   = $this->getReferencesByTaxonId($species->id);
        $species->distribution = $this->distributions($species->id);
        $species->synonyms     = $this->synonyms($species->id, $species->kingdom);
        $species->infraspecies = $this->infraspecies($species->id, $species->kingdom);
        if ($this->_moduleEnabled('images_database')) {
            $species->images = $this->getImages($species->id);
        }
        if ($this->_moduleEnabled('indicators')) {
            $sourceDatabaseQualifiers = $this->getSourceDatabaseQualifiers($species->dbId);
            $species->dbCoverage = $sourceDatabaseQualifiers['dbCoverage'];
            $species->dbCompleteness = $sourceDatabaseQualifiers['dbCompleteness'];
            $species->dbConfidence = $sourceDatabaseQualifiers['dbConfidence'];
        }
        return $species;
    }
    
    private function _setPointOfAttachment($currentRecordId, $species) {
        if ($species->pointOfAttachmentId == $currentRecordId) {
            $species->pointOfAttachment = $species->dbName;
            $species->pointOfAttachmentLinkId = $species->dbId;
        }
    }
    
    public function getHierachyFromSpecies($species)
    {
        if($species->kingdom)
        {
            $this->_setPointOfAttachment($species->kingdom_id, $species);
            $res[] = array(
                'record_id' => $species->kingdom_id,
                'parent_id' => '',
                'name' => $species->kingdom,
                'taxon' => '',
                'LSID' => $species->kingdom_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->phylum)
        {
            $this->_setPointOfAttachment($species->phylum_id, $species);
            $res[] = array(
                'record_id' => $species->phylum_id,
                'parent_id' => '',
                'name' => $species->phylum,
                'taxon' => 'phylum',
                'LSID' => $species->phylum_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->class)
        {
            $this->_setPointOfAttachment($species->class_id, $species);
            $res[] = array(
                'record_id' => $species->class_id,
                'parent_id' => '',
                'name' => $species->class,
                'taxon' => 'class',
                'LSID' => $species->class_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->order)
        {
            $this->_setPointOfAttachment($species->order_id, $species);
            $res[] = array(
                'record_id' => $species->order_id,
                'parent_id' => '',
                'name' => $species->order,
                'taxon' => 'order',
                'LSID' => $species->order_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->superfamily)
        {
            $this->_setPointOfAttachment($species->superfamily_id, $species);
            $res[] = array(
                'record_id' => $species->superfamily_id,
                'parent_id' => '',
                'name' => $species->superfamily,
                'taxon' => 'superfamily',
                'LSID' => $species->superfamily_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->family)
        {
            $this->_setPointOfAttachment($species->family_id, $species);
            $res[] = array(
                'record_id' => $species->family_id,
                'parent_id' => '',
                'name' => $species->family,
                'taxon' => 'family',
                'LSID' => $species->family_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->genus)
        {
            $this->_setPointOfAttachment($species->genus_id, $species);
            $res[] = array(
                'record_id' => $species->genus_id,
                'parent_id' => '',
                'name' => $species->genus,
                'taxon' => 'genus',
                'LSID' => $species->genus_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->subgenus)
        {
            $this->_setPointOfAttachment($species->subgenus_id, $species);
            $res[] = array(
                'record_id' => $species->subgenus_id,
                'parent_id' => '',
                'name' => $species->subgenus,
                'taxon' => 'subgenus',
                'LSID' => $species->subgenus_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->species)
        {
            $this->_setPointOfAttachment($species->species_id, $species);
            $res[] = array(
                'record_id' => $species->species_id,
                'parent_id' => '',
                'name' => $species->species,
                'taxon' => 'species',
                'LSID' => $species->species_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        if($species->infra)
        {
            $this->_setPointOfAttachment($species->infra_id, $species);
            $res[] = array(
                'record_id' => $species->infra_id,
                'parent_id' => '',
                'name' => $species->infra,
                'taxon' => 'infraspecies',
                'LSID' => $species->infa_lsid,
                'sourceDb' => $species->pointOfAttachment,
                'sourceDbId' => $species->pointOfAttachmentLinkId
            );
        }
        return $res;
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
        // $cache = Zend_Registry::get('cache');
        // Try to load cached results
        // $res = $cache ? $cache->load($cacheKey) : false;
        $res = $this->_fetchFromCache($cacheKey);
        if (!$res) {
            $select = new Zend_Db_Select($this->_db);
            $select->from(
                array('tree' => '_taxon_tree'),
                array(
                    'record_id' => 'tree.taxon_id',
                    'parent_id' => 'tree.parent_id',
                    'name' => 'tree.name',
                    'taxon' => 'tree.rank',
                    'LSID' => 'tree.lsid',
                    'is_extinct' => 'tree.is_extinct'
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
/*          if ($cache) {
                $cache->save($res, $cacheKey);
            }
*/          $this->_storeInCache($res, $cacheKey);
        }
        return $res;
    }
    
    /**
     * Gets the list of synonyms of a species and their details
     *
     * @param string $nameCode
     * @return array
     */
    public function synonyms($taxon_id, $kingdom = '')
    {
        $select = new Zend_Db_Select($this->_db);
        $select->distinct()
        ->from(
            array('sn' => 'synonym'),
            array(
                'id' => 'sn.id',
                'name_code' => 'sn.id',
                'status' => 'sn.scientific_name_status_id',
                'genus' => 'snen_g.name_element',
                'species' => 'snen_s.name_element',
                'infraspecies' => 'snen_i.name_element',
                'infraspecific_marker' => 'tr.marker_displayed',
                'author' => 'as.string',
                'num_references' => '(SELECT COUNT(*) FROM
                    reference_to_synonym WHERE synonym_id = sn.id)',
                'rank' => 'IF(sne_i.taxonomic_rank_id IS NOT NULL,'.
                    'sne_i.taxonomic_rank_id,'.
                    'IF(sne_s.taxonomic_rank_id IS NOT NULL,'.
                    'sne_s.taxonomic_rank_id,sne_g.taxonomic_rank_id))'
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
        )->joinLeft(
            array('tr' => 'taxonomic_rank'),
            'sne_i.taxonomic_rank_id = tr.id',
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
                    $synonym['rank'],
                    $synonym['author'],
                    $synonym['infraspecific_marker'],
                    $kingdom
                    );
            $synonym['status'] =
                ACI_Model_Table_Taxa::getStatusString(
                    $synonym['status'], false
                );
        }
        
        return $synonyms;
    }

/*    public function synonyms($taxon_id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->distinct()
        ->from(
            array('sa' => '_search_all'),
            array(
                'id' => 'sa.id',
                'name_code' => 'sa.id',
                'status' => 'sa.name_status',
                'name' => 'sa.name',
                'author' => 'sa.name_suffix',
                'num_references' => '(SELECT COUNT(*) FROM
                    reference_to_synonym WHERE synonym_id = sa.id)',
                'rank' => 'sa.rank'
            )
        )->where(
            'sa.accepted_taxon_id = ?AND 
            sa.name_status != '.ACI_Model_Table_Taxa::STATUS_COMMON_NAME
         )
        ->group('sa.id')
        ->order(array('name'));
        
        $select->bind(array($taxon_id));
        
        $synonyms = $select->query()->fetchAll();
        
        foreach ($synonyms as &$synonym) {
            $synonym['name'] = ACI_Model_Table_Taxa::getTaxaFullName($synonym['name'], 
                    $synonym['status'], $synonym['author'], '');
            $synonym['status'] =
                ACI_Model_Table_Taxa::getStatusString(
                    $synonym['status'], false
                );
        }
        
        return $synonyms;
    }
*/    
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
                'transliteration' => 'cne.transliteration',
                'country' => 'c.name',
                'region' => 'rft.free_text',
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
        )->joinLeft(
            array('rft' => 'region_free_text'),
            'rft.id = cn.region_free_text_id',
            array()
        )
        ->where('cn.taxon_id = ?', $taxon_id)
        ->group(array('common_name', 'language', 'country'))
        ->order(array('language', 'common_name', 'country'));
        
        return $select->query()->fetchAll();
    }
    
    public function infraspecies($taxon_id, $kingdom = '')
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select
        ->from(
            array('tne_s' => 'taxon_name_element'),
            array(
                'id' => 'tne_i.taxon_id',
                'genus' => 'sne_g.name_element',
                'species' => 'sne_s.name_element',
                'infraspecies' => 'sne_i.name_element',
                'infraspecific_marker' => 'tr.marker_displayed',
                'author' => 'aus.string',
                'name' =>
                    "CONCAT_WS(\" \",sne_g.name_element,sne_s.name_element,sne_i.name_element)",
                'rank' => 't.taxonomic_rank_id'
            
            )
        )
        ->joinLeft(
            array('tne_i' => 'taxon_name_element'),
            'tne_s.taxon_id = tne_i.parent_id',
            array()
        )->joinLeft(
            array('tne_g' => 'taxon_name_element'),
            'tne_g.taxon_id = tne_s.parent_id',
            array()
        )->joinLeft(
            array('sne_g' => 'scientific_name_element'),
            'tne_g.scientific_name_element_id = sne_g.id',
            array()
        )->joinLeft(
            array('sne_s' => 'scientific_name_element'),
            'tne_s.scientific_name_element_id = sne_s.id',
            array()
        )->joinLeft(
            array('sne_i' => 'scientific_name_element'),
            'tne_i.scientific_name_element_id = sne_i.id',
            array()
        )->joinLeft(
            array('td' => 'taxon_detail'),
            'tne_i.taxon_id = td.taxon_id',
            array()
        )->joinLeft(
            array('aus' => 'author_string'),
            'td.author_string_id = aus.id',
            array()
        )->joinLeft(
            array('t' => 'taxon'),
            'tne_i.taxon_id = t.id',
            array()
        )->joinLeft(
            array('tr' => 'taxonomic_rank'),
            't.taxonomic_rank_id = tr.id',
            array()
        )
        ->where('tne_s.taxon_id = ? AND t.taxonomic_rank_id != 83')
        ->order(array('infraspecies', 'infraspecific_marker'));
        
        $select->bind(array($taxon_id));
        
        $rowSet = $select->query()->fetchAll();
        
        $infraspecies = array();
        $i = 0;
        foreach ($rowSet as $row) {
            $infraspecies[$i]['id'] = $row['id'];
            $infraspecies[$i]['name'] =
                ACI_Model_Table_Taxa::getAcceptedScientificName(
                    $row['genus'], $row['species'], $row['infraspecies'],
                    $row['rank'], $row['author'], $row['infraspecific_marker'], $kingdom
                );
            $infraspecies[$i]['url'] = '/details/species/id/' . $row['id'];
            $i++;
        }
        return $infraspecies;
    }

/*    public function infraspecies($taxon_id)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select
        ->from(
            array('tt' => '_taxon_tree'),
            array(
                'id' => 'tt.taxon_id',
                'name' => 'tt.name',
                'author' => 'aus.string'
            )
        )->joinLeft(
            array('td' => 'taxon_detail'),
            'tt.taxon_id = td.taxon_id',
            array()
        )->joinLeft(
            array('aus' => 'author_string'),
            'td.author_string_id = aus.id',
            array()
        )
        ->where('tt.parent_id = ?')
        ->order(array('name'));
        
        $select->bind(array($taxon_id));
        
        $rowSet = $select->query()->fetchAll();
        
        $infraspecies = array();
        $i = 0;
        foreach ($rowSet as $row) {
            $infraspecies[$i]['id'] = $row['id'];
            $infraspecies[$i]['name'] = ACI_Model_Table_Taxa::italicizeName($row['name']) . ' ' . $row['author'];
            $infraspecies[$i]['url'] = '/details/species/id/' . $row['id'];
            $i++;
        }
        return $infraspecies;
    }
*/   
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
                'distribution' => 'r.name',
            	'id' => 'r.id',
            	'region_standard' => 'r.region_standard_id'
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
                'distribution' => 'rft.free_text',
            	'id' => 'rft.id',
            	'region_standard' => new Zend_Db_Expr(0)
            )
        )
        ->joinRight(
            array('rft' => 'region_free_text'),
            'd.region_free_text_id = rft.id',
            array()
        )
        ->where('d.taxon_detail_id = ?', $taxon_id);
        
        $select->union(array($distribtion,$distribution_free_text))
        ->order('distribution');
        
        $rowSet = $select->query()->fetchAll();
        
        $dist = array();
        foreach ($rowSet as $row) {
            $dist[] = array(
            	'distribution' => $row['distribution'],
            	'id' => $row['id'],
            	'region_standard' => $row['region_standard']
            );
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
        //return $this->getReferencesByNameCode($nameCode);
    }
    
    public function getReferenceById($id)
    {
        $modelRef = new ACI_Model_Table_References();
        return $modelRef->get($id);
    }
    
    public function getReferencesByTaxonId($taxon_id)
    {
        $modelRef = new ACI_Model_Table_References();
        return $modelRef->getByTaxonId($taxon_id);
    }
        
    public function getReferencesBySynonymId($synonym_id)
    {
        $modelRef = new ACI_Model_Table_References();
        return $modelRef->getBySynonymId($synonym_id);
    }
    
    public function getScientificName($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('t' => 'taxon'),
            array(
                'genus' => 'IF(t.taxonomic_rank_id = 83, sne_2.name_element, sne_3.name_element)',
                'species' => 'IF(t.taxonomic_rank_id = 83, sne_1.name_element, sne_2.name_element)',
                'infraspecies' => 'IF(t.taxonomic_rank_id = 83, "", sne_1.name_element)',
                'infraspecies_marker' => '',
                'author' => 'as.string',
                'id' => 't.id'
            )
        )
        ->joinRight(
            array('tne_1' => 'taxon_name_element'),
            't.id = tne_1.taxon_id',
            array()
        )
        ->joinRight(
            array('tne_2' => 'taxon_name_element'),
            'tne_2.taxon_id = tne_1.parent_id',
            array()
        )
        ->joinRight(
            array('tne_3' => 'taxon_name_element'),
            'tne_3.taxon_id = tne_2.parent_id',
            array()
        )
        ->joinRight(
            array('sne_1' => 'scientific_name_element'),
            'tne_1.scientific_name_element_id = sne_1.id',
            array()
        )
        ->joinRight(
            array('sne_2' => 'scientific_name_element'),
            'tne_2.scientific_name_element_id = sne_2.id',
            array()
        )
        ->joinRight(
            array('sne_3' => 'scientific_name_element'),
            'tne_3.scientific_name_element_id = sne_3.id',
            array()
        )
        ->joinRight(
            array('td' => 'taxon_detail'),
            't.id = td.taxon_id',
            array()
        )
        ->joinLeft(
            array('as' => 'author_string'),
            'td.author_string_id = as.id',
            array()
        )
        ->where('t.id = ?', $id);
        $species = $select->query()->fetchObject('ACI_Model_Table_Taxa');
        return $species;
    }

/*    public function getSynonymName($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('s' => 'synonym'),
            array(
                'genus' => 'sne_g.name_element',
                'species' => 'sne_s.name_element',
                'infraspecies' => 'sne_ss.name_element',
                'infraspecies_marker' => '',
                'author' => 'as.string',
                'id' => 's.id'
            )
        )
        ->joinRight(
            array('syne_g' => 'synonym_name_element'),
            's.id = syne_g.synonym_id AND syne_g.taxonomic_rank_id = 20',
            array()
        )
        ->joinRight(
            array('syne_s' => 'synonym_name_element'),
            's.id = syne_s.synonym_id AND syne_s.taxonomic_rank_id = 83',
            array()
        )
        ->joinLeft(
            array('syne_ss' => 'synonym_name_element'),
            's.id = syne_ss.synonym_id AND syne_ss.taxonomic_rank_id NOT IN (20,83)',
            array()
        )
        ->joinRight(
            array('sne_g' => 'scientific_name_element'),
            'syne_g.scientific_name_element_id = sne_g.id',
            array()
        )
        ->joinRight(
            array('sne_s' => 'scientific_name_element'),
            'syne_s.scientific_name_element_id = sne_s.id',
            array()
        )
        ->joinLeft(
            array('sne_ss' => 'scientific_name_element'),
            'syne_ss.scientific_name_element_id = sne_ss.id',
            array()
        )
        ->joinLeft(
            array('as' => 'author_string'),
            's.author_string_id = as.id',
            array()
        )
        ->where('s.id = ?', $id);
        $species = $select->query()->fetchObject('ACI_Model_Table_Taxa');
        return $species;
    }*/

    public function getSynonymName($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('sa' => '_search_all'),
            array(
                'taxaName' => 'sa.name',
                'taxaAuthor' => 'sa.name_suffix',
                'taxaStatus' => 'sa.name_status',
                'id' => 'sa.id'
            )
        )
        ->where('sa.id = ?', $id)
        ->group(array('id'));
        return $select->query()->fetchObject('ACI_Model_Table_Taxa');
    }

    public function getLsid($taxon_id)
    {
        return ($species->species_id == $species->taxon_id ? $species->species_lsid :
            $species->infraspecies_lsid);
    }

    public function getUrls($taxon_id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('utt' => 'uri_to_taxon'),
            array(
                'url' => 'uri.resource_identifier',
                'uri.uri_scheme_id'
            )
        )
        ->joinRight(
            array('uri' => 'uri'),
            'utt.uri_id = uri.id',
            array()
        )
        ->where('utt.taxon_id = ? AND uri.uri_scheme_id IN (5,6,7,10,18)', $taxon_id);
        $species = $select->query()->fetchAll();
        return $species;
    }
    
    public function getImages($taxon_id) {
        $select = new Zend_Db_Select($this->_db);
        $select->from('_image_resource');
        $select->where('taxon_id = ?', $taxon_id);
        return $select->query()->fetchAll();
    }
    
    public function lifezones($taxon_id) {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('l' => 'lifezone'),
            'l.lifezone'
        )
        ->joinLeft(
            array('lttd' => 'lifezone_to_taxon_detail'),
            'l.id = lttd.lifezone_id', array()
        )
        ->where('lttd.taxon_detail_id = ?', $taxon_id);
        return $select->query()->fetchAll();
    }
    
    public function getSourceDatabaseQualifiers($source_database_id) {
        $select = new Zend_Db_Select($this->_db);
        $select->from('_source_database_details',
            array(
                'dbCoverage' => 'coverage',
                'dbCompleteness' => 'completeness',
                'dbConfidence' => 'confidence'
                ));
        $select->where('id = ?', $source_database_id);
        return $select->query()->fetch();
    }
}