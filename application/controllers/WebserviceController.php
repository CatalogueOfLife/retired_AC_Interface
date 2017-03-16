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
    private $_filter;

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
            $config = Zend_Registry::get('config');
            $this->view->location = $this->getFrontController()->getBaseUrl() .
                '/webservice';
            $this->view->version = $config->eti->application->version.' rev '.
                $config->eti->application->revision;
            $this->view->edition = $this->_setEdition();
            $this->view->layout()->disableLayout();
        }
    }

    public function queryAction ()
    {
        switch ($this->_getParam('format')) {
            case 'php':
                $this->view->layout()->disableLayout();
                $this->_filter = new Eti_Filter_Serialize();
                break;
            case 'json':
                $this->view->layout()->disableLayout();
                $this->getResponse()->setHeader('Content-Type', 'application/json');
                $this->_filter = new Eti_Filter_JsonEncode();
                break;
            default:
                // default context and output filter (XML)
                $this->getRequest()->setParam('format', 'xml');
                $contextSwitch = $this->_helper->getHelper('contextSwitch');
                $contextSwitch->initContext();
                $this->_filter = new Eti_Filter_ArrayToXml();
                // node name mapping based on parent node name
                $this->_filter->setNodeNameMapping(
                    array(
                        'root' => 'results',
                        'results' => 'result',
                        'references' => 'reference',
                        'classification' => 'taxon',
                        'child_taxa' => 'taxon',
                        'synonyms' => 'synonym',
                        'common_names' => 'common_name'
                    )
                );
        }
        $wsModel = new ACI_Model_Webservice($this->_db);
        // Ruud 04-10-16: moved filter to controller so headers can be reset in case of serious error
        // $wsModel->setFilter($filter);
        $res = $wsModel->query($this->getRequest());
        // Ruud 04-10-16: fatal error, set header 500
        if (empty($res['id']) && empty($res['name']) && strpos($res['error_message'], 'FATAL') !== false) {
            $this->getResponse()->setHttpResponseCode(500);
        }

        $this->view->response = $this->_filter->filter($res);
    }

    public function __call ($name, $arguments)
    {
        $this->_forward('index');
    }
}