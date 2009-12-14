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
        $formIsValid = $form->isValid($this->_getAllParams());
        
        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $formIsValid) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
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
                $this->getHelper('Query')->fetchTaxaByRank(
                    $fetch, $this->_getParam('q'), $this->_getParam('p')
                )
            );
        }
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());
        // Results page
        if ($this->_hasParam('match') && $this->_getParam('submit', 1) &&
            $formIsValid) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->view->searchString = 'Search_results_for_scientific_names';
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        // Form page
        } else {
            if (!$formIsValid && $this->_hasParam('match')) {
                $this->_setSessionFromParams($form->getInputElements());
            }
            if ($this->_getParam('submit', 1)) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        }
    }
    
    public function distributionAction()
    {
        $this->view->title = $this->view->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $form = $this->_getSearchForm();
        
        if ($form->isValid($this->_getAllParams()) &&
            $this->_hasParam('key') && $this->_getParam('submit', 1)) {
                
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
            
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
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
        
        if ($form->isValid($this->_getAllParams()) &&
            $this->_hasParam('key') && $this->_getParam('submit', 1)) {
                
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
            
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($formHeader, $form);
        }
    }
    
    public function exportAction()
    {
        if ($this->_hasParam('export') &&
            $this->getHelper('Query')->getLatestQuery()) {
            $this->_exportResults();
        }
        $this->view->form = $this->getHelper('FormLoader')->getExportForm();
        $this->view->export_limit =
            $this->getHelper('Export')->getNumRowsLimit();
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }
}