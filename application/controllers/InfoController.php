<?php
require_once 'AController.php';
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
class InfoController extends AController
{
    public function aboutAction ()
    {
        $this->view->title = $this->view->translate('Info_about');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    public function acAction ()
    {
        $this->view->title =
            sprintf(
                $this->view->translate('Info_annual_checklist'),
                $this->view->app->edition
            );
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    public function databasesAction ()
    {
        $this->view->title = $this->view->translate('Source_databases');
        $this->view->headTitle($this->view->title, 'APPEND');
        
        $defaultSortCol = 'source';
        $sortCol = $this->_getParam('sort', $defaultSortCol);
        $this->view->sort = $sortCol;
        
        $dbTable = new ACI_Model_Table_Databases();
        $rowset =
            $dbTable->getAll(
                array_merge(
                    array(ACI_Model_Info::getRightColumnName($sortCol)),
                    array(ACI_Model_Info::getRightColumnName($defaultSortCol))
                )
            );
        $results = array();
        foreach ($rowset as $row) {
            $results[] = $this->getHelper('DataFormatter')
                ->formatDatabaseDetails($row);
        }
        $this->view->results = $results;
        $this->_setNavigator();
    }
    
    public function hierarchyAction ()
    {
        $this->view->title = $this->view->translate('Management_hierarchy');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    public function copyrightAction ()
    {
        $this->view->title = $this->view
            ->translate('Copyright_reproduction_sale');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    public function citeAction ()
    {
        $this->view->title = $this->view->translate('Cite_work');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    public function websitesAction ()
    {
        $this->view->title = $this->view->translate('Web_sites');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    public function contactAction ()
    {
        $this->view->title = $this->view->translate('Contact_us');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    public function acknowledgementsAction ()
    {
        $this->view->title = $this->view->translate('Acknowledgements');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }
    
    protected function _setNavigator()
    {
        $this->view->navigator_top =
            $this->getHelper('Renderer')->getInfoNavigator('top');
        $this->view->navigator_bottom =
            $this->getHelper('Renderer')->getInfoNavigator('bottom');
    }
    
    public function __call ($name, $arguments)
    {
        $this->_forward('about');
    }
    
}