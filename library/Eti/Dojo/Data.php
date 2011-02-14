<?php
/**
 * Annual Checklist Interface
 *
 * Class Eti_Dojo_Data
 * Extends Zend_Dojo_Data to skip null items and duplicates and encode to UTF-8
 * so that Dojo can process them
 *
 * @category    Eti
 * @package     Eti_Dojo
 *
 */
class Eti_Dojo_Data extends Zend_Dojo_Data implements ArrayAccess, Iterator,
    Countable
{
    /**
     * Add an individual item, optionally by identifier
     *
     * @param  array|object $item
     * @param  string|null $id
     * @return Eti_Dojo_Data
     */
    public function addItem($item, $id = null)
    {
        $item = $this->_utf8Encode($this->_normalizeItem($item, $id));
        if (!$this->hasItem($item['id']) &&
            ($item['id'] || isset($item['data']['label']))) {
            $this->_items[$item['id']] = $item['data'];
        }
        return $this;
    }
    
    /**
     * Encodes to UTF-8 the id and the elements inside the data array
     *
     * @param array $item
     * @return array
     */
    protected function _utf8Encode(array $item) {
        $encItem['id'] = utf8_encode($item['id']);
        foreach ($item['data'] as $k => $v) {
            $encItem['data'][$k] = utf8_encode($v);
        }
        return $encItem;
    }
}