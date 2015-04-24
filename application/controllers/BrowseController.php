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
    protected $_persistTree = false;
    protected $_extinctInTree = false;
    // array used for tree translations
    // key = term; value = ucfirst (0/1)
    private $_jsTreeTranslation = array(
        'est' => 0,
        'Number_of_species' => 0,
        'Estimated_number' => 0,
        'Percentage_covered' => 0,
        'Estimation_source' => 0,
        'Source_database' => 0,
        'Source_databases' => 0,
        'Multiple_providers' => 0,
        'Close_window' => 0,
        'Name' => 0,
        'Email' => 0,
        'Type' => 0,
        'Comment' => 0,
		'wrong_branch' => 0,
	    'general_comment' => 0,
	    'correction' => 0,
	    'additional_information' => 0,
		'Send' => 0,
    	'Comment_being_processed' => 0,
    	'Searching_for_the_regions_please_wait' => 0,
    	'All_regions_retrieved' => 0,
		'x_out_of_y_regions_retrieved' => 0,
    	'There_are_no_regions_to_show' => 0,
    	'failed_to_retrieve_regions' => 0,
    	'failed_to_retrieve_region' => 0,
    	'All_fields_are_required' => 0,
    	'info_icon_title' => 0,
		'feedback_icon_title' => 0,
    	'map_icon_title' => 0,
        'is_extinct' => 1,
        'has_modern' => 1,
        'has_preholocene' => 1,
        'y' => 0,
        'n' => 0,
        'Extinct_tip' => 0,
        'extinct' => 0
    );

    public function treeAction ()
    {
        // Check if extinct taxa should be included in tree
        $this->view->fossilsModuleEnabled = $this->_moduleEnabled('fossils');
        if ($this->_moduleEnabled('fossils')) {
            $this->view->showExtinctInTreeSelected = $this->_getTreeExtinct();
        }
        // Check if thumbnails should be displayed
        $iconInTreeModuleEnabled = $this->_moduleEnabled('icons_browse_tree');
        $this->view->iconsInTreeModuleEnabled = $iconInTreeModuleEnabled;
        if ($iconInTreeModuleEnabled) {
            $this->view->showIconsInTreeSelected = $this->_getOrSetCookie('iconSpan');
        }
        // Check if map data should be displayed
        $this->view->mapInTreeModuleEnabled = $this->_moduleEnabled('map_browse_tree');
		// Check if statistics and estimations should be displayed
        $statisticsModuleEnabled = $this->_moduleEnabled('statistics');
        $this->view->statisticsModuleEnabled = $statisticsModuleEnabled;
        if ($statisticsModuleEnabled) {
            $this->view->showSourceDatabaseCheckboxSelected = $this->_getOrSetCookie('treeSourceDatabase');
            $this->view->showEstimationCheckboxSelected = $this->_getOrSetCookie('treeStatistics');
        }

        $fetch = $this->_getParam('fetch', false);
        if ($fetch !== false) {
            $this->view->layout()->disableLayout();
            exit($this->_getTaxonChildren($this->_getParam('id', 0)));
        }
        $id = false;
        $species = $this->_getParam('species', false);
        if ($species) {
            (int)$this->getHelper('DataFormatter')->naturalKeyToId($species);
        } else {
            $id = (int)$this->getHelper('DataFormatter')->naturalKeyToId($this->_getParam('id', false));
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
        $this->view->dojo()->enable()->registerModulePath('ACI',
            $this->view->baseUrl() . JS_PATH . '/library/ACI')->requireModule('dojo.parser')->requireModule(
            'dojox.data.QueryReadStore')->requireModule('ACI.dojo.TxStoreModel')->requireModule(
            'ACI.dojo.TxTree')->requireModule('ACI.dojo.TxTreeNode');

		$this->view->feedbackModuleEnabled = $this->_moduleEnabled('feedback');

        $translator = Zend_Registry::get('Zend_Translate');
        $this->view->textShowSourceDatabases = $translator->translate('Show_providers');
        $this->view->textShowExtinct = $translator->translate('Include_extinct_taxa');
        $this->view->textShowStatistics = $translator->translate('Show_statistics');
        $this->view->textShowIcons = $translator->translate('Show_thumbnail_images');
        $this->view->jsTranslation = $this->_createJsTranslationArray($this->_jsTreeTranslation);
        $config = Zend_Registry::get('config');
        $this->view->jsFeedbackUrl = $config->module->feedbackUrl;
    }

    /**
     * Stores/retrieves the latest status of the tree if tree presistance is
     * enabled
     *
     * @param int $id
     * @return boolean
     */
    protected function _persistTree (&$id)
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

    public function classificationAction ()
    {
        $reset = $this->_getParam('reset', false);
        if ($reset) {
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
        $this->view->controller = 'browse';
        $this->view->action = 'classification';
        // Search hint query request
        $fetch = $this->_getParam('fetch', false);
        if ($fetch) {
            $this->view->layout()->disableLayout();
            exit(
                $this->getHelper('Query')->fetchTaxaByRank($fetch,
                    $this->_getParam('q'),
                    $this->_getParam('p')));
        }
        // Prefill form fields from request
        $name = $this->_getParam('name', false);
        if ($name) {
            $this->_setParamForTaxa($name);
        }
        $this->view->title = $this->view->translate('Browse_taxonomic_classification');
        $this->view->headTitle($this->view->title, 'APPEND');

        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());
        // Results page
        if ($this->_hasParam('match') && $this->_getParam('submit',
            1) && $formIsValid) {
        	$searchString = array();
            if($this->_getParam('kingdom') !== null) {
        		$searchString['kingdom'] = $this->_getParam('kingdom');
        	}
            if($this->_getParam('phylum') !== null) {
        		$searchString['phylum'] = $this->_getParam('phylum');
        	}
            if($this->_getParam('order') !== null) {
        		$searchString['order'] = $this->_getParam('order');
        	}
            if($this->_getParam('class') !== null) {
        		$searchString['class'] = $this->_getParam('class');
        	}
            if($this->_getParam('superfamily') !== null) {
        		$searchString['superfamily'] = $this->_getParam('superfamily');
        	}
            if($this->_getParam('family') !== null) {
        		$searchString['family'] = $this->_getParam('family');
        	}
            if($this->_getParam('genus') !== null) {
        		$searchString['genus'] = $this->_getParam('genus');
        	}
            if($this->_getParam('subgenus') !== null) {
        		$searchString['subgenus'] = $this->_getParam('subgenus');
        	}
            if($this->_getParam('species') !== null) {
        		$searchString['species'] = $this->_getParam('species');
        	}
            if($this->_getParam('infraspecies') !== null) {
        		$searchString['infraspecies'] = $this->_getParam('infraspecies');
        	}
        	$this->_setSessionFromParams($form->getInputElements());

            $this->view->searchString = 'Search_results_for_taxonomic_classification';
            $this->view->searchParams = $searchString;
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
            // Form page
        }
        else {
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

    public function exportAction ()
    {
        if ($this->_hasParam('export') && $this->getHelper('Query')->getLatestQuery()) {
            $this->_exportResults();
        }
        else {
            $this->view->form = $this->getHelper('FormLoader')->getExportForm();
            $this->view->export_limit = $this->getHelper('Export')->getNumRowsLimit();
            $this->renderScript('search/export.phtml');
        }
    }

    /**
     * Prefills the taxonomic browser form with the hierarchy of the given
     * rank name
     *
     * @param string $id
     */
    /*    protected function _setParamForTaxa($id)
    {
        $select = new ACI_Model_Search($this->_db);
//        $taxaRecords = $select->getRecordIdFromName($name);

        $hierarchy = $this->_getHierarchy($id);
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
    }*/

    protected function _setParamForTaxa ($name)
    {
        $select = new ACI_Model_Search($this->_db);
        $taxaRecords = $select->getRecordIdFromName($name);

        if (!empty($taxaRecords)) {
            $hierarchy = $this->_getHierarchy($taxaRecords[0]['id']);
            if (is_array($hierarchy)) {
                // prefill the form with the hierarchy values
                foreach ($hierarchy as $rank) {
                    if ($rank != 0) {
                        $temp = $select->getRankAndNameFromRecordId(
                            $rank);
                        $this->_setParam(
                            strtolower(
                                $temp[0]['rank']),
                            $temp[0]['name']);
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
    protected function _sendRankData ($rank)
    {
        $substr = trim(str_replace('*', '', $this->_getParam('name')));
        $this->_logger->debug($substr);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->getRankEntries($rank, $this->_getParam('name'));
        foreach ($res as &$row) {
            $row['label'] = $this->getHelper('TextDecorator')->highlightMatch($row['name'],
                $substr, false);
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
    protected function _getTaxonChildren ($parentId)
    {
        $this->_logger->debug($parentId);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->getTaxonChildren($parentId);
        $this->_logger->debug($res);
        $higher_taxon = array(
            '',
            'phylum',
            'class',
            'order',
            'superfamily',
            'family',
            'genus',
            'subgenus'
        );
        $translator = Zend_Registry::get('Zend_Translate');
        foreach ($res as &$row) {
            // If not properly encoded, the names with diacritics are truncated
            // in the tree
            $row['name'] = utf8_encode($row['name']);
            $image = strtolower($row['name']) . '.png';
            if(file_exists(TREE_ICONS_PATH . '/' . $image)) {
                $row['image'] = $this->view->baseUrl() . '/images/tree_icons/' . $image;
            } else {
            	$row['image'] = 0;
            }
            $row['type'] = $row['type'] == "kingdom" ? '' : $row['type'];

            $row['rank'] = $row['type'] == "" ? '' : $translator->translate(
                strtoupper('RANK_' . $row['type']));
            $row['url'] = !in_array($row['type'], $higher_taxon) ? $this->view->baseUrl() . '/details/species/id/' .
                 $this->getHelper('DataFormatter')->idToNaturalKey($row['id']) . '/source/tree' : null;
            //TODO: Get infraspecies marker in between
            /* if (!in_array($row['type'],array_merge($higher_taxon,array('species')))) {
                $row['name'] = $this->getHelper('DataFormatter')
                    ->splitByMarkers($row['name']);
            }*/
            //$row['estimate_source'] = 'estimation source';
            if ($row['total'] && $row['estimation']) {
                $row['percentage'] = $this->getHelper('DataFormatter')->getCoverage(
                    $row['total'], $row['estimation']);
            } else {
                $row['percentage'] = "?";
            }

            //Checks if the module statistics is enabled
            $statisticsModuleEnabled = Bootstrap::instance()->getOption(
                'module.statistics');
            if ($statisticsModuleEnabled) {
                $row['estimation'] = $row['estimation'] == 0 ? '?' : number_format(
                    $row['estimation'], 0, '.', ',');
                $row['total'] = number_format($row['total'], 0, '.', ',');
                $row['nr_fossils'] = number_format($row['nr_fossils'], 0, '.', ',');
                $gsds = $search->getSourceDatabasesPerTaxonTreeId($row['id']);
                $row['source_databases'] = $gsds;
            }
        }

        $data = new Zend_Dojo_Data('id', $res, $parentId);
        $data->setLabel('name');
        return $data;
    }

    protected function _getTaxaFromSpeciesId ($speciesId)
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
    protected function _getHierarchy ($id)
    {
        $details = new ACI_Model_Details($this->_db);
        $res = $details->speciesHierarchy($id);
        $hierarchy = array(
            0
        );
        foreach ($res as $row) {
            $hierarchy[] = $row['record_id'];
        }
        return $hierarchy;
    }

    public function __call ($name, $arguments)
    {
        $this->_forward('tree');
    }
}