<?php

class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
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
                // application error
                $this->view->layout()->disableLayout();
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                $this->view->exception = $errors->exception;
                $this->view->request   = $errors->request;
                break;
        }
    }


}

