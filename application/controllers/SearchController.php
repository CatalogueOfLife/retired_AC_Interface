<?php
/**
 * Annual Checklist Interface
 *
 * Class SearchController
 * Defines the search actions
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class SearchController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
        
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_logger->debug($this->_getAllParams());
        $this->_db = Zend_Registry::get('db');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    public function commonAction()
    {
        $this->view->title = $this->view->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_hasParam('key') ?
            $this->_renderResultsPage() :
            $this->_renderFormPage();
    }
    
    public function scientificAction()
    {
        $fetch = $this->_getParam('fetch', false);
        if($fetch) {
            $this->view->layout()->disableLayout();
            $this->_sendRankData($fetch);
            return;
        }
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        // TODO: implement search query
        //$form = new ACI_Form_Dojo_SearchScientific();               
        $this->view->form = '';
        $this->renderScript('search/form.phtml');
    }
    
    public function distributionAction()
    {
        $this->view->title = $this->view->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');
        // TODO: implement search query and render normally
        $this->view->form = '';
        $this->renderScript('search/form.phtml');
    }
    
    public function allAction()
    {
        $this->view->title = $this->view->translate('Search_all_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_hasParam('key') ?
            $this->_renderResultsPage() :
            $this->_renderFormPage();
    }
    
    protected function _renderFormPage()
    {
        $this->view->form = new ACI_Form_Search();
        $this->renderScript('search/form.phtml');
    }
    
    protected function _renderResultsPage()
    {
        $items = (int)$this->_getParam('items', 10);
        
        // Get the paginator
        $this->view->paginator = $this->_getPaginator(
            $this->_getSearchQuery($this->_getParam('action')),
            $this->_getParam('page', 1),
            $items
        );
        
        // Build items per page form
        $form = new ACI_Form_ItemsPerPage();

        $form->getElement('key')->setValue($this->_getParam('key'));
        $form->getElement('match')->setValue($this->_getParam('match'));
        $form->getElement('items')->setValue($items);
        
        $form->setAction(
            $this->view->baseUrl() . '/search/' . $this->_getParam('action')
        );
        
        // Set view values
        $this->view->key = $this->_getParam('key');
        $this->view->match = $this->_getParam('match');
        $this->view->items = $this->_getParam('items');
        $this->view->form = $form;
        
        // Render the results page
        $this->renderScript('search/results.phtml');
    }
     
    /**
     * Builds the paginator
     *
     * @param Zend_Db_Select $query
     * @param int $page
     * @param int $items
     *
     * @return Zend_Paginator
     */
    protected function _getPaginator(Zend_Db_Select $query, $page, $items)
    {
        $paginator = new Zend_Paginator(
            new Zend_Paginator_Adapter_DbSelect($query));
            
        $paginator->setItemCountPerPage((int)$items);
        $paginator->setCurrentPageNumber((int)$page);
        return $paginator;
    }
    
    /**
     * Returns the corresponding search query based on the requested action
     *
     * @return Zend_Db_Select
     */
    protected function _getSearchQuery($action)
    {
        $select = new ACI_Model_Search($this->_db);
        
        switch($action) {
            case 'common':
                $query = $select->commonNames(
                    $this->_getParam('key'), $this->_getParam('match')
                );
                break;
            case 'all':
            default:
                $query = $select->all(
                    $this->_getParam('key'), $this->_getParam('match')
                );
                break;
        }
        return $query;
    }
    
    protected function _sendRankData($rank)
    {
        $name = $this->_getParam('name', '*');
        $this->_logger->debug($name);
        // Restrict auto-complete function to 2 char length input strings
        if(strlen($name) < 3) {
            $res = array();
        }
        else {
            $res = array_merge(
                       array(), 
                       $this->_getRankEntries(
                           $rank, 
                           str_replace('*', '%', $name)
                       )
                   );
        }
        $this->_logger->debug($res);
        $this->view->data = new Zend_Dojo_Data('name', $res, $rank);
        $this->renderScript('search/data.phtml');
    }
    
    protected function _getRankEntries($rank, $name)
    {
        $select = new Zend_Db_Select($this->_db);
        
        $select->distinct()
               ->from(array('hard_coded_taxon_lists'), array('name'))
               ->where('rank = ? AND name LIKE "'. $name .'%"', $rank)
               ->order(array('name'));
        return $select->query()->fetchAll();
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}