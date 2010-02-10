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
    protected $_dom;
    
    public function init() {
        $this->_setContext();
        $this->_dom = new DOMDocument('1.0', $this->view->encoding);
    }
    
    public function queryAction ()
    {
        $results = $this->_dom->createElement('results');
        $results->setAttribute('id', null);
        $results->setAttribute('name', null);
        $results->setAttribute('total_number_of_results', 0);
        $results->setAttribute('start', 0);
        $results->setAttribute('number_of_results_returned', 0);
        $results->setAttribute('error_message', 'No name or ID given');
        $results->setAttribute('version', '1.0');
        $this->_dom->appendChild($results);
        $this->_sendResponse();
    }
    
    protected function _sendResponse()
    {
        $this->view->response = $this->_dom->saveXML();
    }
    
    protected function _setContext()
    {
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('query', 'xml')
            ->addActionContext('query', 'json');
        switch($this->getRequest()->getParam('format')) {
            case 'xml':
            case 'json':
                break;
            case 'php':
                $this->getRequest()->setParam('format', 'json');
                break;
            default:
                // default context
                $this->getRequest()->setParam('format', 'xml');
        }
        $contextSwitch->initContext();
    }
    
    public function __call ($name, $arguments)
    {
        $this->_forward('query');
    }
}