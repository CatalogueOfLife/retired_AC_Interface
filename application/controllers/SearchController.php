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
        $sn = $this->getHelper('SessionHandler');
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->_renderResultsPage($form->getInputElements());
        } else {
            $this->_setParamsFromSession($form->getInputElements());
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function scientificAction()
    {
        $fetch = $this->_getParam('fetch', false);
        if ($fetch) {
            $this->view->layout()->disableLayout();
            exit($this->_fetchTaxaByRank($fetch));
        }
        
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $form = $this->_getSearchForm();
        $sn = $this->getHelper('SessionHandler');
        if ($this->_hasParam('genus') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
            $key = '';
            foreach($form->getInputElements() as $el) {
                $key .= ' ' . $this->_getParam($el);
            }
            $this->_setParam('key', trim($key));
            $this->_renderResultsPage($form->getInputElements());
        } else {
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
        $sn = $this->getHelper('SessionHandler');
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
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
        $sn = $this->getHelper('SessionHandler');
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $form->isValid($this->_getAllParams())) {
            $this->_setSessionFromParams($form->getInputElements());
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
                $field->setValue($this->_getParam($el));
            }
        }
        $this->view->contentClass = 'search-box';
        $this->view->form = $form;
        $this->renderScript('search/form.phtml');
    }
    
    protected function _renderResultsPage(array $elements)
    {
        $items = (int)$this->_getParam(
            'items',
            ACI_Model_Search::ITEMS_PER_PAGE
        );

        $this->view->urlParams = array(
            'key' => $this->_getParam('key'),
            'match' => $this->_getParam('match'),
            'items' => $items,
            'sort' => $this->_getParam('sort', 'name')
        );
        
        // Get the paginator
        $this->view->paginator = $this->_getPaginator(
            $this->_getSearchQuery($this->_getParam('action')),
            $this->_getParam('page', 1),
            $items
        );
        
        $this->view->paginator->urlParams = $this->view->urlParams;
        
        $this->_logger->debug($this->view->paginator->getCurrentItems());
        $this->view->data = $this->_createTableFromResults();
        
        $this->_logger->debug($elements);
        
        // Build items per page form
        $form = new ACI_Form_Dojo_ItemsPerPage();
        // Dynamically set hidden fields
        foreach($elements as $el) {
            $form->addElement(
                $form->createElement('hidden', $el)
                     ->setValue($this->_getParam($el))
            );
        }
        $form->getElement('items')->setValue($items);
        $form->setAction($this->getHelper('FormLoader')->getAction());
        
        $this->view->search = $this->_getParam('search');
        $this->view->form = $form;
        
        // Results table differs depending on the action
        $this->view->results = $this->view->render(
            'search/results/' . $this->_getParam('action') . '.phtml'
        );
        
        // Render the results layout
        $this->renderScript('search/results/layout.phtml');
    }
    
    /**
     * Builds the result table
     *
     * @return $resultTable
     */
    protected function _createTableFromResults()
    {
        $resultTable = array();
        $i = 0;
        
        foreach ($this->view->paginator as $row) {
            if ($row['rank'] >= ACI_Model_Table_Taxa::RANK_SPECIES) {
                $resultTable[$i]['link'] =
                    $this->view->translate('Show_details');
                $resultTable[$i]['url'] =
                    '/details/species/id/' . $row['accepted_species_id'];
                if (!$row['is_accepted_name']) {
                    if ($row['status'] == ACI_Model_Table_Taxa::STATUS_COMMON_NAME) {
                        $resultTable[$i]['url'] .= '/common/' . $row['taxa_id'];
                    } else {
                        $resultTable[$i]['url'] .= '/taxa/' . $row['taxa_id'];
                    }
                }
            } else {
                $resultTable[$i]['link'] = $this->view->translate('Show_tree');
                $resultTable[$i]['url'] = '/browse/tree/id/' . $row['taxa_id'];
            }
            $resultTable[$i]['name'] = $this->_getSuffix(
                $this->_getSpanTaxonomicName(
                    $this->_highlightMatch(
                        $row['name'], $this->_getParam('key')
                    ),
                    $row['status'],
                    $row['rank']
                ),
                $row['status'],
                $row['status'] == ACI_Model_Table_Taxa::STATUS_COMMON_NAME ?
                $row['language'] : $row['author']
            );
            $resultTable[$i]['rank'] = $this->view->translate(
                ACI_Model_Table_Taxa::getRankString($row['rank'])
            );
            
            $resultTable[$i]['status'] = $this->view->translate(
                ACI_Model_Table_Taxa::getStatusString($row['status'])
            );
            
            $resultTable[$i]['group'] = $row['kingdom'];
            
            if (!$row['is_accepted_name']) {
                $resultTable[$i]['status'] = sprintf(
                    $resultTable[$i]['status'],
                    '<span class="taxonomicName">' .
                    $row['accepted_species_name'] . '</span> ' .
                    $row['accepted_species_author']
                );
            }
            
            $resultTable[$i]['dbLogo'] = '/images/databases/' .
                $row['db_thumb'];
            $resultTable[$i]['dbLabel'] = $row['db_name'];
            $resultTable[$i]['dbUrl'] =
                '/details/database/id/' . $row['db_id'];
            if(isset($row['distribution']))
            {
                $resultTable[$i]['distribution'] = $row['distribution'];
            }
            $i++;
        }
        return $resultTable;
    }
    
    protected function _getSuffix($source, $status, $suffix)
    {
        switch($status && $suffix != "") {
            case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                $source .= ' (' . $suffix . ')';
                break;
            default:
                $source .= '  ' . $suffix;
                break;
        }
        return $source;
    }

    protected function _getSpanTaxonomicName($source, $status, $rank)
    {
        if ($status != ACI_Model_Table_Taxa::STATUS_COMMON_NAME &&
            $rank >= ACI_Model_Table_Taxa::RANK_SPECIES) {
            $source = '<span class="taxonomicName">' . $source . '</span>';
        }
        return $source;
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
                    ), $this->_getParam('sort')
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
    protected function _fetchTaxaByRank($rank)
    {
        $params = $this->_filterParams(
            Zend_Json::decode(stripslashes($this->_getParam('p'))), $rank
        );
        $this->_logger->debug($params);
        $substr = trim(str_replace('*', '', $this->_getParam('q')));
        $this->_logger->debug($substr);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->fetchTaxaByRank($rank, $this->_getParam('q'), $params);
        foreach ($res as &$row) {
            $row['label'] = $this->_highlightMatch($row['name'], $substr);
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
    
    public function __call($name, $arguments)
    {
        $this->_logger->debug("$name");
        $this->_forward('all');
    }
}