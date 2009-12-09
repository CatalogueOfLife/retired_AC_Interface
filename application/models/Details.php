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
                'id' => 'sn.record_id',
                'sn.family_id',
                'f.kingdom',
                'sn.genus',
                'sn.species',
                'infra_marker' => 'sn.infraspecies_marker',
                'infra' => 'sn.infraspecies',
                'sn.name_code',
                'sn.accepted_name_code',
                'sn.author',
                'sn.comment',
                'sn.web_site',
                'sn.scrutiny_date',
                'status' => 'sn.sp2000_status_id',
                'sn.scrutiny_date',
                'sp.specialist_name',
                'db_id' => 'sn.database_id',
                'sn_taxa_id' => 't.record_id',
                'lsid' => 't.lsid',
                'rank' => new Zend_Db_Expr(
                            'IF(t.taxon = "Infraspecies", ' .
                                ACI_Model_Table_Taxa::RANK_INFRASPECIES . ', ' .
                                ACI_Model_Table_Taxa::RANK_SPECIES . ')')
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
            array('sn' => 'scientific_names'),
            array_merge($fields, $extraFields)
        )
        ->joinLeft(
            array('f' => 'families'),
            'sn.family_id = f.record_id',
            array()
        )
        ->joinLeft(
            array('t' => 'taxa'),
            'sn.name_code = t.name_code',
            array()
        )
        ->joinLeft(
            array('sp' => 'specialists'),
            'sn.specialist_id = sp.record_id',
            array()
        )
        ->where('sn.record_id = ?', (int)$id);
        
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
            array('sn' => 'scientific_names'),
            array('status' => 'sp2000_status_id')
        )->where('sn.record_id = ?', (int)$id);
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
        $select = new Zend_Db_Select($this->_db);
        $select->from(
            array('tx' => 'taxa'),
            array(
                'tx.record_id',
                'tx.parent_id',
                'tx.name',
                'tx.taxon',
                'tx.LSID'
            )
        )->where('tx.record_id = ?');
            
        $hierarchy = array();
        
        do {
            $select->bind(array($id));
            $res = $select->query()->fetchAll();
            if (!count($res)) {
                break;
            }
            $hierarchy[] = $res[0];
            $id = $res[0]['parent_id'];
        } while ($id > 0);

        return array_reverse($hierarchy);
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
            'sn.infraspecies IS NOT NULL AND sn.is_accepted_name = ?'
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