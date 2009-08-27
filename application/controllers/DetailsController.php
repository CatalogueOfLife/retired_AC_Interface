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
        $dbDetails = $dbTable->get($this->_getParam('id'));
        $this->_logger->debug($dbDetails);
        $this->view->db = $dbDetails;
    }
    
    public function speciesAction()
    {
        $id = $this->_getParam('id', false);
        $taxaId = $this->_getParam('taxa', false);
        
        if($id) {
            $detailsModel = new ACI_Model_Details($this->_db);
            $speciesDetails = $detailsModel->species($id, $taxaId);
        }
        else {
            $speciesDetails = false;
        }
        
        $title =
            $speciesDetails &&
            $speciesDetails->rank == ACI_Model_Taxa::RANK_INFRASPECIES ?
                'Infraspecies_details' : 'Species_details';
        $this->view->title = $this->view->translate($title);
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $this->_logger->debug($speciesDetails);
        $this->view->species = $speciesDetails;
    }
    
    protected function __createSpeciesDetailsTable()
    {
        
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}