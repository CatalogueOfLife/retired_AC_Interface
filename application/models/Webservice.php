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
    
    protected $_responseLimits = array('terse' => 500, 'full' => 50);
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
        if (is_null($this->_filter)) {
            throw new ACI_Model_Exception(
                'No filter defined for the webservice output'
            );
        }
        try {
            $this->_process($this->_validate($request));
        } catch(Zend_Db_Exception $e) {
            $this->_setError('Database query failed');
        } catch (ACI_Model_Webservice_Exception $e) {
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
            'response', current(array_keys($this->_responseLimits))
        );
         
        // providing with *either* id or name params is required
        if ($this->_response['id'] == '' && $this->_response['name'] == '') {
            throw new ACI_Model_Webservice_Exception('No name or ID given');
        }
        if ($this->_response['id'] && $this->_response['name']) {
            throw new ACI_Model_Webservice_Exception(
                'Both name and ID are given. Give either a name or an ID'
            );
        }
        // if the parameter name is given, it must be at least 3 characters
        // long, excluding wildcards (*, %)
        $tooShortName = strlen(str_replace('*', '', $this->_response['name']))
            < self::REQUEST_NAME_MIN_STRLEN;
        if ($this->_response['name'] && $tooShortName) {
            throw new ACI_Model_Webservice_Exception(
                'Invalid name given. The name given must consist of at least ' .
                self::REQUEST_NAME_MIN_STRLEN . ' characters, not counting ' .
                'wildcards (*)'
            );
        }
        // id must be a valid positive integer
        $positiveIntId = Zend_Validate::is($this->_response['id'], 'Digits') &&
            $this->_response['id'] >= 0;
        if ($this->_response['id'] != '' && !$positiveIntId) {
            throw new ACI_Model_Webservice_Exception(
                'Invalid ID given. The ID must be a positive integer'
            );
        }
        // response param (if set) must be one of the keys of the defined
        // $this->_responseLimits
        if (!array_key_exists($responseFormat, $this->_responseLimits)) {
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
            $this->_responseLimits[$request->getParam('response')], // LIMIT
            $request->getParam('start') // OFFSET
        );
        $numRows = count($res);
        if ($numRows == 0) {
            throw new ACI_Model_Webservice_Exception('No names found');
        }
        $this->_response['number_of_results_returned'] = $numRows;
        $this->_response['total_number_of_results'] = $wsSearch->getFoundRows();
        $names = $this->_processResults(
            $res, $request->getParam('response') == 'full' ? true : false
        );
        $this->_response['names'] = $names;
    }
    
    protected function _processResults(array $res, /*bool*/$full)
    {
        $results = array();
        foreach($res as $row) {
            switch($row['status']) {
                case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                    $item = $this->_processCommonName($row, $full);
                break;
                default:
                    $item = $this->_processScientificName($row, $full);
                break;
            }
            $results[] = $item;
        }
        return $results;
    }
    
    protected function _processCommonName(array $row, /*bool*/$full)
    {
        $item = array(
            'name' => $row['name'],
            'name_status' => $this->_getNameStatusById($row['status']),
            'language' => $row['language'],
            'country' => $row['country'],
            'url' => self::getTaxaUrl(
                $row['record_id'], $row['rank_id'], $row['status'],
                $row['sn_id']
            ),
            'source_database' => $row['source_database'],
            'source_database_url' => $row['source_database_url'],
            'accepted_name' => $this->_getAcceptedName($row['name_code'], $full)
        );
        
        if (!$full) {
            return $item;
        }
        
        // full response =+ references
        $item['references'] =
            $this->_getReferences(array($row['reference_id']));
        
        return $item;
    }
    
    protected function _processScientificName(array $row, /*bool*/$full)
    {
        if ($row['rank_id'] < ACI_Model_Table_Taxa::RANK_SPECIES) {
            return array(
                'id' => $row['record_id'],
                'name' => $row['name'],
                'rank' => $row['rank'],
                'name_status' => $this->_getNameStatusById($row['status']),
                'name_html' => $row['name_html'],
                'url' => self::getTaxaUrl(
                    $row['record_id'], $row['rank_id'], $row['status']
                )
            );
            // TODO: implement full response for higher taxa
        }
        // Species and infraspecies
        return $this->_getAcceptedName($row['name_code'], $full);
    }
    
    protected function _getAcceptedName($nameCode, /*bool*/$full)
    {
        $wsSearch = new ACI_Model_WebserviceSearch($this->_db);
        $an = $wsSearch->acceptedScientificName($nameCode);
        if (!$an) {
            return array();
        }
        $an['name_html'] =
            ACI_Model_Table_Taxa::getAcceptedScientificName(
                $an['genus'], $an['species'], $an['infraspecies'],
                $an['infraspecies_marker'], $an['author']
            );
        $an['rank'] = $this->_getRankNameById($an['rank_id']);
        $an['name_status'] = $this->_getNameStatusById($an['status']);
        $an['url'] = self::getTaxaUrl(
            $an['id'], $an['rank_id'], $an['status'], $an['id']
        );
        
        unset($an['rank_id'], $an['status']);
        
        if (!$full) {
            $this->_arrayFilterKeys(
                $an, array('id', 'name', 'rank', 'name_status', 'name_html',
                'url', 'source_database', 'source_database_url',
                'online_resource')
            );
            return $an;
        }
        // full response
        $an['distribution'] = $this->_getDistribution($an['name_code']);
        $an['references'] = $this->_getReferences($an['name_code']);
        $an['classification'] = $this->_getClassification($an['id']);
        $an['child_taxa'] = $this->_getChildren($an['id']);
        $an['synonyms'] = $this->_getSynonyms($an['id']);
        $an['common_names'] = $this->_getCommonNames($an['id']);
        
        return $an;
    }
    
    protected function _getReferences(/*mixed*/$rCode)
    {
        $dm = new ACI_Model_Details($this->_db);
        
        if(is_array($rCode)) {
            $refs = array();
            foreach ($rCode as $refId) {
                $ref = $dm->getReferenceById($refId);
                if ($ref) {
                    $refs[] = $ref;
                }
            }
        } else {
            $refs = $dm->getReferencesByNameCode($rCode);
        }
        
        $this->_arrayFilterKeys(
            $refs, array('author', 'title', 'year', 'source')
        );
        return $refs;
    }
    
    protected function _getDistribution($nameCode)
    {
        $dm = new ACI_Model_Details($this->_db);
        $distributions = $dm->distributions($nameCode);
        return implode('; ', $distributions);
    }
    
    protected function _getClassification($snId)
    {
        $wsSearch = new ACI_Model_WebserviceSearch($this->_db);
        return $wsSearch->classification($snId);
    }
    
    protected function _getChildren()
    {
        // TODO: implement
        return array();
    }
    
    protected function _getSynonyms()
    {
        // TODO: implement
        return array();
    }
    
    protected function _getCommonNames()
    {
        // TODO: implement
        return array();
    }
    
    protected function _arrayFilterKeys(array &$array, array $whitelist)
    {
        foreach ($array as $k => &$v) {
            if(is_array($v)) {
                $this->_arrayFilterKeys($v, $whitelist);
            } else if (!in_array($k, $whitelist)) {
                unset($array[$k]);
            }
        }
    }
    
    public static function getTaxaUrl($taxaId, $rankId, $statusId, $snId = null)
    {
        $config = Zend_Registry::get('config');
        $url = $config->eti->application->location . '/';
        if ($statusId == ACI_Model_Table_Taxa::STATUS_COMMON_NAME) {
            $url .= 'details/species/id/' . $snId . '/common/' . $taxaId;
        } else {
            // species or infraspecies
            if ($rankId >= ACI_Model_Table_Taxa::RANK_SPECIES) {
                $url .= 'details/species/id/' . $taxaId;
            }
            // higher taxa
            else {
                $url .= 'browse/tree/id/' . $taxaId;
            }
        }
        return $url;
    }
    
    protected function _getNameStatusById($id)
    {
        switch ($id) {
            case ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME:
                return 'accepted name';
            case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                return 'common name';
            case ACI_Model_Table_Taxa::STATUS_SYNONYM:
                return 'synonym';
            case ACI_Model_Table_Taxa::STATUS_AMBIGUOUS_SYNONYM:
                return 'ambiguous synonym';
            case ACI_Model_Table_Taxa::STATUS_MISAPPLIED_NAME:
                return 'misapplied name';
            case ACI_Model_Table_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME:
                return 'provisionally accepted name';
        }
        return 'unknown';
    }
    
    protected function _getRankNameById($id)
    {
        switch ($id) {
            case ACI_Model_Table_Taxa::RANK_SPECIES:
                return 'Species';
            case ACI_Model_Table_Taxa::RANK_INFRASPECIES:
                return 'Infraspecies';
            case ACI_Model_Table_Taxa::RANK_KINGDOM:
                return 'Kingdom';
            case ACI_Model_Table_Taxa::RANK_PHYLUM:
                return 'Phylum';
            case ACI_Model_Table_Taxa::RANK_CLASS:
                return 'Class';
            case ACI_Model_Table_Taxa::RANK_ORDER:
                return 'Order';
            case ACI_Model_Table_Taxa::RANK_SUPERFAMILY:
                return 'Superfamily';
            case ACI_Model_Table_Taxa::RANK_FAMILY:
                return 'Family';
            case ACI_Model_Table_Taxa::RANK_GENUS:
                return 'Genus';
        }
        return 'unknown';
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