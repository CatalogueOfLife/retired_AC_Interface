<?php
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';
/**
 * Annual Checklist Interface
 *
 * Class Eti_Filter_Serialize
 * Serializes an array
 *
 * @category    Eti
 * @package     Eti_Filter
 *
 */
class Eti_Filter_Serialize implements Zend_Filter_Interface
{
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the array $value serialized 
     *
     * @param  array $value
     * @return string
     */
    public function filter($value)
    {
        if(!is_array($value)) {
            throw new Zend_Filter_Exception('Given value is not an array');
        }
        return serialize($value);
    }
}