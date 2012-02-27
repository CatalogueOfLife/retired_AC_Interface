<?php
/**
 * Annual Checklist Interface
 *
 * Class Eti_Paginator
 * Extends the Zend_Paginator to add a method to retrieve the custom counting
 * fields
 *
 * @category    Eti
 * @package     Eti_Paginator
 *
 */
class Eti_Paginator extends Zend_Paginator
{
    public function getCountColumn($columnName)
    {
        return $this->getAdapter()->getCountColumn($columnName);
    }
}