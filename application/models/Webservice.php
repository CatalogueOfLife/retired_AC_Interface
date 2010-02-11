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
            $this->_response['id'] = is_null($request->getParam('id')) ? 
                '' : (int)$request->getParam('id');
            $this->_response['name'] = (string)$request->getParam('name');
            // providing with either id or name params is required
            if(!($this->_response['id'] || $this->_response['name'])) {
                throw new ACI_Model_Webservice_Exception('No name or ID given');
            }
        }
        catch (ACI_Model_Webservice_Exception $e) {
            $this->_setError($e->getMessage());
        }
        return $this->_filter->filter($this->_response);
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