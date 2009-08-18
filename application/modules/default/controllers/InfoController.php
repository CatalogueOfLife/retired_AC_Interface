<?php
class InfoController extends Zend_Controller_Action
{
    protected $_logger;
    
    public function init ()
    {
        $this->_logger = Zend_Registry::get('logger');
        $this->view->controller = $this->getRequest()->controller;
        $this->view->action = $this->getRequest()->action;
    }
    
    public function aboutAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Info_about');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function acAction ()
    {
        $this->view->title = sprintf($this->view->t
            ->translate('Info_annual_checklist'), $this->view->app->version);
        $this->view->headTitle($this->view->title, 'APPEND');

    }
    
    public function databasesAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Source_databases');
        $this->view->headTitle($this->view->title, 'APPEND');

    }
    
    public function hierarchyAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Management_hierarchy');
        $this->view->headTitle($this->view->title, 'APPEND');

    }
    
    public function copyrightAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Copyright_reproduction_sale');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function citeAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Cite_work');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function websitesAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Web_sites');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function contactAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Contact_us');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function acknowledgementsAction ()
    {
        $this->view->title = $this->view->t
            ->translate('Acknowledgements');
        $this->view->headTitle($this->view->title, 'APPEND');
    }
    
    public function __call ($name, $arguments)
    {
        $this->_forward('about');
    }

}