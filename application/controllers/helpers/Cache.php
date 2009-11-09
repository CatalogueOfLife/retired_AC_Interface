<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_Cache
 * Cache handler
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
class ACI_Helper_Cache extends Zend_Controller_Action_Helper_Abstract
{
    protected $_cacheDir;
    
    /**
     * Returns an instance of Zend_Cache_Core to use as cache for the
     * paginator
     *
     * @return Zend_Cache_Core
     */
    public function getPaginatorCache()
    {
        return Zend_Registry::get('cache');
    }
    
    /**
     * Gets the cache directory from the static cacheDir variable if set,
     * otherwise from the configuration file
     *
     * @return string $cacheDir
     */
    protected function _getCacheDir() {
        if(is_null($this->_cacheDir)) {
            $config = Zend_Registry::get('config');
            $this->_cacheDir = $config->cache->directory;
        }
        return $this->_cacheDir;
    }
}