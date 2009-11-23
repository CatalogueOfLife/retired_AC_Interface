<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_Export
 * Export (to CSV) helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
class ACI_Helper_Export extends Zend_Controller_Action_Helper_Abstract
{
    const MAX_ROWS = 65535;
    const SEPARATOR = ',';
    
    public function csv($controller, $action, Zend_Db_Select $select, $fileName)
    {
        $timeLimit = ini_get('max_execution_time');
        set_time_limit(300); // 5 minutes
        //$this->setHeaders($fileName);
        $actionController = $this->getActionController();
        $actionController->view->separator = self::SEPARATOR;
        $db = clone $actionController->getDbAdapter();
        $stmt = $db->query($select);
        $res = array();
        $actionController->renderScript(
            $controller . '/export/' . $action . '/headers.phtml'
        );
        while($row = $stmt->fetch()) {
            $this->getActionController();
            $actionController->view->data = $actionController
                ->getHelper('DataFormatter')->formatPlainRow($row);
            $actionController->renderScript(
                $controller . '/export/' . $action . '/data.phtml'
            );
            flush();
        }
        // restore maximum execution time to the default value
        set_time_limit($timeLimit);
    }
    
    public function setHeaders($fileName)
    {
        header('Expires: 0');
        header('Cache-control: private');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=' . $fileName);
    }
    
    /**
     * The CSV format cannot contain over 65535 rows
     *
     * @return int
     */
    public function getNumRowsLimit()
    {
        return self::MAX_ROWS;
    }
}