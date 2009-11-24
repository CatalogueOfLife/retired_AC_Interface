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
                    $field->setValue($this->getRequest()->getParam($el));
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
        $orderParam = $this->getRequest()->getParam(
            'order',
            'asc'
        );
        if (isset($this->_ac->view->searchString)) {
            $this->_ac->view->searchString =
                $this->_ac->view->translate($this->_ac->view->searchString);
        } else {
            $this->_ac->view->searchString = $this->_ac->view->title . ' - ' .
                sprintf(
                $this->_ac->view->translate('Search_results_for'), '"' .
                stripslashes($this->_ac->view->escape(
                    $this->getRequest()->getParam('key')
                )) . '"'
            );
        }
        $this->_ac->view->urlParams = array(
            'sort' => $this->_ac->view->escape($sortParam),
            'order' => $this->_ac->view->escape($orderParam)
        );
        foreach ($elements as $e) {
             $this->_ac->view->urlParams[$e] =
                $this->getRequest()->getParam($e);
        }
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
        
        // Results table differs depending on the action
        $this->_ac->view->results = $this->_ac->view->render(
            'search/results/' .
            $this->getRequest()->getParam('action') .
            '.phtml'
        );
        // Render the results layout
        $this->_ac->renderScript('search/results/layout.phtml');
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
        if($countQuery instanceof Zend_Db_Select) {
            $paginatorAdapter = new Eti_Paginator_Adapter_DbSelect($query);
            $paginatorAdapter->setRowCount($countQuery);
            $paginator = new Eti_Paginator($paginatorAdapter);
        }
        else {
            $paginator = new Zend_Paginator(
                new Zend_Paginator_Adapter_DbSelect($query)
            );
        }
        $cache = Zend_Registry::get('cache');
        if($cache instanceof Zend_Cache_Core) {
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
        $nav->getElement('page')->setAttrib('id', $selId)
            ->addMultiOptions(
                array(
                    'about' => $this->_ac->view->translate('Info_about'),
                    'ac' => sprintf(
                        $this->_ac->view->translate('Info_annual_checklist'),
                        $this->_ac->view->app->edition
                    ),
                    'databases' => $this->_ac->view->translate('Source_databases'),
                    'hierarchy' => $this->_ac->view->translate('Management_hierarchy'),
                    'copyright' => $this->_ac->view->translate('Copyright_reproduction_sale'),
                    'cite' => $this->_ac->view->translate('Cite_work'),
                    'websites' => $this->_ac->view->translate('Web_sites'),
                    'contact' => $this->_ac->view->translate('Contact_us'),
                    'acknowledgements' => $this->_ac->view->translate('Acknowledgements')
                )
            )
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
}