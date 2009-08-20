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
        $this->view->title = $this->view->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->view->form = new AC_Form_Search();
        $this->renderScript('search/form.phtml');
    }
    
    public function scientificAction()
    {
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->renderScript('search/form.phtml');
    }
    
    public function distributionAction()
    {
        $this->view->title = $this->view->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->view->form = new AC_Form_Search();
        $this->renderScript('search/form.phtml');
    }

    public function allAction()
    {
        $this->view->title = $this->view->translate('Search_all_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        if($this->_hasParam('key'))
        {
            $items = 10;
        	if($this->_hasParam('items'))
        	   $items = $this->_getParam('items');
            $select = new AC_Model_Search($this->_db);
            $query = $select->all(
                $this->_getParam('key'), $this->_getParam('match')
            );
            $page = $this->_hasParam('page') ? $this->_getParam('page') : 1;
            $paginator = new Zend_Paginator(
                new Zend_Paginator_Adapter_DbSelect($query));
            $paginator->setItemCountPerPage($items);
            $paginator->setCurrentPageNumber($page);
            $paginator->setView($this->view);
            $paginator->t = $this->view->t;
            $this->view->paginator = $paginator;
            $form = new AC_Form_SearchResult();

            $form->getElement('key')->setValue($this->_getParam('key'));
            $form->getElement('match')->setValue($this->_getParam('match'));

            $this->view->form = $form;
            
            $this->renderScript('search/results.phtml');
        }
        else
        {
	        $this->view->form = new AC_Form_Search();
            $this->renderScript('search/form.phtml');
        }
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}