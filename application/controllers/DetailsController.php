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
    
    public function init()
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
    public function referenceAction()
    {
    	$speciesId = (int)$this->_getParam('species');
    	$referenceId = (int)$this->_getParam('id');
    	$references = false;
    	$sn = false;
    	$preface = '';
    	
    	$detailsModel = new ACI_Model_Details($this->_db);
    	
    	if($referenceId) {
    	    $reference = $detailsModel->getReferenceById($referenceId);
    	    if($reference) {
    	        $references = array($reference);
    	    }
    	    $preface =
    	       $this->view->translate('literature_reference_found');
    	}
    	else if($speciesId) {
    	    $taxa = $detailsModel->getScientificName($speciesId);
    	    if($taxa instanceof ACI_Model_Table_Taxa && $taxa->nameCode) {
        	    $references =
        	       $detailsModel->getReferencesByNameCode($taxa->nameCode);
        	    $numReferences = count($references);
        	    $preface = preg_replace(
                    array(
                        '(%count%)',
                        '(%name%)'
                    ),
                    array(
                        $numReferences,
                        $taxa->name
                    ),
                    ($numReferences > 1 ?
                        $this->view->translate('literature_references_found_for') :
                        $this->view->translate('literature_reference_found_for'))
                );
    	    }
    	}
        $this->view->title = $this->view->translate('Literature_references');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_logger->debug($references);
        $this->view->references = $references;
        $this->view->sn = $sn;
        $this->view->preface = $preface;
    }
    
    public function databaseAction()
    {
        $this->view->title = $this->view->translate('Database_details');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $dbTable = new ACI_Model_Table_Databases();
        $dbDetails = $dbTable->get($this->_getParam('id'));
        $this->_logger->debug($dbDetails);
        $this->view->db = $dbDetails;
    }
    
    public function speciesAction()
    {
        $id = $this->_getParam('id');
        $commonNameId = $this->_getParam('common');
        $speciesDetails = false;
        
        if ($commonNameId) {
            $fromType = 'common';
            $fromId = $commonNameId;
        } else {
            $fromType = $fromId = null;
        }
        if ($id) {
            $detailsModel = new ACI_Model_Details($this->_db);
            // This will modify the id to that of the accepted name for synonyms
            // and keep the same for accepted names
            if (ACI_Model_Table_Taxa::isSynonym(
                $detailsModel->speciesStatus($id))) {
                $fromType = 'taxa';
                $links = $detailsModel->synonymLinks($id);
                $id = $links['id'];
                $fromId = $links['taxa_id'];
            }
            if ($detailsModel->species($id, $fromType, $fromId))
            {
                $speciesDetails =
                    $this->getHelper('DataFormatter')->formatSpeciesDetails(
                        $detailsModel->species($id, $fromType, $fromId)
                    );
            }
        }
	    $title = $speciesDetails && $speciesDetails->rank ==
	       ACI_Model_Table_Taxa::RANK_INFRASPECIES ?
	       'Infraspecies_details' : 'Species_details';
        $this->view->title = $this->view->translate($title);
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_logger->debug($speciesDetails);
        $this->view->species = $speciesDetails;
    }
     
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}