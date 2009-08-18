<?php
class SearchController extends Zend_Controller_Action
{
    protected $_logger;
        
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    public function commonAction()
    {
        $this->view->title = $this->view->t
            ->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');

        $form = new AC_Form_Search();
        $form->setAction('all');
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
        $form->setAction('all');
        $this->view->form = $form;
                
        $this->renderScript('search/search.phtml');
    }

    public function allAction()
    {
        $this->view->title = $this->view->t
            ->translate('Search_all_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        $search = new AC_Model_Search($db);
        $select = $search->all('cola', false);
        $stmt = $db->query($select);
               
        $res = $stmt->fetchAll();
        foreach($res as $row) {
            //var_dump($row);
        }
        
        $this->view->numResults = count($res);
       
        $this->_logger->debug($this->getRequest());
        
        $form = new AC_Form_Search();
        $form->setAction('all');
        $this->view->form = $form;
        
        $this->renderScript('search/search.phtml');
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}