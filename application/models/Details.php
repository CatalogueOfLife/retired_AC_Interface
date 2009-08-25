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
    
    public function taxa($id)
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
                'status_id' => 'sn.sp2000_status_id',
                'db_id' => 'sn.database_id',
                'db_name' => 'db.database_name',
                'db_full_name' => 'db.database_full_name',
                'db_version' => 'db.version',
                'taxa_id' => 'tx.record_id'
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
            'tx.name_code = sn.name_code',
            array()
        )
        ->where('sn.record_id = ?', (int)$id);
        
        $taxa = $select->query()->fetchObject('ACI_Model_Taxa');
        
        if(!$taxa instanceof ACI_Model_Taxa) {
            return false;
        }
        
        $taxa->hierarchy = $this->taxaHierarchy($taxa->taxa_id);
        if(!$taxa->isAcceptedName()) {
            $taxa->accepted_name = $this->accepted_name($taxa->name_code);
            $accepted_name_code = $taxa->accepted_name->name_code;
        }
        else {
            $accepted_name_code = $taxa->name_code;
        }
        $taxa->synonyms = $this->synonyms($accepted_name_code);
        
        return $taxa;
    }
    
    public function commonName($name)
    {
        return false;
    }
    
    public function taxaHierarchy($id)
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
    
    public function acceptedName($nameCode)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->distinct()
        ->from(
            array('sn' => 'scientific_names'),
            array(
                'sn.record_id',
                'sn.name_code',
                'sn.genus',
                'sn.species',
                'sn.infraspecies_marker',
                'sn.infraspecies',
                'sn.author'
            )
        )
        ->where('sn.accepted_name_code = ? AND sn.sp2000_status_id IN (?, ?)')
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $select->bind(
            array(
                $nameCode,
                ACI_Model_Taxa::STATUS_ACCEPTED_NAME,
                ACI_Model_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME
            )
        );
        
        $res = $select->query()->fetchAll();
        
        if(!count($res)) {
            return false;
        }
        return $res[0];
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
                'sn.author',
                'sn.sp2000_status_id'
            )
        )
        ->where(
            'sn.accepted_name_code = ? AND sn.sp2000_status_id = ?'
        )
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
        $select->bind(array($nameCode, ACI_Model_Taxa::STATUS_SYNONYM));
        $stmt = $select->query();
        
        while ($row = $stmt->fetchObject()) {
            $synonyms[] = $row;
        }
        //$synonyms = $select->query()->fetchAll();
        
        return $synonyms;
    }
}