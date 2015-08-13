<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_Renderer
 * Text rendering helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
class ACI_Helper_Renderer extends Zend_Controller_Action_Helper_Abstract
{
    protected $_ac;

    public function init()
    {
        $this->_ac =  $this->getActionController();
        $this->_logger = Zend_Registry::get('logger');
    }

    public function renderFormPage($header, $form)
    {
        $elements = $form->getInputElements();
        // Set form input values from request params
        foreach ($elements as $el) {
            $field = $form->getElement($el);
            if ($field) {
                $v = $this->getRequest()->getParam($el, null);
                if ($v !== null) {
                    $field->setValue(($this->getRequest()->getParam($el)));
                }
            }
        }
        $this->_ac->view->formHeader = $header;
        $this->_ac->view->contentClass = 'centered-box';
        $this->_ac->view->form = $form;
        $this->_ac->renderScript('search/form.phtml');
    }

    public function renderResultsPage(array $elements = array())
    {
        $items = $this->_getItemsPerPage();

        $sortParam = $this->_ac->view->escape(
            $this->getRequest()->getParam(
                'sort',
                ACI_Model_Search::getDefaultSortParam(
                    $this->getRequest()->getActionName()
                )
            )
        );

        $directionParam = $this->getRequest()->getParam('direction', 'asc');

        if (isset($this->_ac->view->searchString)) {
            $this->_ac->view->searchString =
                $this->_ac->view->translate($this->_ac->view->searchString);
            $this->_ac->view->searchString .= isset($this->_ac->view->searchParams) ?
                $this->_formatSearchStringAppendix($this->_ac->view->searchParams) : '';
        } else {
            $this->_ac->view->searchString = $this->_ac->view->title . ' - ' .
                sprintf(
                $this->_ac->view->translate('Search_results_for'), '"' .
                stripslashes(
                    $this->escape($this->getRequest()->getParam('key'))
                ) . '"'
            );
        }
        $sortParam = $this->_ac->view->escape($sortParam);
        $directionParam = $this->_ac->view->escape($directionParam);
        $this->_ac->view->urlParams = array(
            'sort' => $sortParam, 'direction' => $directionParam
        );
        foreach ($elements as $e) {
             $this->_ac->view->urlParams[$e] =
                $this->getRequest()->getParam($e);
        }
        $this->_ac->view->sortArrow =
            '<img src="' . $this->_ac->view->baseUrl() . '/images/' .
            ($directionParam == 'asc' ?
                'Arrow_up.gif" alt="' .
                    $this->_ac->view->translate('ascending') :
                'Arrow_down.gif" alt="' .
                    $this->_ac->view->translate('descending')
             ) . '" />';
        $this->_ac->view->sortDesc = ($sortParam && $directionParam == 'asc') ?
            $sortParam : null;

        $query = $this->_ac->getHelper('Query')->getSearchQuery(
            $this->getRequest()->getParam('controller'),
            $this->getRequest()->getParam('action')
        );
        $paginator = $this->_getPaginator(
            $query,
            $this->_ac->getHelper('Query')->getCountQuery(
                $this->getRequest()->getParam('controller'),
                $this->getRequest()->getParam('action'),
                $query
            ),
            $this->getRequest()->getParam('page', 1),
            $items
        );
        $this->_ac->view->exportable = $paginator->getTotalItemCount() <=
            $this->_ac->getHelper('Export')->getNumRowsLimit() &&
            $paginator->getTotalItemCount() > 0;
        $this->_logger->debug($paginator->getCurrentItems());
        $this->_ac->view->data = $this->_ac->getHelper('DataFormatter')
            ->formatSearchResults($paginator);
        $this->_ac->view->paginator = $paginator;
        $this->_ac->view->sort = $sortParam;
        $this->_ac->view->form = $this->_ac->getHelper('FormLoader')
            ->getItemsForm($elements, $items);

        // Result details (moved here from view)
        $this->_ac->view->resultDetails = '';
        if ($paginator instanceof Eti_Paginator) {
            $allSpecies = $paginator->getCountColumn('species');
            $allInfraspecies = $paginator->getCountColumn('infraspecies');
            $fossilInfraspecies = $paginator->getCountColumn('fossil_infraspecies');
            $allFossils = $paginator->getCountColumn('fossil_total');
            $fossilSpecies = $allFossils - $fossilInfraspecies;
            $livingSpecies = $allSpecies - $fossilSpecies;
            $livingInfraspecies = $allInfraspecies - $fossilInfraspecies;

            $this->_ac->view->resultDetails =
                ' (' . number_format($livingSpecies) . ' ' .
                ($fossilSpecies > 0 ? $this->_ac->view->translate('living') . ' ' : '') .
                $this->_ac->view->translate('species') . ', ' .
                number_format($livingInfraspecies) . ' ' .
                ($fossilInfraspecies > 0 ? $this->_ac->view->translate('living') . ' ' : '') .
                ($livingInfraspecies == 1 ?
                    $this->_ac->view->translate('infraspecies') . ')' :
                    $this->_ac->view->translate('infraspecific_taxa'));
           if ($allFossils > 0) {
                $this->_ac->view->resultDetails .=
                    ', ' . number_format($fossilSpecies) . ' ' . $this->_ac->view->translate('extinct') .
                    ' ' .$this->_ac->view->translate('species') . ', ' .
                    number_format($fossilInfraspecies) . ' ' . $this->_ac->view->translate('extinct') . ' ' .
                    ($fossilInfraspecies == 1 ?
                        $this->_ac->view->translate('infraspecies') . ')' :
                        $this->_ac->view->translate('infraspecific_taxa'));
           }
           $this->_ac->view->resultDetails .= ')';
        }

        // Results table differs depending on the action
        $this->_ac->view->results = $this->_ac->view->render(
            'search/results/' .
            $this->getRequest()->getParam('action') .
            '.phtml'
        );

        // Fuzzy (new view results)
        $fuzzy = $this->getRequest()->getParam('fuzzy', '0') == '1' && $this->getRequest()->getParam('action') == 'all' && $paginator->getTotalItemCount() == 0;
        $this->_ac->view->fuzzy = $fuzzy;

        if ($fuzzy) {
            $this->_ac->view->data = $this->_ac->getHelper('Fuzzy')->getMatches($this->escape($this->getRequest()->getParam('key')));
            $this->_ac->view->results = $this->_ac->view->render('search/results/fuzzy.phtml');
        }

        // Render the results layout
        $this->_ac->renderScript('search/results/layout.phtml');
    }

    protected function _formatSearchStringAppendix ($p)
    {
        $output = count($p) > 2 ? '<br>(' : ' (';
        foreach ($p as $rank => $taxon) {
            $formattedRank = (
                $rank == 'kingdom' ? strtolower($this->_ac->view->translate('Top_level_group')) :
                    strtolower($this->_ac->view->translate('RANK_' . strtoupper($rank)))
            );
            $taxon = stripslashes($this->escape($taxon));
            if (in_array($rank, array(
                'genus',
                'subgenus',
                'species',
                'infraspecies'
            ))) {
                $taxon = "<i>$taxon</i>";
            }
            $output .= "$formattedRank: $taxon, ";
        }
        return substr($output, 0, -2) . ')';
    }


    /**
     * Builds the paginator
     *
     * @param Zend_Db_Select $query
     * @param int $page
     * @param int $items
     *
     * @return Zend_Paginator
     */
    protected function _getPaginator(Zend_Db_Select $query, $countQuery, $page,
        $items)
    {
        if ($countQuery instanceof Zend_Db_Select) {
            $paginatorAdapter = new Eti_Paginator_Adapter_DbSelect($query);
            $paginatorAdapter->setRowCount($countQuery);
            $paginator = new Eti_Paginator($paginatorAdapter);
        } else {
            $paginator = new Zend_Paginator(
                new Zend_Paginator_Adapter_DbSelect($query)
            );
        }
        $cache = Zend_Registry::get('cache');
        if ($cache instanceof Zend_Cache_Core) {
            $paginator->setCache($cache);
        }
        $paginator->setItemCountPerPage((int)$items);
        $paginator->setCurrentPageNumber((int)$page);

        return $paginator;
    }

    public function getInfoNavigator($pos = '')
    {
        $selId = 'page_' . $pos;
        $baseUrl = $this->_ac->view->baseUrl() . '/info/';
        $nav = new ACI_Form_Dojo_InfoNavigator();
        $menu = array(
            'about' => $this->_ac->view->translate('Info_about'),
            'special' => sprintf(
                $this->_ac->view->translate('Info_special_edition'),
                $this->_ac->view->app->edition
            ),
            'ac' => sprintf(
                $this->_ac->view->translate('Info_annual_checklist'),
                $this->_ac->view->app->edition
            ),
            'databases' =>
                $this->_ac->view->translate('Source_databases'),
            'hierarchy' =>
                $this->_ac->view->translate('Management_hierarchy'),
            'copyright' =>
                $this->_ac->view->translate(
                    'Copyright_reproduction_sale'
                ),
            'cite' => $this->_ac->view->translate('Cite_work'),
            'websites' => $this->_ac->view->translate('Web_sites'),
            'contact' => $this->_ac->view->translate('Contact_us'),
            'acknowledgements' =>
                $this->_ac->view->translate('Acknowledgments')
        );
        // Totals in info menu only is statistics have been enabled
        if ($this->_moduleEnabled('statistics')) {
            $this->insertIntoArray(
                $menu,
                'databases',
                array('totals' => $this->_ac->view->translate('Species_totals'))
            );
        }
        $nav->getElement('page')->setAttrib('id', $selId)
            ->addMultiOptions($menu)
            ->setValue(array($this->getRequest()->getParam('action')))
            ->onchange =
                'navigateToSelected("' . $baseUrl . '", this, "current")';
        $nav->getElement('next')->setAttrib('id', 'next_' . $pos)
            ->onclick =
                'navigateToSelected("' . $baseUrl .
                '", document.getElementById("' . $selId . '"), "next")';
        ($this->getRequest()->getParam('action') == 'acknowledgements' ?
            $nav->getElement('next')->setAttrib('class', 'hidden') : '');
        $nav->getElement('previous')->setAttrib('id', 'previous_' . $pos)
            ->onclick =
                'navigateToSelected("' . $baseUrl .
                '", document.getElementById("' . $selId . '"), "previous")';
        ($this->getRequest()->getParam('action') == 'about' ?
            $nav->getElement('previous')->setAttrib('class','hidden') : '');
        return '<div class="navigator">' . $nav . '</div>';
    }

    protected function _getItemsPerPage()
    {
        $items = (int)$this->getRequest()->getParam('items', null);
        if (!$items) {
            $items =
                (int)$this->_ac->getHelper('SessionHandler')
                    ->get('items', false);
            if (!$items) {
                $items = ACI_Model_Search::ITEMS_PER_PAGE;
            }
        }
        $this->_ac->getHelper('SessionHandler')->set('items', $items, false);
        $this->getRequest()->setParam('items', $items);
        return $items;
    }

    public function escape($str)
    {
        $config = Zend_Registry::get('config');
        return htmlspecialchars(
            $str, ENT_NOQUOTES, $config->resources->view->encoding, false
        );
    }

    // Insert item into array after specified key
    // $arr2 is key => value of item to insert
    public function insertIntoArray(&$arr1, $key, $arr2) {
        $index = array_search($key, array_keys($arr1));
        if ($index === false) {
            $index = count($arr1); // insert @ end of array if $key not found
        }
        $end = array_splice($arr1, $index++);
        $arr1 = array_merge($arr1, $arr2, $end);
    }

    protected function _moduleEnabled ($module)
    {
        return Bootstrap::instance()->getOption('module.' . $module);
    }
}