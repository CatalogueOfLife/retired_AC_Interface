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
    
    public function getFoundRows()
    {        
        $select = new Eti_Db_Select($this->_db);
        $select->from(null, new Zend_Db_Expr('FOUND_ROWS()'));
        return $select->query()->fetchColumn(0);
    }
    
    protected function _moduleEnabled($module) {
        return Bootstrap::instance()->getOption('module.'.$module);
    }
    
    protected function _fetchFromCache($cacheKey) {
        $cache = Zend_Registry::get('cache');
        if ($cache) {
            // Try to load cached results
            try {
                $res = $cache->load($cacheKey);
            } catch (Zend_Cache_Exception $zce) {
                // An exception may be thrown if the cache key is not valid
                // In that case, the cache is not used
                return false;
            }
            return $res;
        }
        return false;
    }
    
    protected function _storeInCache($res, $cacheKey) {
        $cache = Zend_Registry::get('cache');
        if ($cache) {
            $cache->save($res, $cacheKey);
        }
     }
}