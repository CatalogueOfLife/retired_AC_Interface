<?php
class ACI_Helper_Query extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Gets the latest query executed (if any) and returns the corresponding
     * Zend_Db_Select object
     *
     * @return Zend_Db_Select
     */
    public function getLatestSelect()
    {
        $sh = $this->getActionController()->getHelper('SessionHandler');
        $latestQuery = $sh->get('latest_query', false);
        if (!$latestQuery) {
            return null;
        }
        $controller = $this->getLatestQueryController();
        $action = $this->getLatestQueryAction();
        $params = $sh->getContextParams($controller . '_' . $action);
        $select = $this->getSelect($controller, $action, $params);
        return $select;
    }
    
    /**
     * Gets the select associated to the given controller, action using
     * the parameters $params
     *
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return Zend_Db_Select
     */
    public function getSelect($controller, $action, $params)
    {
        $select = null;
        $model = new ACI_Model_Search(Zend_Registry::get('db'));
        
        switch ($controller) {
            case 'search':
                switch ($action) {
                    case 'all':
                        $select = $model->all($params['key'], $params['match']);
                        break;
                    case 'scientific':
                        $match = $params['match'];
                        unset($params['match']);
                        $select = $model->scientificNames(
                            $params, $match
                        );
                        break;
                    case 'common':
                        $select = $model->commonNames(
                            $params['key'], $params['match']
                        );
                        break;
                    case 'distribution':
                        $select = $model->distributions(
                            $params['key'], $params['match']
                        );
                        break;
                }
                break;
            case 'browse':
                switch($action) {
                    case 'classification':
                        $match = $params['match'];
                        unset($params['match']);
                        $select = $model->scientificNames(
                            $params, $match
                        );
                        break;
                }
                break;
        }
        return $select;
    }
    
    /**
     * Returns the corresponding search query based on the requested action
     *
     * @return Zend_Db_Select
     */
    public function getSearchQuery($controller, $action)
    {
        $select = new ACI_Model_Search(Zend_Registry::get('db'));
        $search = $controller . '/' . $action;
        
        switch ($search) {
            case 'search/common':
                $query = $select->commonNames(
                    $this->getRequest()->getParam('key'),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort')
                );
                break;
            case 'search/scientific':
                $query = $select->scientificNames(
                    array(
                        'genus' => $this->getRequest()->getParam('genus'),
                        'species' => $this->getRequest()->getParam('species'),
                        'infraspecies' =>
                            $this->getRequest()->getParam('infraspecies')
                    ),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort')
                );
                break;
            case 'browse/classification':
                $query = $select->scientificNames(
                    array(
                        'kingdom' => $this->getRequest()->getParam('kingdom'),
                        'phylum' => $this->getRequest()->getParam('phylum'),
                        'class' => $this->getRequest()->getParam('class'),
                        'order' => $this->getRequest()->getParam('order'),
                        'superfamily' =>
                            $this->getRequest()->getParam('superfamily'),
                        'family' => $this->getRequest()->getParam('family'),
                        'genus' => $this->getRequest()->getParam('genus'),
                        'species' => $this->getRequest()->getParam('species'),
                        'infraspecies' =>
                            $this->getRequest()->getParam('infraspecies')
                    ),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort')
                );
                break;
            case 'search/distribution':
                $query = $select->distributions(
                    $this->getRequest()->getParam,
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort')
                );
                break;
            case 'search/all':
            default:
                $query = $select->all(
                    $this->getRequest()->getParam('key'),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort')
                );
                break;
        }
        return $query;
    }
    
    public function tagLatestQuery()
    {
        $this->getActionController()->getHelper('SessionHandler')->set(
            'latest_query',
            $this->getRequest()->getControllerName() . '/' .
            $this->getRequest()->getActionName(),
            false
        );
    }
    
    public function getLatestQueryController()
    {
        $latestQuery = $this->getLatestQuery();
        if ($latestQuery) {
            list($controller, $action) = explode('/', $latestQuery);
        }
        return $controller;
    }
    
    public function getLatestQueryAction()
    {
        $latestQuery = $this->getLatestQuery();
        if ($latestQuery) {
            list($controller, $action) = explode('/', $latestQuery);
        }
        return $action;
    }
    
    public function getLatestQuery()
    {
        return $this->getActionController()->getHelper('SessionHandler')
                    ->get('latest_query', false);
    }
    
    /**
     * Returns an array with all taxa names by rank on a dojo-suitable format
     * Used to populate the scientific search combo boxes
     *
     * @return void
     */
    public function fetchTaxaByRank($rank, $query, $params)
    {
        $params = $this->_filterRankParams(
            $this->decodeKey($params), $rank
        );
        $query = str_replace('\\', '', $query);
        $cleanQuery = substr($query, 1, -1);
        $search = new ACI_Model_Search(Zend_Registry::get('db'));
        $res = $this->parseFetchedResults(
            $search->fetchTaxaByRank($rank, $query, $params), $cleanQuery
        );
        return new Zend_Dojo_Data('name', $res, $rank);
    }
    
    protected function parseFetchedResults(array $res, $query)
    {
        $error = $res['error'];
        unset($res['error']);
        if (!$res || $error) {
            switch ($error) {
                case 1:
                    $errStr = 'Please_enter_a_longer_search_string';
                    break;
                case 2:
                    $errStr = 'Too_many_results_to_display';
                    break;
                default:
                    $errStr = 'No_matching_results_found';
                    break;
            }
            $res = array(
                array(
                    'label' => $this->getActionController()
                        ->getHelper('TextDecorator')
                        ->decorateComboLabel($errStr),
                    'name' => $query)
            );
        } else {
            foreach ($res as &$row) {
                $row['label'] = $this->getActionController()
                    ->getHelper('TextDecorator')->highlightMatch(
                        $row['name'], $query
                    );
            }
        }
        return $res;
    }
    
    /**
     * It takes a key -> value array with the submitted rank -> string pairs
     * and removes those that are not relevant for the filtering of the main
     * rank ($rank)
     *
     * @param array $params
     * @param string $rank
     * @return array $params
     */
    protected function _filterRankParams(array $params, $rank)
    {
        if (isset($params[$rank])) {
            unset($params[$rank]);
        }
        if (empty($params)) {
            return array();
        }
        $search = new ACI_Model_Search(Zend_Registry::get('db'));
        foreach ($params as $r => $str) {
            if (trim($str) == '') {
                unset($params[$r]);
                continue;
            }
            if (!$search->taxaExists($r, $str)) {
                unset($params[$r]);
            }
        }
        return $params;
    }
    
    /**
     * Converts a JSON string to array or object, although it always returns
     * an array
     *
     * @param string $key
     * @return array $res
     */
    public function decodeKey($key)
    {
        $res = Zend_Json::decode(stripslashes($key));
        if (!is_array($res)) {
            return array();
        }
        return $res;
    }
}