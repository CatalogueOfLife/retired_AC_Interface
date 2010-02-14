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
        catch(Zend_Db_Exception $e) {
            $this->_setError('Database query failed');
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
            str_replace('%', '*' , (string)$request->getParam('name', ''));
        $this->_response['start'] = (int)$request->getParam('start');
        
        $responseFormat = $request->getParam(
            'response', current(array_keys($this->_responseFormats))
        );
            
        $this->_logger->debug($this->_response);   
         
        // providing with *either* id or name params is required
        if($this->_response['id'] == '' && $this->_response['name'] == '') {
            throw new ACI_Model_Webservice_Exception('No name or ID given');
        }
        if($this->_response['id'] && $this->_response['name']) {
            throw new ACI_Model_Webservice_Exception(
                'Both name and ID are given. Give either a name or an ID'
            );
        }
        // if the parameter name is given, it must be at least 3 characters
        // long, excluding wildcards (*, %)
        $tooShortName = strlen(str_replace('*', '', $this->_response['name'])) 
            < self::REQUEST_NAME_MIN_STRLEN;
        if($this->_response['name'] && $tooShortName) {
            throw new ACI_Model_Webservice_Exception(
                'Invalid name given. The name given must consist of at least ' . 
                self::REQUEST_NAME_MIN_STRLEN . ' characters, not counting ' .
                'wildcards (*)'
            );
        }
        // id must be a valid positive integer
        $positiveIntId = Zend_Validate::is($this->_response['id'], 'Digits') && 
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
        $request->setParam('start', $this->_response['start']);
        
        return $request;
    }
    
    protected function _process(Zend_Controller_Request_Abstract $request)
    {
        $wsSearch = new ACI_Model_WebserviceSearch($this->_db);        
        $res = $wsSearch->taxa(
            $request->getParam('id'), 
            $request->getParam('name'),
            $request->getParam('start'),
            $this->_responseFormats[$request->getParam('response')]
        );        
        $numRows = count($res);
        if($numRows == 0) {
            throw new ACI_Model_Webservice_Exception('No names found');
        }
        $this->_response['number_of_results_returned'] = $numRows;
        $this->_response['total_number_of_results'] = $wsSearch->getFoundRows();        
        $this->_response['names'] = $this->_processResults($res);
    }
    
    protected function _processResults(array $res)
    {  
        $results = array();
        foreach($res as $row) {
            $item = array(
                'id' => $row['record_id'],                
                'name' => $row['name'],
                'name_html' => $row['name_html'],
                'name_status' => 
                    ACI_Model_Table_Taxa::getStatusString($row['status']),
                'rank' => $row['rank'],
                'url' => $this->_getTaxaUrl(
                    $row['record_id'], $row['sn_id'], 
                    $row['rank_id'], $row['status']
                )
            );
            $results[] = $item;
        }
        return $results;
    }
    
    protected function _getTaxaUrl($taxaId, $snId, $rankId, $statusId)
    {
        $config = Zend_Registry::get('config');
        $url = $config->eti->application->location . '/'; 
        if($statusId == ACI_Model_Table_Taxa::STATUS_COMMON_NAME) {
            $url .= 'details/species/id/' . $snId . '/common/' . $taxaId;
        } else {
            // species or infraspecies
            if($rankId >= ACI_Model_Table_Taxa::RANK_SPECIES) {
                $url .= 'details/species/id/' . $taxaId;
            }
            // higher taxa
            else {
                $url .= 'browse/tree/id/' . $taxaId;
            }
        }
        return $url;
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