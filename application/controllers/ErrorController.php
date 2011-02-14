<?php
/**
 * Annual Checklist Interface
 *
 * Class ErrorController
 * Handles the errors of the application
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler', false);
        
        // If there's no error (for example, if accessing directly the page),
        // redirect to the application root url
        if (!$errors) {
            $this->_redirect($this->view->baseUrl);
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // On controller/action not found, redirect to the default page
                $front = Zend_Controller_Front::getInstance();
                $this->_redirect(
                    $front->getDefaultControllerName() . '/' .
                    $front->getDefaultAction()
                );
                break;
            default:
                $logger = Zend_Registry::get('logger');
                $logger->log($errors->exception, Zend_Log::CRIT);
                // application error
                'development' == APPLICATION_ENV ?
                    $this->view->layout()->disableLayout() : '';
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->exception = $errors->exception;
                $this->view->request   = $errors->request;
                break;
        }
    }
    
    public function __call($name, $arguments)
    {
        $this->_forward('all', 'search');
    }
}