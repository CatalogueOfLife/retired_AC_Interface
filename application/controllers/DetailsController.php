<?php
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
class DetailsController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
    protected $_empty;
    
    public function init()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_db = Zend_Registry::get('db');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
        $this->view->search = $this->_getParam('search', 'all');
        $this->view->key = $this->_getParam('key');
        $this->_empty = "-";
        $this->view->contentClass = 'details';
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
        $taxaId = $this->_getParam('taxa');
        $commonNameId = $this->_getParam('common');
        
        if($taxaId) {
            $fromType = 'taxa';
            $fromId = $taxaId;
        }
        elseif($commonNameId) {
            $fromType = 'common';
            $fromId = $commonNameId;
        }
        else {
            $fromType = $fromId = null;
        }
        
        if($id) {
            $detailsModel = new ACI_Model_Details($this->_db);
            $speciesDetails = $this->_decorateSpeciesDetails(
                $detailsModel->species($id, $fromType, $fromId));
        }
        else {
            $speciesDetails = false;
        }

        if($speciesDetails->synonyms == "") {
            $speciesDetails->synonyms = $this->_empty;
        }
        if($speciesDetails->common_names == "") {
            $speciesDetails->common_names = $this->_empty;
        }
        if($speciesDetails->hierarchy == "") {
            $speciesDetails->hierarchy = $this->_empty;
        }
        if($speciesDetails->distribution == "") {
            $speciesDetails->distribution = $this->_empty;
        }
        else {
            $speciesDetails->distribution = implode(
                '; ', $speciesDetails->distribution
            );
        }
        if($speciesDetails->comment == "") {
            $speciesDetails->comment = $this->_empty;
        }
        if($speciesDetails->db_id == "" && $speciesDetails->db_name = "" &&
          $speciesDetails->db_version = "") {
            $speciesDetails->db_name = $this->_empty;
        }
        if($speciesDetails->scrutiny_date == "" &&
          $speciesDetails->specialist_name = "") {
            $speciesDetails->scrutiny_date = $$this->_empty;
        }
        if($speciesDetails->web_site == "") {
            $speciesDetails->web_site = $this->_empty;
        }
        if($speciesDetails->lsid == "") {
            $speciesDetails->lsid = $this->_empty;
        }
        
        
        $title =
            $speciesDetails &&
            $speciesDetails->rank == ACI_Model_Taxa::RANK_INFRASPECIES ?
                'Infraspecies_details' : 'Species_details';
        $this->view->title = $this->view->translate($title);
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $this->_logger->debug($speciesDetails);
        $this->view->species = $speciesDetails;
    }
    
    protected function _decorateSpeciesDetails(ACI_Model_Taxa $speciesDetails)
    {
        $preface = '';
        if($speciesDetails->taxa_status) {
            $preface = '<p>' . sprintf($this->view->translate('You_selected'),
                $speciesDetails->taxa_full_name) .
                (strrpos($speciesDetails->taxa_full_name, '.') ==
                    strlen($speciesDetails->taxa_full_name) - 1 ? ' ' : '. ');
            switch($speciesDetails->taxa_status) {
                case ACI_Model_Taxa::STATUS_COMMON_NAME:
                    $preface .= $this->view
                        ->translate('This_is_a_common_name_for') . ':';
                break;
                case ACI_Model_Taxa::STATUS_SYNONYM:
                    $preface .= $this->view
                        ->translate('This_is_a_synonym_for') . ':';
                break;
                case ACI_Model_Taxa::STATUS_AMBIGUOUS_SYNONYM:
                    $preface .= $this->view
                        ->translate('This_is_an_ambiguous_synonym_for') . ':';
                break;
                case ACI_Model_Taxa::STATUS_MISAPPLIED_NAME:
                    $preface .= $this->view
                        ->translate('This_is_a_misapplied_name_for') . ':';
                break;
            }
            $preface .= '</p>';
        }
        $speciesDetails->name .= ' (' .
            $this->view->translate(
                ACI_Model_Taxa::getStatusString($speciesDetails->status)
            ) . ')';
        $speciesDetails->preface = $preface;
        return $speciesDetails;
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}