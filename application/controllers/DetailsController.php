<?php
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
        $dbTable = new AC_Model_Table_Databases();
        $rowSet = $dbTable->find($this->_getParam('id'));
        
        if($row = $rowSet->current()) {
            $database = $row->toArray();
            $database['image'] = str_replace(
                ' ', '_', $database['database_name']) . '.jpg';
        }
        else {
            $database = false;
        }
        
        $this->_logger->debug($database);
        $this->view->db = $database;
    }
    
    public function speciesAction()
    {
        $this->view->title = $this->view->translate('Species_details');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}