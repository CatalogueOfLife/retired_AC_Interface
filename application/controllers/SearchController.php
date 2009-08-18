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
        
        $this->view->form = $this->getForm();
//        $this->render('form');
        
        $this->renderScript('search/search.phtml');
    }
    
    public function getForm($action='all')
    {
        $form = new Zend_Form();
        $form->setAction('/search/'.$action);
        $form->setMethod('post');

        $searchfield = new Zend_Form_Element_Text('search_string');
        $searchfield->setRequired(true);

        $match_whole_words = new Zend_Form_Element_Checkbox('whole_words');
        $match_whole_words->setValue('1');

        $mode = new Zend_Form_Element_Hidden('hidden_field_name');
        $mode->setValue('1');

        // Add elements to form:
        $form->addElement($searchfield)
             ->addElement($mode)
             ->addElement($match_whole_words);
        
        return $form;
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}