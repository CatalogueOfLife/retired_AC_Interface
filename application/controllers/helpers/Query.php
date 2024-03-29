<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_Query
 * Query building helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
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
        $params['fossil'] = isset($params['fossil']) && $this->_moduleEnabled('fossils') ?
            $params['fossil'] : 0;
        $model = new ACI_Model_Search(Zend_Registry::get('db'));
        switch ($controller) {
            case 'search':
                switch ($action) {
                    case 'all':
                        $select = $model->all($params['key'], $params['match'], null, null, $params['fossil']);
                        break;
                    case 'scientific':
                        $match = $params['match'];
                        $fossil = $params['fossil'];
                        unset($params['match'], $params['fossil']);
                        $select = $model->scientificNames(
                            $params, $match, null, null, $action, $fossil
                        );
                        break;
                    case 'common':
                        $select = $model->commonNames(
                            $params['key'], $params['match'], null, null, $params['fossil']
                        );
                        break;
                    case 'distribution':
                        $select = $model->distributions(
                            $params['key'], $params['match'], null, null, null, null, $params['fossil']
                        );
                        break;
                }
                break;
            case 'browse':
                switch($action) {
                    case 'classification':
                        $match = $params['match'];
                        $fossil = $params['fossil'];
                        unset($params['match'], $params['fossil']);
                        $select = $model->scientificNames(
                            $params, $match, null, null, $action, $fossil
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
                    trim($this->getRequest()->getParam('key')),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort'),
                    $this->getRequest()->getParam('direction'),
                    $this->getRequest()->getParam('fossil')
                );
                break;
            case 'search/scientific':
                $query = $select->scientificNames(
                    array(
                        'genus' => $this->getRequest()->getParam('genus'),
                        'subgenus' => $this->getRequest()->getParam('subgenus'),
                        'species' => $this->getRequest()->getParam('species'),
                        'infraspecies' =>
                            $this->getRequest()->getParam('infraspecies')
                    ),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort'),
                    $this->getRequest()->getParam('direction'),
                    $action,
                    $this->getRequest()->getParam('fossil')
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
                        'subgenus' => $this->getRequest()->getParam('subgenus'),
                        'species' => $this->getRequest()->getParam('species'),
                        'infraspecies' =>
                            $this->getRequest()->getParam('infraspecies')
                    ),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort'),
                    $this->getRequest()->getParam('direction'),
                    $action,
                    $this->getRequest()->getParam('fossil')
                );
                break;
            case 'search/distribution':
                $query = $select->distributions(
                    trim($this->getRequest()->getParam('key')),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort'),
                    $this->getRequest()->getParam('direction'),
                    $this->getRequest()->getParam('regions'),
                    $this->getRequest()->getParam('regionStandard'),
                    $this->getRequest()->getParam('fossil')
                );
	            break;
            case 'search/all':
            default:
                $query = $select->all(
                    trim($this->getRequest()->getParam('key')),
                    $this->getRequest()->getParam('match'),
                    $this->getRequest()->getParam('sort'),
                    $this->getRequest()->getParam('direction'),
                    $this->getRequest()->getParam('fossil')
                );
                break;
        }
        /*
        echo '<pre>';
        print_r((string)$query);
        echo '</pre>';
        die();
        */
        return $query;
    }

    /**
     * Returns a custom row count query based on a group statement in case
     * the instance of $query is Eti_Db_Select
     *
     * @param string $controller
     * @param string $action
     * @param Zend_Db_Select $query
     * @return Zend_Db_Select
     */
    public function getCountQuery($controller, $action, Zend_Db_Select $query)
    {
        if (!$query instanceof Eti_Db_Select) {
            return null;
        }
        $rowCount = clone $query;
        $rowCount->reset(Zend_Db_Select::COLUMNS);
        $rowCount->reset(Zend_Db_Select::ORDER);
        $rowCount->from('',
            array(
                'zend_paginator_row_count' => new Zend_Db_Expr('COUNT(1)'),
                'species' => new Zend_Db_Expr(
                    'SUM(IF(LENGTH(TRIM(dss.infraspecies)) > 0, 0, 1))'
                ),
                'infraspecies' => new Zend_Db_Expr(
                    'SUM(IF(LENGTH(TRIM(dss.infraspecies)) > 0, 1, 0))'
                ),
                'fossil_infraspecies' => new Zend_Db_Expr(
                    'SUM(IF((LENGTH(TRIM(dss.infraspecies)) > 0 AND dss.is_extinct = 1), 1, 0))'

                ),
                'fossil_total' => new Zend_Db_Expr(
                    'SUM(IF(dss.is_extinct = 1, 1, 0))'
                )
            )
        );
        return $rowCount;
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

    public function getAcceptedSpecies($nameCode)
    {
        if (is_null($nameCode)) {
            return array();
        }
        $search = new ACI_Model_Search(Zend_Registry::get('db'));
        $species = $search->getAcceptedSpeciesByNameCode($nameCode);
        return is_array($species) ? $species : array();
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
        // Ruud 06-06-14: trim leading and trailing spaces from string;
        // slightly more complicated than just trim() because q always seems to end with *...
        //$query = str_replace('\\', '', $query);
        $query = str_replace('\\', '', trim(substr($query, 0, -1))) . substr($query, -1);

        $search = new ACI_Model_Search(Zend_Registry::get('db'));
        $res = $this->parseFetchedResults(
            $search->fetchTaxaByRank($rank, $query, $params), $query
        );
        return new Eti_Dojo_Data('name', $res, $rank);
    }

    /**
     * It takes the results of the search hint query, decorates the labels and
     * adds custom messages depending on their output:
     * - Please enter a longer search string
     * - No matching results found
     * - More than n matching results
     *
     * @param array $res
     * @param string $query
     * @return array
     */
    protected function parseFetchedResults(array $res, $query)
    {
        $error = $res['error'];
        unset($res['error']);

        // No results
        if (empty($res)) {
            $errStr = $error ?
                'Please_enter_a_longer_search_string' :
                'No_matching_results_found';
            $res = array(
                array(
                    'label' => $this->getActionController()
                        ->getHelper('TextDecorator')
                        ->decorateComboLabel($errStr),
                    'name' => $query
                )
            );
        } else {
            $i = 0;
            foreach ($res as &$row) {
                $row['label'] = $this->getActionController()
                    ->getHelper('TextDecorator')
                    ->highlightMatch($row['name'], $query);
                if ($i == ACI_Model_Search::API_ROWSET_LIMIT) {
                    $errStr = 'More_matching_results';
                    $row = array(
                        'name' => empty($query) ? '' : $query . '*',
                        'label' => $this->getActionController()
                             ->getHelper('TextDecorator')
                             ->decorateComboLabel(
                                 $errStr, ACI_Model_Search::API_ROWSET_LIMIT
                             )
                    );
                }
                $i++;
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

    protected function _moduleEnabled($module) {
        return Bootstrap::instance()->getOption('module.'.$module);
    }

}