<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class DetailsController
 * Defines the detail pages
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class DetailsController extends AController
{
    protected $_empty;
    private $_jsTreeTranslation = array(
    	'All_regions_retrieved',
		'x_out_of_y_regions_retrieved',
    	'There_are_no_regions_to_show',
    	'failed_to_retrieve_region'
    );
    
    public function init ()
    {
        parent::init();
        $this->view->key = $this->_getParam('key');
        $this->view->contentClass = 'details';
    }

    /**
     * Handles the reference details requests
     * Accepted request parameters are:
     * - species -> scientific name id to load the references from
     * OR
     * - id -> reference id
     */
    public function referenceAction ()
    {
        $speciesId = (int) $this->_getParam('species');
        $synonymId = (int) $this->_getParam('synonym');
        $referenceId = $this->_getParam('id');
        $references = false;
        $sn = false;
        
        $detailsModel = new ACI_Model_Details($this->_db);
        
        if ($referenceId) {
            $ids = explode(',', $referenceId);
            foreach ($ids as $id) {
                $references[] = $detailsModel->getReferenceById($id);
            }
            $preface = $this->getHelper('DataFormatter')->getReferencesLabel(count($ids));
        }
        elseif ($speciesId) {
            $taxa = $detailsModel->getScientificName($speciesId);
            if ($taxa instanceof ACI_Model_Table_Taxa) {
                $references = $detailsModel->getReferencesByTaxonId(
                    $taxa->id);
                $numReferences = count($references);
                $preface = $this->getHelper('DataFormatter')->getReferencesLabel(
                    $numReferences, $taxa->name);
            }
        }
        elseif ($synonymId) {
            $taxa = $detailsModel->getSynonymName($synonymId);
            if ($taxa instanceof ACI_Model_Table_Taxa) {
                $references = $detailsModel->getReferencesBySynonymId(
                    $taxa->id);
                $numReferences = count($references);
                $preface = $this->getHelper('DataFormatter')->getReferencesLabel(
                    $numReferences, $taxa->taxaFullName);
            }
        }
        $this->view->title = $this->view->translate('Literature_references');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_logger->debug($references);
        $this->view->references = $references;
        $this->view->sn = $sn;
        $this->view->preface = $preface;
    }

    public function databaseAction ()
    {
        $this->view->title = $this->view->translate('Database_details');
        $this->view->headTitle($this->view->title, 'APPEND');
        $dbTable = new ACI_Model_Table_Databases();
        $dbDetails = $dbTable->get($this->_getParam('id'));
        if ($dbDetails) {
            $dbDetails = $this->getHelper('DataFormatter')->formatDatabaseDetails($dbDetails);
        }
        $this->_logger->debug($dbDetails);
        $this->view->db = $dbDetails;
        $this->view->indicatorsModuleEnabled = $this->_moduleEnabled('indicators');
    }

    public function speciesAction ()
    {
        $id = $this->_getParam('id');
        $source = $this->_getParam('source', '');
        $commonNameId = $this->_getParam('common');
        $synonymId = $this->_getParam('synonym');
        $speciesDetails = false;
        
        if ($commonNameId) {
            $fromType = 'common';
            $fromId = $commonNameId;
        }
        elseif ($synonymId) {
            $fromType = 'taxa';
            $fromId = $synonymId;
        }
        else {
            $fromType = $fromId = null;
        }
        if ($id) {
            $detailsModel = new ACI_Model_Details($this->_db);
            // This will modify the id to that of the accepted name for synonyms
            // and keep the same for accepted names
            if (ACI_Model_Table_Taxa::isSynonym(
                $detailsModel->speciesStatus($synonymId))) {
                $links = $detailsModel->synonymLinks($synonymId);
            }
            if ($detailsModel->species($id, $fromType, $fromId)) {
                $speciesDetails = $this->getHelper('DataFormatter')->formatSpeciesDetails(
                    $detailsModel->species($id, $fromType, 
                        $fromId));
            }
        }
		$this->view->mapInSpeciesDetailEnabled = $this->_moduleEnabled(
            'map_species_details');
        
		$title = $speciesDetails && $speciesDetails->infra_id != '' ? 'Infraspecies_details' : 'Species_details';
        $this->view->title = $this->view->translate($title);
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $this->_logger->debug($speciesDetails);
        $this->view->jsTranslation = $this->_createJsTranslationArray($this->_jsTreeTranslation);
        $this->view->species = $speciesDetails;
        $this->view->source = $source;
        $this->view->creditsModuleEnabled = $this->_moduleEnabled('credits');
        $this->view->indicatorsModuleEnabled = $this->_moduleEnabled('indicators');
        $this->view->imagesModuleDatabaseEnabled = $this->_moduleEnabled('images_database');
        $this->view->imagesModuleAjaxEnabled = $this->_moduleEnabled(
            'images_ajax');
        $this->view->mapModuleEnabled = $this->_moduleEnabled(
            'map_species_details');
        if ($speciesDetails && $this->view->imagesModuleAjaxEnabled) {
            $this->view->dojo()->enable();
            $this->view->ajaxUri = '/ajax/images/name/' . $this->view->species->genus . ' ' .
                 $this->view->species->species;
            if ($this->view->species->infra) {
                $this->view->ajaxUri .= '+' . $this->view->species->infra;
            }
            $this->view->webserviceTimeoutInMs = $this->_webserviceTimeout * 1000;
        }
        $this->view->googleMaps = true;
	    $regions = array();
	    if($speciesDetails && is_array($speciesDetails->distribution)) {
	        foreach($speciesDetails->distribution as $dist) {
	        	if($dist['region_standard'] != 0)
			        $regions[] = $dist['id'];
	        }
	    }
        $this->view->hasTransliterations = $this->_hasTransliterations($speciesDetails->commonNames);
        $this->view->regionsCount = count($regions);
        $this->view->regions = implode(',',$regions);
        $translator = Zend_Registry::get('Zend_Translate');
        
        switch ($speciesDetails->status) {
        		case ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME:
        			$name_status_written = $translator->translate(
        				'STATUS_ACCEPTED_NAME'
        			);
        			break;
                case ACI_Model_Table_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME:
        			$name_status_written = $translator->translate(
        				'STATUS_PROVISIONALLY_ACCEPTED_NAME'
        			);
        			break;
        }
        $this->view->name_status_written = ' ('.$name_status_written.')';
    }

    public function __call ($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
    
    private function _hasTransliterations ($commonnames)
    {
        if (is_array($commonnames) && !empty($commonnames)) {
            foreach ($commonnames as $cn) {
                if (isset($cn['transliteration']) && !empty($cn['transliteration'])) {
                    return true;
                }
            }
        }
        return false;
    }
}
