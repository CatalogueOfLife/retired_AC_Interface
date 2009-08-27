<?php
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
class ACI_Model_Details
{
    protected $_db;
    protected $_logger;
    
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_db = $dbAdapter;
        $this->_logger = Zend_Registry::get('logger');
    }
    
    /**
     * Gets all the details of a species
     *
     * @param int $id
     * @param string $fromType common or taxa
     * @param int $fromId id of the common name or taxa of reference
     * @return ACI_Model_Taxa
     */
    public function species($id, $fromType = null, $fromId = null)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $fields =
            array(
                'id' => 'sn.record_id',
                'sn.family_id',
                'f.kingdom',
                'sn.genus',
                'sn.species',
                'sn.infraspecies_marker',
                'sn.infraspecies',
                'sn.name_code',
                'sn.accepted_name_code',
                'sn.author',
                'sn.comment',
                'sn.specialist_id',
                'sn.web_site',
                'sn.scrutiny_date',
                'status' => 'sn.sp2000_status_id',
                'db_id' => 'sn.database_id',
                'db_name' => 'db.database_name',
                'db_full_name' => 'db.database_full_name',
                'db_version' => 'db.version',
                'sn_taxa_id' => 't.record_id'
            );
            
        switch ($fromType) {
            case 'common':
                $extraFields = array(
                    'taxa_id' => 'cn.record_id',
                    'taxa_name' => 'cn.common_name',
                    'taxa_language' => 'cn.language',
                    'taxa_status' =>
                        new Zend_Db_Expr(ACI_Model_Taxa::STATUS_COMMON_NAME)
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
        }
        
        $select->from(
            array('sn' => 'scientific_names'),
            array_merge($fields, $extraFields)
        )
        ->joinLeft(
            array('db' => 'databases'),
            'sn.database_id = db.record_id',
            array()
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
        ->where('sn.record_id = ?', (int)$id);
        
        foreach($joinLeft as $jl) {
            $select->joinLeft($jl['name'], $jl['cond'], $jl['columns']);
        }
        
        $species = $select->query()->fetchObject('ACI_Model_Taxa');
        
        if(!$species instanceof ACI_Model_Taxa) {
            return false;
        }
        
        $species->hierarchy = $this->speciesHierarchy($species->sn_taxa_id);
        $species->synonyms = $this->synonyms($species->name_code);
        $species->common_names = $this->commonNames($species->name_code);
        $species->references = $this->references($species->id);
        
        return $species;
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
                    'tx.taxon',
                    'tx.LSID'
                )
            )->where('tx.record_id = ?');
            
        $hierarchy = array();
        
        do {
            $select->bind(array($id));
            $res = $select->query()->fetchAll();
            if(!count($res)) {
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
                'sn.genus',
                'sn.species',
                'sn.infraspecies_marker',
                'sn.infraspecies',
                'sn.author',
                'name' =>
                    "TRIM(CONCAT(IF(sn.genus IS NULL, '', sn.genus) " .
                    ", ' ', IF(sn.species IS NULL, '', sn.species), ' ', " .
                    "IF(sn.infraspecies IS NULL, '', sn.infraspecies)))"
            )
        )
        ->where(
            'sn.accepted_name_code = ? AND sn.sp2000_status_id = ?'
        )
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $select->bind(array($nameCode, ACI_Model_Taxa::STATUS_SYNONYM));
        
        return $select->query()->fetchAll();
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
                'reference_id' => 'r.record_id'
            )
        )
        ->joinLeft(
            array('r' => 'references'),
            'cn.reference_id = r.record_id',
            array()
        )
        ->where('cn.name_code = ?', $nameCode)
        ->order(array('cn.common_name', 'cn.language', 'cn.country'));
        
        return $select->query()->fetchAll();
    }
    
    public function references ($nameCode)
    {
        //TODO: optimize reference retrieval
        return array();
        $select = new Zend_Db_Select($this->_db);
        
        $select->distinct()
        ->from(
            array('snr' => 'scientific_name_references'),
            array(
                'snr.reference_type',
                'r.*'
            )
        )
        ->join(
            array('r' => 'references'),
            'snr.reference_id = r.record_id',
            array()
        )
        ->where('snr.name_code = ?', $nameCode)
        ->order(array('snr.reference_type', 'snr.reference_id'));
        
        return $select->query()->fetchAll();
    }
}