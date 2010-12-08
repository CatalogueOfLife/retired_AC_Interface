<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class BrowseController
 * Defines the browse actions
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class BrowseController extends AController
{
    // Tree persistance
    protected $_persistTree = false;
    
    public function treeAction()
    {
        $fetch = $this->_getParam('fetch', false);
        if ($fetch !== false) {
            $this->view->layout()->disableLayout();
            exit($this->_getTaxonChildren($this->_getParam('id', 0)));
        }
        $id = false;
        $species = $this->_getParam('species', false);
        if ($species) {
            $id = $this->_getTaxaFromSpeciesId($species);
        } else {
            $id = $this->_getParam('id', false);
        }
        $this->_persistTree($id);
        
        $hierarchy = array();
        if ($id !== false) {
            $hierarchy = $this->_getHierarchy($id);
        }
        $this->_logger->debug($hierarchy);
        $this->view->hierarchy = implode(',', $hierarchy);
        $this->view->title = $this->view->translate('Taxonomic_tree');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->view->dojo()->enable()
             ->registerModulePath(
                 'ACI', $this->view->baseUrl() . JS_PATH . '/library/ACI'
             )
             ->requireModule('dojo.parser')
             ->requireModule('dojox.data.QueryReadStore')
             ->requireModule('ACI.dojo.TxStoreModel')
             ->requireModule('ACI.dojo.TxTree')
             ->requireModule('ACI.dojo.TxTreeNode');
    }
    
    /**
     * Stores/retrieves the latest status of the tree if tree presistance is
     * enabled
     *
     * @param int $id
     * @return boolean
     */
    protected function _persistTree(&$id)
    {
        if (!$this->_persistTree) {
            return false;
        }
        // If no id or species was passed
        if (!$id) {
            // get the id from persistance (session)
            $id = $this->getHelper('SessionHandler')->get('tree_id', false);
        }
        else {
            // persist current id in session
            $this->getHelper('SessionHandler')->set('tree_id', $id, false);
        }
        return true;
    }
    
    public function classificationAction()
    {
        $reset = $this->_getParam('reset', false);
        if($reset) {
            $this->getHelper('SessionHandler')->clear('kingdom');
            $this->getHelper('SessionHandler')->clear('phylum');
            $this->getHelper('SessionHandler')->clear('class');
            $this->getHelper('SessionHandler')->clear('order');
            $this->getHelper('SessionHandler')->clear('superfamily');
            $this->getHelper('SessionHandler')->clear('family');
            $this->getHelper('SessionHandler')->clear('genus');
            $this->getHelper('SessionHandler')->clear('species');
            $this->getHelper('SessionHandler')->clear('infraspecies');
            $this->getHelper('SessionHandler')->clear('match');
        }
        // Search hint query request
        $fetch = $this->_getParam('fetch', false);
        if ($fetch) {
            $this->view->layout()->disableLayout();
            exit(
                $this->getHelper('Query')->fetchTaxaByRank(
                    $fetch, $this->_getParam('q'), $this->_getParam('p')
                )
            );
        }
        // Prefill form fields from request
        $name = $this->_getParam('name', false);
        if ($name) {
           $this->_setParamForTaxa($name);
        }
        $this->view->title = $this->view
            ->translate('Browse_taxonomic_classification');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());
        // Results page
        if ($this->_hasParam('match') && $this->_getParam('submit', 1) &&
            $formIsValid) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->view->searchString =
                'Search_results_for_taxonomic_classification';
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        // Form page
        } else {
            if (!$formIsValid && $this->_hasParam('match')) {
                // TODO: remove next line, it's useless
                $this->view->formError = true;
                $this->_setSessionFromParams($form->getInputElements());
            }
            if ($this->_getParam('submit', 1) && !$name) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function exportAction()
    {
        if ($this->_hasParam('export') &&
            $this->getHelper('Query')->getLatestQuery()) {
            $this->_exportResults();
        } else {
            $this->view->form = $this->getHelper('FormLoader')->getExportForm();
            $this->view->export_limit =
                $this->getHelper('Export')->getNumRowsLimit();
            $this->renderScript('search/export.phtml');
        }
    }
    
    /**
     * Prefills the taxonomic browser form with the hierarchy of the given
     * rank name
     *
     * @param string $name
     */
    protected function _setParamForTaxa($name)
    {
        $select = new ACI_Model_Search($this->_db);
        $taxaRecords = $select->getRecordIdFromName($name);
        
        if (!empty($taxaRecords)) {
            $hierarchy = $this->_getHierarchy($taxaRecords[0]['id']);
            if (is_array($hierarchy)) {
                // prefill the form with the hierarchy values
                foreach ($hierarchy as $rank) {
                    if ($rank != 0) {
                        $temp = $select->getRankAndNameFromRecordId($rank);
                        $this->_setParam(
                            strtolower($temp[0]['rank']), $temp[0]['name']
                        );
                    }
                }
            }
        }
    }
    
    /**
     * Returns an array with all taxa names by rank on a dojo-suitable format
     * Used to populate the browse by classification search combo boxes
     *
     * @return void
     */
    protected function _sendRankData($rank)
    {
        $substr = trim(str_replace('*', '', $this->_getParam('name')));
        $this->_logger->debug($substr);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->getRankEntries($rank, $this->_getParam('name'));
        foreach ($res as &$row) {
            $row['label'] = $this->getHelper('TextDecorator')
                ->highlightMatch($row['name'], $substr, false);
        }
        $this->_logger->debug($res);
        exit(new Eti_Dojo_Data('name', $res, $rank));
    }
    
    /**
     * Returns an array with all the children of a given taxon on a
     * dojo-suitable format
     * Used to populate the taxonomic tree
     *
     * @return void
     */
    protected function _getTaxonChildren($parentId)
    {
        $this->_logger->debug($parentId);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->getTaxonChildren($parentId);
        $this->_logger->debug($res);
        $higher_taxon = array('','phylum','class','order','superfamily',
                'family','genus','subgenus');
        foreach ($res as &$row) {
            // If not properly encoded, the names with diacritics are truncated
            // in the tree
            $row['name'] = utf8_encode($row['name']);
            $row['type'] = $row['type'] == "kingdom" ? '' : $row['type'];
            $row['url'] = !in_array($row['type'],
                $higher_taxon
            ) ?
                $this->view->baseUrl() . '/details/species/id/' . $row['id'] .
                    '/source/tree' : null;
            //TODO: Get infraspecies marker in between
/*            if (!in_array($row['type'],array_merge($higher_taxon,array('species')))) {
                $row['name'] = $this->getHelper('DataFormatter')
                    ->splitByMarkers($row['name']);
            }*/
        }
        
        $data = new Zend_Dojo_Data('id', $res, $parentId);
        $data->setLabel('name');
        return $data;
    }
    
    protected function _getTaxaFromSpeciesId($speciesId)
    {
        $search = new ACI_Model_Search($this->_db);
        return $search->getTaxaFromSpeciesId($speciesId);
    }
    
    /**
     * Gets the upwards hierarchy for a given taxa id
     *
     * @param $id
     * @return array | false
     */
    protected function _getHierarchy($id)
    {
        $details = new ACI_Model_Details($this->_db);
        $res = $details->speciesHierarchy($id);
        $hierarchy = array(0);
        foreach ($res as $row) {
            $hierarchy[] = $row['record_id'];
        }
        return $hierarchy;
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('tree');
    }
}