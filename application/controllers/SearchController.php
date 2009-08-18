<?php
class SearchController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
        
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_db = Zend_Registry::get('db');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
        $this->_logger->debug($this->_getAllParams());
    }
    
    public function commonAction()
    {
        $this->view->title = $this->view->t
            ->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');

        $form = new AC_Form_Search();
        $this->view->form = $form;
                
        $this->renderScript('search/search.phtml');
    }
    
    public function scientificAction()
    {
        $this->view->title = $this->view->t
            ->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->renderScript('search/search.phtml');
    }
    
    public function distributionAction()
    {
        $this->view->title = $this->view->t
            ->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');

        $form = new AC_Form_Search();
        $this->view->form = $form;
                
        $this->renderScript('search/search.phtml');
    }

    public function allAction()
    {
        $this->view->title = $this->view->t
            ->translate('Search_all_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        if($this->_hasParam('key'))
        {
            $select = new AC_Model_Select($this->_db);
            $query = $select->all($this->_getParam('key'), $this->_getParam('exact'));
            $this->_db->query($query);
            //$res = $stmt->fetchAll();
            //TODO: Zend_Paginator
        }
        
        $form = new AC_Form_Search();
        $this->view->form = $form;
        $this->renderScript('search/search.phtml');
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}