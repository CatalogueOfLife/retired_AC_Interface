<?php
/**
 * Annual Checklist Interface
 *
 * Class InfoController
 * Defines the info pages
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class InfoController extends Zend_Controller_Action
{
    protected $_logger;
    protected $_db;
    
    public function init ()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->_db = Zend_Registry::get('db');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    public function aboutAction ()
    {
        $this->view->title = $this->view->translate('Info_about');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function acAction ()
    {
        $this->view->title = sprintf($this->view
            ->translate('Info_annual_checklist'), $this->view->app->version);
        $this->view->headTitle($this->view->title, 'APPEND');

    }
    
    public function databasesAction ()
    {
        $info = new ACI_Model_Info($this->_db);
    	$this->view->title = $this->view->translate('Source_databases');
        $this->view->headTitle($this->view->title, 'APPEND');
        $dbTable = new ACI_Model_Table_Databases();
        $sort = $info->_getRightColumnName(array('source'));
        if($this->_getParam('sort'))
        {
            $this->view->sort = $this->_getParam('sort');
        	$sort = array_merge(array($info->_getRightColumnName($this->_getParam('sort'))),$sort);
        }
        else
        {
            $this->view->sort = 'source';
        }
        $this->view->tableResults = $this->_createTableFromResults(
          $dbTable->fetchAll(null, $sort)
        );
    }
    
    public function hierarchyAction ()
    {
        $this->view->title = $this->view->translate('Management_hierarchy');
        $this->view->headTitle($this->view->title, 'APPEND');

    }
    
    public function copyrightAction ()
    {
        $this->view->title = $this->view
            ->translate('Copyright_reproduction_sale');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function citeAction ()
    {
        $this->view->title = $this->view->translate('Cite_work');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function websitesAction ()
    {
        $this->view->title = $this->view->translate('Web_sites');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function contactAction ()
    {
        $this->view->title = $this->view->translate('Contact_us');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function acknowledgementsAction ()
    {
        $this->view->title = $this->view->translate('Acknowledgements');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function __call ($name, $arguments)
    {
        $this->_forward('about');
    }

    protected function _createTableFromResults($results)
    {
	    $resultTable = array();
	    $i = 0;
	    
	    foreach($results as $value)
	    {
	        $resultTable[$i]['name'] = $value['database_name_displayed'];
	        
	        $resultTable[$i]['english_name'] = $value['taxa'];
	        
	        $resultTable[$i]['accepted_scientific_names'] = $value['accepted_species_names'];
	        
	        $resultTable[$i]['dbLogo'] = '/images/databases/' .
	          str_replace(" ","_",$value['database_name']);
	        $resultTable[$i]['dbLabel'] = $value['database_name'];
	        $resultTable[$i]['url'] = '/details/database/id/' . $value['record_id'];
	        
	        $resultTable[$i]['link'] = $this->view->translate('Show_details');
	        $i++;
	    }
	    return $resultTable;
    }
}