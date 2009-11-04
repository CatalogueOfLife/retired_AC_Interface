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
    protected static $cacheDir;
    
    /**
     * Returns an instance of Zend_Cache_Core to use as cache for the 
     * paginator
     * 
     * @return Zend_Cache_Core
     */
    public function getPaginatorCache()
    {
        $fO = array('lifetime' => null, 'automatic_serialization' => true);
        $bO = array('cache_dir' => $this->_getCacheDir());
        try {
            $cache = Zend_Cache::factory('Core', 'File', $fO, $bO);
            return $cache;
        } catch(Exception $e) {}
        // The application must not crash if the cache directory is not valid
        // (does not exist, is not readable, ...), but throw an error instead:
        // Catchable fatal error: Argument 1 passed to 
        // Zend_Paginator::setCache() must be an instance of Zend_Cache_Core, 
        // null given
        return null;
    }
    
    /**
     * Gets the cache directory from the static cacheDir variable if set,
     * otherwise from the configuration file
     *
     * @return string $cacheDir
     */
    protected function _getCacheDir() {
        if(is_null(self::$cacheDir)) {
            $config = Zend_Registry::get('config');
            self::$cacheDir = $config->cache->directory;
        }
        return self::$cacheDir;
    }
}