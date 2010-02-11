<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class WebservicesController
 * Defines the webservices for the AC
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class WebservicesController extends AController
{  
    public function init()
    {
        parent::init();
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('query', 'xml');
    }
    
    public function queryAction ()
    {
        switch($this->_getParam('format')) {
            case 'php':
                $this->view->layout()->disableLayout();
                $filter = new Eti_Filter_Serialize();
                break;
            default:
                // default context and output filter (XML)
                $this->getRequest()->setParam('format', 'xml');
                $contextSwitch = $this->_helper->getHelper('contextSwitch');
                $contextSwitch->initContext();
                $filter = new Eti_Filter_ArrayToXml();
                $filter->setEncoding($this->view->encoding)->setRoot('results');
        }
        
        $wsModel = new ACI_Model_Webservice($this->_db);
        $wsModel->setFilter($filter);
        $res = $wsModel->query($this->getRequest());
        $this->view->response = $res;
    }
    
    public function __call ($name, $arguments)
    {
        $this->_forward('query');
    }
}