<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Info
 * Search queries builder
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Info
{
    protected $_logger;
    
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_logger = Zend_Registry::get('logger');
    }
    
    public static function getRightColumnName($columName)
    {
        $columMap = array(
            'source' => 'database_name_displayed',
            'group' => 'taxa',
            'names' => 'accepted_species_names DESC'
        );
        return isset($columMap[$columName]) ?
            $columMap[$columName] : null;
    }
}