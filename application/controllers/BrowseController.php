<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class BrowseController
 * Defines the browse actions
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class BrowseController extends AController
{
    public function treeAction()
    {
        //TODO: add LSIDs
        //TODO: implement automatic deployment for a given taxa id
        $fetch = $this->_getParam('fetch', false);
        if ($fetch !== false) {
            $this->view->layout()->disableLayout();
            exit($this->_getTaxonChildren($this->_getParam('id', 0)));
        }
        $this->view->title = $this->view->translate('Taxonomic_tree');
        $this->view->headTitle($this->view->title, 'APPEND');
        $this->view->dojo()->enable()
            ->requireModule('dojo.parser')
            ->requireModule('dijit.TitlePane')
            ->requireModule('dijit.Tree')
            ->requireModule('dojox.data.QueryReadStore');
        $this->view->headScript()->appendFile(
            $this->view->baseUrl() . '/scripts/library/ACI.dojo.js'
        );
    }
    
    public function classificationAction()
    {
        //TODO: fix fetch action to work for all search fields
        $fetch = $this->_getParam('fetch', false);
        if ($fetch) {
            $this->view->layout()->disableLayout();
            $this->_sendRankData($fetch);
            return;
        }
        $this->view->title = $this->view
            ->translate('Taxonomic_classification');
        $this->view->headTitle($this->view->title, 'APPEND');
        // ComboBox (v1.3.2) custom extension
        $this->view->headScript()->appendFile(
            $this->view->baseUrl() . '/scripts/library/ComboBox.ext.js'
        );
        $this->view->contentClass = 'search-box';
        $this->view->formHeader =
            $this->view->translate('Browse_by_classification');
        // TODO: implement search query
        $form = new ACI_Form_Dojo_BrowseClassification();
        $form->setAction(
            $this->view->baseUrl() . '/' . $this->view->controller . '/' .
            $this->view->action
        );
        $this->view->form = $form;
        $this->renderScript('search/form.phtml');
    }
        
    /**
     * Returns an array with all taxa names by rank on a dojo-suitable format
     * Used to populate the browse by classification search combo boxes
     *
     * @return void
     */
    protected function _sendRankData($rank)
    {
        //TODO: review, may need to be replaced to support all taxons
        $substr = trim(str_replace('*', '', $this->_getParam('name')));
        $this->_logger->debug($substr);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->getRankEntries($rank, $this->_getParam('name'));
        foreach ($res as &$row) {
            $row['label'] = $this->_highlightMatch($row['name'], $substr);
        }
        $this->_logger->debug($res);
        exit(new Zend_Dojo_Data('name', $res, $rank));
    }
    
    /**
     * Returns an array with all the children of a given taxon on a
     * dojo-suitable format
     * Used to populate the taxonomic tree
     *
     * @return void
     */
    protected function _getTaxonChildren($parentId)
    {
        $this->_logger->debug($parentId);
        $search = new ACI_Model_Search($this->_db);
        $res = $search->getTaxonChildren($parentId);
        $this->_logger->debug($res);
        foreach ($res as &$row) {
            $row['url'] =
                $this->view->baseUrl() . '/details/species/id/' . $row['snId'];
        }
        $data = new Zend_Dojo_Data('id', $res, $parentId);
        $data->setLabel('name');
        return $data;
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('tree');
    }
}