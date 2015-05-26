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

    public function init ()
    {
        parent::init();
        $info = new ACI_Model_Info($this->_db);
        $this->view->stats = $info->getStatistics();
        if ($this->_moduleEnabled('fossils')) {
            $this->view->stats['species'] = $this->view->stats['living_species'] .
                '</b> living and <b>' . $this->view->stats['extinct_species'] . '</b> extinct';
            $this->view->stats['infraspecific_taxa'] = $this->view->stats['living_infraspecific_taxa'] .
                '</b> living and <b>' . $this->view->stats['extinct_infraspecific_taxa'] . '</b> extinct';
        } else {
            $this->view->stats['species'] = $this->view->stats['living_species'];
            $this->view->stats['infraspecific_taxa'] = $this->view->stats['living_infraspecific_taxa'];
        }

    }

    public function aboutAction ()
    {
        $this->view->title = $this->view->translate('Info_about');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }

    public function specialAction ()
    {
        $this->view->title = sprintf($this->view->translate('Info_special_edition'),
            $this->view->app->edition);
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }

    public function acAction ()
    {
        $this->view->title = sprintf($this->view->translate('Info_annual_checklist'),
            $this->view->app->edition);
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }

    public function databasesAction ()
    {
        $this->view->title = $this->view->translate('Source_databases');
        $this->view->headTitle($this->view->title, 'APPEND');

        $defaultSortCol = 'source';

        $sortCol = $this->_getParam('sort', $defaultSortCol);
        $direction = $this->_getParam('direction', 'asc');

        $this->view->urlParams = array(
            'sort' => $sortCol,
            'direction' => $direction
        );

        $this->view->sortArrow = '<img src="' . $this->view->baseUrl() . '/images/' . ($direction == 'asc' ? 'Arrow_up.gif" alt="' .
             $this->view->translate('ascending') : 'Arrow_down.gif" alt="' . $this->view->translate(
                'descending')) . '" />';
        $this->view->sortDesc = $direction == 'asc' ? $sortCol : null;
        $this->view->fossilsModuleEnabled = $this->_moduleEnabled('fossils');

        $this->view->sort = $sortCol;

        $info = new ACI_Model_Info($this->_db);
        $rowset = $info->getSourceDatabases($sortCol, $direction);
        $results = array();
        foreach ($rowset as $row) {
            $results[] = $this->getHelper('DataFormatter')->formatDatabaseResultPage($row);
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
        $this->view->title = $this->view->translate('Copyright_reproduction_sale');
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
        $this->view->title = $this->view->translate('Acknowledgments');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->_setNavigator();
    }

    public function totalsAction ()
    {
        $this->view->title = $this->view->translate('Species_totals');
        $this->view->headTitle($this->view->title, 'APPEND');
        $info = new ACI_Model_Info($this->_db);
        $this->view->results = $this->getHelper('DataFormatter')->formatSpeciesTotals(
            $info->getSpeciesTotals());
        $this->_setNavigator();
        $this->view->fossilsModuleEnabled = $this->_moduleEnabled('fossils');
    }

    protected function _setNavigator ()
    {
        $this->view->navigator_top = $this->getHelper('Renderer')->getInfoNavigator('top');
        $this->view->navigator_bottom = $this->getHelper('Renderer')->getInfoNavigator('bottom');
    }

    public function __call ($name, $arguments)
    {
        $this->_forward('about');
    }

}