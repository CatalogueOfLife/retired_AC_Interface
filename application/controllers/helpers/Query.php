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
        if(!$latestQuery) {
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
        switch($controller) {
            case 'search':
                $model = new ACI_Model_Search(
                    $this->getActionController()->getDbAdapter()
                );
                switch($action) {
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
                        break;
                }
                break;
        }
        return $select;
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
        if($latestQuery) {
            list($controller, $action) = explode('/', $latestQuery);
        }
        return $controller;
    }
    
    public function getLatestQueryAction()
    {
        $latestQuery = $this->getLatestQuery();
        if($latestQuery) {
            list($controller, $action) = explode('/', $latestQuery);
        }
        return $action;
    }
    
    public function getLatestQuery()
    {
        return $this->getActionController()->getHelper('SessionHandler')
            ->get('latest_query', false);
    }
}