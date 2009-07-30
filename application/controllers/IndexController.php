<?php
/**
 * Controller of the index page
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * Action index
     */
    public function indexAction ()
    {
        $view = $this->view;
        $view->__set('header', 'main page'); // passing variable to your template
        $view->page = 'index';
        $view->render('index.tpl');
    }
}