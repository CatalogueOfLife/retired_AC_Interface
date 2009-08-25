<?php
/**
 * Annual Checklist Interface
 *
 * Class DetailsController
 * Defines the detail pages
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class DetailsController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
    
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_db = Zend_Registry::get('db');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    public function databaseAction()
    {
        $this->view->title = $this->view->translate('Database_details');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $dbTable = new ACI_Model_Table_Databases();
        $rowSet = $dbTable->find($this->_getParam('id'));
        $row = $rowSet->current();
        $database = false;
        
        if($row) {
            $database = $row->toArray();
            $database['image'] = str_replace(
                ' ', '_', $database['database_name']) . '.jpg';
        }
                
        $this->_logger->debug($database);
        $this->view->db = $database;
    }
    
    public function speciesAction()
    {
        //TODO: The title may be infraspecies
        $this->view->title = $this->view->translate('Species_details');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $taxaId = $this->_getParam('id', false);
        
        if($taxaId) {
            $detailsModel = new ACI_Model_Details($this->_db);
            $taxaDetails = $detailsModel->taxa($taxaId);
        }
        else {
            $taxaDetails = false;
        }
        
        $this->_logger->debug($taxaDetails);
        $this->view->taxa = $taxaDetails;
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}