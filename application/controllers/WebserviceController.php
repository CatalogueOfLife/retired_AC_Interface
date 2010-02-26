<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class WebserviceController
 * Defines the webservice for the AC
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class WebserviceController extends AController
{  
    public function init()
    {  
        parent::init();
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('query', 'xml');
    }
    
    public function indexAction ()
    {
        // if no webservice-specific parameters are set, show the documentation
        if (ACI_Model_Webservice::paramsExist($this->_getAllParams())) {
            $this->_forward('query');
        } else {
            $this->view->layout()->disableLayout();
        }
    }
    
    public function queryAction ()
    {   
        switch ($this->_getParam('format')) {
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
                $filter->setEncoding($this->view->encoding)
                    ->setRoot('results')->setNode('result');
        }
        
        $wsModel = new ACI_Model_Webservice($this->_db);
        $wsModel->setFilter($filter);
        $res = $wsModel->query($this->getRequest());
        $this->view->response = $res;
    }
    
    public function __call ($name, $arguments)
    {
        $this->_forward('index');        
    }
}