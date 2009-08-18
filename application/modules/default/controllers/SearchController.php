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
                
        $searchKey = 'colo';
        
        $selectTaxa = new Zend_Db_Select($db);
        
        $selectTaxa->from(
            array(
                'ss' => 'simple_search'
            ),
            array(
                'id' => 'tx.record_id',
                'tx.taxon',
                'tx.name_code',
                'tx.name',
                'tx.is_accepted_name',
                'db_name' => 'db.database_name',
                'status' => 'st.sp2000_status'
            )
        )->join(
            array('tx' => 'taxa'),
            'ss.taxa_id = tx.record_id',
            array()
        )->joinLeft(
            array('st' => 'sp2000_statuses'),
            'tx.sp2000_status_id = st.record_id',
            array()
        )->joinLeft(
            array('db' => 'databases'),
            'tx.database_id = db.record_id',
            array()
        )->where('ss.words = ?', $searchKey)
         ;//->order(array('name', 'status'));
        
        $selectCommonNames = new Zend_Db_Select($db);
        
        $selectCommonNames
        ->from(
            array(
                'cn' => 'common_names'
            ),
            array(
                'id' => new Zend_Db_Expr(0),
                'taxon' => new Zend_Db_Expr('IF(cn.is_infraspecies, "Infraspecies", "Species")'),
                'cn.name_code',
                'name' => 'cn.common_name',
                'is_accepted_name' => new Zend_Db_Expr(1),
                'db_name' => 'db.database_name',
                'status' => new Zend_Db_Expr('"common name"'),
            )
        )->joinLeft(
            array('db' => 'databases'),
            'cn.database_id = db.record_id',
            array()
        )->where('cn.common_name LIKE CONCAT("%", ?, "%")', $searchKey)
         ;//->order(array('name', 'status'));
        
        $select = $db->select()->union(
            array(
                '(' . $selectTaxa . ')',
                '(' . $selectCommonNames . ')'
            )
        )->order(array('name', 'status'));
        
                
        $stmt = $db->query($select);
               
        $res = $stmt->fetchAll();
        foreach($res as $row) {
            //var_dump($row);
        }
        
        $this->view->numResults = count($res);
       
        $this->_logger->debug($this->getRequest());
        
        $this->renderScript('search/search.phtml');
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}

