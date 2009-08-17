<?php
class SearchController extends Zend_Controller_Action
{

    public function init()
    {}
    
    public function commonAction()
    {
        $this->renderScript('search/search.phtml');
    }
    
    public function scientificAction()
    {
        $this->renderScript('search/search.phtml');
    }
    
    public function distributionAction()
    {
        $this->renderScript('search/search.phtml');
    }

    public function allAction()
    {
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
        
        //$res->execute();
        
        //$stmt->fetchAll();
        
        //$stmt = new Zend_Db_Statement_Mysqli($db, self::SEARCH_SQL);
        //$stmt->bindParam('searchKey', $searchKey, Zend_Db::PARAM_STR);
        //$stmt->bindValue('searchKey', 'colo', Zend_Db::PARAM_STR);
        
        //var_dump($stmt);
        //$stmt->execute(array('colo'));
               
        $res = $stmt->fetchAll();
        foreach($res as $row) {
            var_dump($row);
        }
        
        $this->view->numRows = count($res);
        
        $profiler = $db->getProfiler();
        
        $totalTime = $profiler->getTotalElapsedSecs();
        $queryCount = $profiler->getTotalNumQueries();
        $longestTime = 0;
        $longestQuery = null;
        
        foreach($profiler->getQueryProfiles() as $query) {
            if($query->getElapsedSecs() > $longestTime) {
                $longestTime = $query->getElapsedSecs();
                $longestQuery = $query->getQuery();
            }
        }
        
        echo '<br/>Executed ' . $queryCount . ' queries in ' . $totalTime . ' seconds<br/>';
        echo 'Average query length: ' . $totalTime / $queryCount . ' seconds<br/>';
        echo 'Queries per second: ' . $queryCount / $totalTime . '<br/>';
        echo 'Longest query length: ' . $longestTime . '<br/>';
        echo 'Longest query: <br/>' . $longestQuery . '<br/>';
        
        $this->renderScript('search/search.phtml');
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}

