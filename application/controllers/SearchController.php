<?php
require_once 'AController.php';
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
class SearchController extends AController
{
    public function commonAction()
    {
        $this->view->title = $this->view->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $form = $this->_getSearchForm();
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->_tagLatestSearch();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            $this->_setParamsFromSession($form->getInputElements());
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function scientificAction()
    {
        // Search hint query request
        $fetch = $this->_getParam('fetch', false);
        if ($fetch) {
            $this->view->layout()->disableLayout();
            exit(
                $this->_fetchTaxaByRank(
                    $fetch, $this->_getParam('q'), $this->_getParam('p')
                )
            );
        }
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $params = $this->_decodeKey($this->_getParam('key'));
            foreach ($params as $k => $v) {
                if (!($this->_getParam($k, false))) {
                    $this->_setParam($k, $v);
            }
        }
        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());
        $this->_logger->debug($form);
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $formIsValid) {
            $this->_setSessionFromParams($form->getInputElements());
            $str = '';
            foreach ($form->getInputElements() as $el) {
                if ($el != 'match') {
                    $str .= ' ' . $this->_getParam($el);
                }
            }
            $this->view->searchString = trim($str);
            $this->_tagLatestSearch();
            $this->_renderResultsPage($form->getInputElements());
        // Form page
        } else {
            if(!$formIsValid && $this->_hasParam('key')) {
                $this->view->formError = true;
                $this->_setSessionFromParams($form->getInputElements());
            }
            $this->_setParamsFromSession($form->getInputElements());
            $this->view->dojo()
                 ->registerModulePath(
                    'ACI', $this->view->baseUrl() . '/scripts/library/ACI'
                 )->requireModule('ACI.dojo.TxReadStore');
            // ComboBox (v1.3.2) custom extension
            $this->view->headScript()->appendFile(
                $this->view->baseUrl() . '/scripts/ComboBox.ext.js'
            );
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function distributionAction()
    {
        $this->view->title = $this->view->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');
        $form = $this->_getSearchForm();
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->_tagLatestSearch();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            $this->_setParamsFromSession($form->getInputElements());
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function allAction()
    {
        $this->view->title = $this->view->translate('Search_all_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $formHeader =
            sprintf(
                $this->view->translate('Search_fixed_edition'),
                '<span class="red">' .
                $this->view->translate('Annual_Checklist') . '</span>'
            );
        $form = $this->_getSearchForm();
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->_tagLatestSearch();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            $this->_setParamsFromSession($form->getInputElements());
            $this->_renderFormPage($formHeader, $form);
        }
    }
    
    protected function _renderFormPage($formHeader, $form)
    {
        $this->view->formHeader = $formHeader;
        $elements = $form->getInputElements();
        // Set form input values from request params
        foreach($elements as $el) {
            $field = $form->getElement($el);
            if($field) {
                $v = $this->_getparam($el, null);
                if($v !== null) {
                    $field->setValue($this->_getParam($el));
                }
            }
        }
        $this->view->contentClass = 'search-box';
        $this->view->form = $form;
        $this->renderScript('search/form.phtml');
    }
    
    protected function _renderResultsPage(array $elements = array())
    {
        $items = $this->_getItemsPerPage();
        
        if(!isset($this->view->searchString)) {
            $this->view->searchString = $this->_getParam('key');
        }
        $this->view->urlParams = array(
            'key' => $this->_getParam('key'),
            'match' => $this->_getParam('match'),
            'sort' => $this->_getParam('sort', 'name')
        );
        $paginator = $this->_getPaginator(
            $this->_getSearchQuery($this->_getParam('action')),
            $this->_getParam('page', 1),
            $items
        );
        
        $this->_logger->debug($paginator->getCurrentItems());
        $this->view->data =
            $this->getHelper('DataFormatter')->formatSearchResults($paginator);
        $this->view->paginator = $paginator;
        $this->view->sort = $this->_getParam('sort', 'name');
        $this->view->form = $this->getHelper('FormLoader')
            ->getItemsForm(
                array_merge(array('key'), $elements), $items
            );
        
        // Results table differs depending on the action
        $this->view->results = $this->view->render(
            'search/results/' . $this->_getParam('action') . '.phtml'
        );
        // Render the results layout
        $this->renderScript('search/results/layout.phtml');
    }
    
    protected function _getItemsPerPage()
    {
        $items = (int)$this->_getParam('items', null);
        if(!$items) {
            $items =
                (int)$this->getHelper('SessionHandler')->get('items', false);
            if(!$items) {
                $items = ACI_Model_Search::ITEMS_PER_PAGE;
            }
        }
        $this->getHelper('SessionHandler')->set('items', $items, false);
        $this->_setParam('items', $items);
        return $items;
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
        $this->_logger->debug($query);
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
                    $this->_getParam('key'), $this->_getParam('match'),
                    $this->_getParam('sort')
                );
                break;
            case 'scientific':
                $query = $select->scientificNames(
                    array(
                        'genus' => $this->_getParam('genus'),
                        'species' => $this->_getParam('species'),
                        'infraspecies' => $this->_getParam('infraspecies')
                    ),
                    $this->_getParam('match'),
                    $this->_getParam('sort')
                );
                break;
            case 'distribution':
                $query = $select->distributions(
                    $this->_getParam('key'), $this->_getParam('match'),
                    $this->_getParam('sort')
                );
                break;
            case 'all':
            default:
                $query = $select->all(
                    $this->_getParam('key'), $this->_getParam('match'),
                    $this->_getParam('sort')
                );
                break;
        }
        return $query;
    }
    
    /**
     * Returns an array with all taxa names by rank on a dojo-suitable format
     * Used to populate the scientific search combo boxes
     *
     * @return void
     */
    protected function _fetchTaxaByRank($rank, $query, $params)
    {
        $params = $this->_filterParams(
            $this->_decodeKey($params), $rank
        );
        $query = str_replace('\\', '', $query);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->fetchTaxaByRank($rank, $query, $params);
        foreach ($res as &$row) {
            $row['label'] = $this->getHelper('TextDecorator')->highlightMatch(
                $row['name'], substr($query, 1, -1)
            );
        }
        $this->_logger->debug($res);
        return new Zend_Dojo_Data('name', $res, $rank);
    }
    
    protected function _filterParams($params, $rank) {
        if(isset($params[$rank])) {
            unset($params[$rank]);
        }
        if(empty($params)) {
            return array();
        }
        $search = new ACI_Model_Search($this->_db);
        foreach($params as $r => $str) {
            if(trim($str) == '') {
                unset($params[$r]);
                continue;
            }
            if(!$search->taxaExists($r, $str)) {
                unset($params[$r]);
            }
        }
        return $params;
    }
    
    protected function _decodeKey($key)
    {
        $res = Zend_Json::decode(stripslashes($key));
        if(!is_array($res)) {
            return array();
        }
        return $res;
    }
    
    protected function _tagLatestSearch()
    {
        $this->getHelper('SessionHandler')->set(
            'latest_search',
            $this->getRequest()->getActionName(),
            false
        );
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}