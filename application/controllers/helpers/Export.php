<?php
class ACI_Helper_Export extends Zend_Controller_Action_Helper_Abstract
{
    const TAB = '\t';
    
    public function csv($fileName, Zend_Db_Select $select)
    {
        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=' . $fileName);
        echo '';
    }
}