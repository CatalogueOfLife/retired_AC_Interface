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
    public function treeAction()
    {
        $fetch = $this->_getParam('fetch', false);
        if ($fetch !== false) {
            $this->view->layout()->disableLayout();
            exit($this->_getTaxonChildren($this->_getParam('id', 0)));
        }
        $id = $this->_getParam('id', false);
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
                 'ACI', $this->view->baseUrl() . '/scripts/library/ACI'
             )
             ->requireModule('dojo.parser')
             ->requireModule('dojox.data.QueryReadStore')
             ->requireModule('ACI.dojo.TxStoreModel')
             ->requireModule('ACI.dojo.TxTree')
             ->requireModule('ACI.dojo.TxTreeNode');
    }
    
    public function classificationAction()
    {
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
        if ($this->_getParam('name', false)) {
           $this->_setParamForTaxa($this->_getParam('name'));
        }
        $this->view->title = $this->view
            ->translate('Browse_by_classification');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());
        //TODO: enable searching
        // Results page
        /*if ($this->_hasParam('match') && $this->_getParam('submit', 1) &&
            $formIsValid) {
            $this->_setSessionFromParams($form->getInputElements());
            $str = '';
            foreach ($form->getInputElements() as $el) {
                if ($el != 'match') {
                    $str .= ' ' . $this->_getParam($el);
                }
            }
            $this->view->searchString = trim($str);
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        // Form page
        } else {*/
            if(!$formIsValid && $this->_hasParam('match')) {
                $this->view->formError = true;
                $this->_setSessionFromParams($form->getInputElements());
            }
            if($this->_getParam('submit', 1)) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        //}
    }
    
    private function _setParamForTaxa($name)
    {
    	//TODO:
    	//Get `record_id` from `taxa`
    	$select = new ACI_Model_Search($this->_db);
    	$recordId = $select->getRecordIdFromName($name);
    	//TODO:
    	//Pass the `record_id` to _getHierarchy()
    	if($recordId)
        	$hierarchy = $this->_getHierarchy($recordId[0]['id']);
    	//TODO:
    	//read hierachy out and _setParam()'s.
    	if($hierarchy && is_array($hierarchy))
    	{
	    	foreach($hierarchy as $rank)
	    	{
	    		if($rank != 0)
	    		{
		    		$temp = $select->getRankAndNameFromRecordId($rank);
		            $this->_setParam(strtolower($temp[0]['rank']),$temp[0]['name']);
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
        exit(new Zend_Dojo_Data('name', $res, $rank));
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
        foreach ($res as &$row) {
            $row['type'] = $row['type'] == 'Kingdom' ? '' : $row['type'];
            $row['url'] = $row['snId'] ?
                $this->view->baseUrl() . '/details/species/id/' . $row['snId'] :
                null;
            $row['subsp'] = null;
            if($row['type'] == 'Infraspecies') {
                $split = explode('subsp.', $row['name']);
                if(count($split) > 1) {
                    $row['name'] = $split[0];
                    $row['subsp'] = $split[1];
                }
            }
        }
        $data = new Zend_Dojo_Data('id', $res, $parentId);
        $data->setLabel('name');
        return $data;
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