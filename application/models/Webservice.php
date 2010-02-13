<?php
require_once 'AModel.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Webservice
 * Search query for the webservices
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Webservice extends AModel
{
    const REQUEST_NAME_MIN_STRLEN = 3;
    protected $_responseFormats = array('terse' => 500, 'full' => 50);
    protected $_filter;
    protected $_response = array(
        'name' => '',
        'id' => '',
        'number_of_results_returned' => 0,
        'total_number_of_results' => 0,
        'start' => 0,
        'error_message' => '',
        'version' => '1.0'
    );
    
    public function query(Zend_Controller_Request_Abstract $request)
    {
        if(is_null($this->_filter)) {
            throw new ACI_Model_Exception(
                'No filter defined for the webservice output'
            );
        }
        try {            
            $this->_process($this->_validate($request));
        }
        catch (ACI_Model_Webservice_Exception $e) {
            $this->_setError($e->getMessage());
        }
        return $this->_filter->filter($this->_response);
    }
    
    /**
     * Validates the request parameters
     *
     * @param Zend_Controller_Request_Abstract $request
     * @throws ACI_Model_Webservice_Exception
     * @return Zend_Controller_Request_Abstract
     */
    protected function _validate(Zend_Controller_Request_Abstract $request)
    {
        $this->_response['id'] = $request->getParam('id', '');
        $this->_response['name'] = 
            str_replace('*', '%' , (string)$request->getParam('name', ''));
        $responseFormat = $request->getParam(
            'response', current(array_keys($this->_responseFormats))
        );
            
        $this->_logger->debug($this->_response);   
         
        // providing with *either* id or name params is required
        if(empty($this->_response['id']) && empty($this->_response['name'])) {
            throw new ACI_Model_Webservice_Exception('No name or ID given');
        }
        if($this->_response['id'] && $this->_response['name']) {
            throw new ACI_Model_Webservice_Exception(
                'Both name and ID are given. Give either a name or an ID'
            );
        }
        // if the parameter name is given, it must be at least 3 characters
        // long, excluding wildcards (*, %)
        $tooShortName = strlen(str_replace('%', '', $this->_response['name'])) 
            < self::REQUEST_NAME_MIN_STRLEN;
        if($this->_response['name'] && $tooShortName) {
            throw new ACI_Model_Webservice_Exception(
                'Invalid name given. The name given must consist of at least ' . 
                self::REQUEST_NAME_MIN_STRLEN . ' characters, not counting ' .
                'wildcards (*)'
            );
        }
        // id must be a valid positive integer
        $positiveIntId = Zend_Validate::is($this->_response['id'], 'Int') &&
            $this->_response['id'] >= 0;        
        if($this->_response['id'] != '' && !$positiveIntId) {
            throw new ACI_Model_Webservice_Exception(
                'Invalid ID given. The ID must be a positive integer'
            );
        }
        // response param (if set) must be one of the keys of the defined 
        // $this->_responseFormats
        if(!array_key_exists($responseFormat, $this->_responseFormats)) {
            throw new ACI_Model_Webservice_Exception(
                'Unknown response format: ' . $responseFormat
            );
        }
        
        // reset validated params
        $request->setParam('id', $this->_response['id']);
        $request->setParam('name', $this->_response['name']);
        $request->setParam('response', $responseFormat);
        
        return $request;
    }
    
    protected function _process(Zend_Controller_Request_Abstract $request)
    {
        
    }
    
    public function setFilter(Zend_Filter_Interface $filter)
    {
        $this->_filter = $filter;
    }
    
    protected function _setError($message)
    {
        $this->_response['error_message'] = $message;
    }
}