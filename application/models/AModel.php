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
                $res = $cache->load($this->_sanitizeCacheKey($cacheKey));
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
            $cache->save($res, $this->_sanitizeCacheKey($cacheKey));
        }
    }

    private function _sanitizeCacheKey($key) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', "_", $key));
    }


    public function idToNaturalKey ($id)
    {
        if (empty($id) || !$this->_moduleEnabled('natural_keys')) {
            return $id;
        }
        $select = new Zend_Db_Select($this->_db);
        $select->from('_natural_keys', array('hash'))->where('id=?', (int)$id);
        $res = $select->query()->fetchColumn(0);
        return empty($res) ? $id : $res;
    }

    public function naturalKeyToId ($hash)
    {
        if (empty($hash) || !$this->_moduleEnabled('natural_keys')) {
            return $hash;
        }
        $select = new Zend_Db_Select($this->_db);
        $select->from('_natural_keys', array('id'))->where('hash=?', $hash);
        $res = $select->query()->fetchColumn(0);
        return empty($res) ? $hash : $res;
    }

    protected function _prepend ($s, $a) {
        return $a . implode(" $a", explode(" ", $s));
    }

    protected function _append ($s, $a) {
        return implode("$a ", explode(" ", $s)) . "$a";
    }
}