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
    const SEPARATOR = ",";
    
    public function csv($controller, $action, Zend_Db_Select $select, $fileName)
    {
        $this->setHeaders($fileName);
        $actionController = $this->getActionController();
        $actionController->view->data =
            $this->_loadData($controller, $action, $select);
        $actionController->view->separator = self::SEPARATOR;
        $actionController->renderScript(
            $controller . '/export/' . $action . '.phtml'
        );
    }
    
    protected function _loadData($controller, $action, Zend_Db_Select $select)
    {
        $db = $this->getActionController()->getDbAdapter();
        $res = $this->getActionController()
            ->getHelper('DataFormatter')->formatPlain(
                $select->query()->fetchAll()
            );
        return $res;
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