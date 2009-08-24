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
                'status' => 'st.sp2000_status',
                'db_id' => 'sn.database_id',
                'db_name' => 'db.database_name',
                'db_full_name' => 'db.database_full_name',
                'db_version' => 'db.version',
                'taxa_id' => 'tx.record_id'
            )
        )
        ->joinLeft(
            array('st' => 'sp2000_statuses'),
            'sn.sp2000_status_id = st.record_id',
            array()
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
        $taxa->acceptedName = $taxa->isAcceptedName() ?
            array(
                'id' => $taxa->id,
                'name_code' => $taxa->name_code,
                'genus' => $taxa->genus,
                'species' => $taxa->species,
                'infraspecies_marker' => $taxa->infraspecies_marker,
                'infraspecies' => $taxa->infraspecies,
                'author' => $taxa->author
            ) :
            $this->acceptedName($taxa->name_code);
            
        $this->_logger->debug($taxa);
            
        $taxa->synonyms = $this->synonyms($taxa->acceptedName['name_code']);
        
        return $taxa;
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
        ->joinLeft(
            array('st' => 'sp2000_statuses'),
            'sn.sp2000_status_id = st.record_id',
            array()
        )
        ->where(
            'sn.accepted_name_code = ? AND st.sp2000_status = "accepted name"',
            $nameCode
        )
        ->order(array('genus', 'species', 'infraspecies', 'author'));
        
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
        
        $select->bind(array($nameCode, ACI_Model_Taxa::SYNONYM));
        $synonyms = $select->query()->fetchAll();
        
        return $synonyms;
    }
}