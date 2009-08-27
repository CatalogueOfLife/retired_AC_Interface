<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Search
 * Search queries builder
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
    
    public function species($id, $taxaId)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->from(
            array('sn' => 'scientific_names'),
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
                'taxa_id' => 'tx.record_id',
                'taxa_status' => 'tx.sp2000_status_id',
                'taxa_name' => 'tx.name'
            )
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
            array('tx' => 'taxa'),
            'tx.record_id = ' . (int)$taxaId,
            array()
        )
        ->where('sn.record_id = ?', (int)$id);
        
        $species = $select->query()->fetchObject('ACI_Model_Taxa');
        
        if(!$species instanceof ACI_Model_Taxa) {
            return false;
        }
        
        $species->hierarchy = $this->speciesHierarchy($species->taxa_id);
        $species->synonyms = $this->synonyms($species->name_code);
        
        return $species;
    }
    
    public function commonName($name)
    {
        return false;
    }
    
    public function speciesHierarchy($id)
    {
        $select = new Zend_Db_Select($this->_db);
        $select->from(
                array('tx' => 'taxa'),
                array(
                    'tx.record_id',
                    'tx.parent_id',
                    'tx.taxon',
                    'LSID'
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
                'sn.author'
            )
        )
        ->where(
            'sn.name_code = ? AND sn.sp2000_status_id = ?'
        )
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $select->bind(array($nameCode, ACI_Model_Taxa::STATUS_SYNONYM));
        
        return $select->query()->fetchAll();
    }
}