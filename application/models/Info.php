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
class ACI_Model_Info
{
    protected $_db;
    protected $_logger;
    const API_ROWSET_LIMIT = 1500;
    
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_db = $dbAdapter;
        $this->_logger = Zend_Registry::get('logger');
    }
    
    public function _getRightColumnName($columName)
    {
        $find = array(
           'source',
           'groupName',
           'names'
        );
        $replace = array(
           'database_name_displayed',
           'taxa',
           'accepted_species_names DESC'
        );
        return str_replace($find,$replace,$columName);
    }
}