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
        $synonymId = (int)$this->_getParam('synonym');
        $referenceId = $this->_getParam('id');
        $references = false;
        $sn = false;
        
        $detailsModel = new ACI_Model_Details($this->_db);
        
        if ($referenceId) {
            $ids = explode(',', $referenceId);
            foreach ($ids as $id) {
                $references[] = $detailsModel->getReferenceById($id);
            }
            $preface = $this->getHelper('DataFormatter')
                ->getReferencesLabel(count($ids));
        } elseif ($speciesId) {
            $taxa = $detailsModel->getScientificName($speciesId);
            if ($taxa instanceof ACI_Model_Table_Taxa) {
                $references =
                   $detailsModel->getReferencesByTaxonId($taxa->id);
                $numReferences = count($references);
                $preface = $this->getHelper('DataFormatter')
                   ->getReferencesLabel($numReferences, $taxa->name);
            }
        } elseif ($synonymId) {
            $taxa = $detailsModel->getSynonymName($synonymId);
            if ($taxa instanceof ACI_Model_Table_Taxa) {
                $references =
                   $detailsModel->getReferencesBySynonymId($taxa->id);
                $numReferences = count($references);
                $preface = $this->getHelper('DataFormatter')
                   ->getReferencesLabel($numReferences, $taxa->name);
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
        if ($dbDetails) {
            $dbDetails = $this->getHelper('DataFormatter')
                ->formatDatabaseDetails($dbDetails);
        }
        $this->_logger->debug($dbDetails);
        $this->view->db = $dbDetails;
    }
    
    public function speciesAction()
    {
        $id = $this->_getParam('id');
        $source = $this->_getParam('source', '');
        $commonNameId = $this->_getParam('common');
        $synonymId = $this->_getParam('synonym');
        $speciesDetails = false;
        
        if ($commonNameId) {
            $fromType = 'common';
            $fromId = $commonNameId;
        } elseif ($synonymId) {
            $fromType = 'taxa';
            $fromId = $synonymId;
        } else {
            $fromType = $fromId = null;
        }
        if ($id) {
            $detailsModel = new ACI_Model_Details($this->_db);
            // This will modify the id to that of the accepted name for synonyms
            // and keep the same for accepted names
            if (ACI_Model_Table_Taxa::isSynonym(
                $detailsModel->speciesStatus($synonymId)
            )) {
                $links = $detailsModel->synonymLinks($synonymId);
            }
            if ($detailsModel->species($id, $fromType, $fromId)) {
                $speciesDetails =
                    $this->getHelper('DataFormatter')->formatSpeciesDetails(
                        $detailsModel->species($id, $fromType, $fromId)
                    );
            }
        }
        $title = $speciesDetails && $speciesDetails->infra_id != '' ?
           'Infraspecies_details' : 'Species_details';
        $this->view->title = $this->view->translate($title);
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_logger->debug($speciesDetails);
        $this->view->species = $speciesDetails;
        $this->view->source = $source;
    }
     
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}