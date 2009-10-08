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
        $this->_empty = "-";
        $this->view->contentClass = 'details';
    }
    
    public function referenceAction()
    {
    	$taxa = $this->_getParam('taxa');
    	$common = $this->_getParam('common');
    	$id = 0;
    	$type = '';
    	
        $preface = '<p>';

        if($taxa){
        	$id = $taxa;
            $type = 'taxa';
	        $preface .= preg_replace(
	            array(
	                '(%count%)',
	                '(%name%)'
	            ),
	            array(
	                count($this->view->ref),
	                $taxaDetails->name
	            ),
	            (count($this->view->ref) == 1 ?
	                $this->view->translate('literature_reference_found_for') :
	                $this->view->translate('literature_references_found_for'))
	        );
	        $detailsModel = new ACI_Model_Details($this->_db);
	        $taxaDetails = $detailsModel->getReferenceTaxa($id);

	        $referenceModel = new ACI_Model_Table_References();
	        $refDetails = $referenceModel->get($taxaDetails->nameCode);
        }
        if($common){
        	$id = $common;
            $type = 'common';
            $preface .= preg_replace(
            array(
                '(%count%)'
            ),
            array(
                count($this->view->ref)
            ),
            (count($this->view->ref) == 1 ?
                $this->view->translate('literature_reference_found') :
                $this->view->translate('literature_references_found'))
            );
            $detailsModel = new ACI_Model_Details($this->_db);
            $taxaDetails = $detailsModel->getReferenceTaxa($id);
        }

        $preface .= ':</p>';
        
        $this->view->title = $this->view->translate('Literature_references');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $this->_logger->debug($refDetails);
        $this->view->ref = $refDetails;
        
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
                $speciesDetails = $this->_decorateSpeciesDetails(
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
    
    //TODO: move to the DataFormatter helper
    protected function _decorateSpeciesDetails(ACI_Model_Table_Taxa
        $speciesDetails)
    {
        $preface = '';
        if ($speciesDetails->taxaStatus) {
            $preface = '<p>' .
                sprintf(
                    $this->view->translate('You_selected'),
                    $speciesDetails->taxaFullName
                ) .
                (strrpos($speciesDetails->taxaFullName, '.') ==
                    strlen($speciesDetails->taxaFullName) - 1 ? ' ' : '. ');
            switch($speciesDetails->taxaStatus) {
                case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                    $preface .= $this->view
                        ->translate('This_is_a_common_name_for') . ':';
                    break;
                case ACI_Model_Table_Taxa::STATUS_SYNONYM:
                    $preface .= $this->view
                        ->translate('This_is_a_synonym_for') . ':';
                    break;
                case ACI_Model_Table_Taxa::STATUS_AMBIGUOUS_SYNONYM:
                    $preface .= $this->view
                        ->translate('This_is_an_ambiguous_synonym_for') . ':';
                    break;
                case ACI_Model_Table_Taxa::STATUS_MISAPPLIED_NAME:
                    $preface .= $this->view
                        ->translate('This_is_a_misapplied_name_for') . ':';
                    break;
            }
            $preface .= '</p>';
        }
        $speciesDetails->name .= ' (' .
            $this->view->translate(
                ACI_Model_Table_Taxa::getStatusString($speciesDetails->status)
            ) . ')';
        
        $speciesDetails->preface = $preface;
        
        if (empty($speciesDetails->synonyms)) {
            $speciesDetails->synonyms = $this->_empty;
        }
        if (empty($speciesDetails->commonNames)) {
            $speciesDetails->commonNames = $this->_empty;
        }
        if ($speciesDetails->hierarchy == '') {
            $speciesDetails->hierarchy = $this->_empty;
        }
        if ($speciesDetails->distribution == '') {
            $speciesDetails->distribution = $this->_empty;
        } else {
            $speciesDetails->distribution = implode(
                '; ', $speciesDetails->distribution
            );
        }
        if ($speciesDetails->comment == '') {
            $speciesDetails->comment = $this->_empty;
        }
        if ($speciesDetails->dbId == '' && $speciesDetails->dbName = '' &&
            $speciesDetails->dbVersion = '') {
            $speciesDetails->dbName = $this->_empty;
        }
        if ($speciesDetails->scrutinyDate == '' &&
            $speciesDetails->specialistName = '') {
            $speciesDetails->scrutinyDate = $$this->_empty;
        }
        if ($speciesDetails->webSite == '') {
            $speciesDetails->webSite = $this->_empty;
        }
        if ($speciesDetails->lsid == '') {
            $speciesDetails->lsid = $this->_empty;
        }
        return $speciesDetails;
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}