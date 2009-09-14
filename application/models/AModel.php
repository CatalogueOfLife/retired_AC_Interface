<?php
/**
 * Annual Checklist Interface
 *
 * Class AModel
 * Abstract model class
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
abstract class AModel
{
    protected $_db;
    protected $_logger;
    
    public function __construct(Zend_Db_Adapter_Abstract $dbAdapter)
    {
        $this->_db = $dbAdapter;
        $this->_logger = Zend_Registry::get('logger');
    }
}