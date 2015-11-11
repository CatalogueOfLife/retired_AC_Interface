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

    // allowed parameters
    protected static $_params = array(
        'id', 'name', 'start', 'response', 'format', 'rank'
    );
    protected $_responseLimits = array('terse' => 500, 'full' => 50);
    protected $_filter;
    protected $_naturalKey;
    protected $_response = array(
        'id' => '',
        'name' => '',
        'total_number_of_results' => 0,
        'number_of_results_returned' => 0,
        'start' => 0,
        'error_message' => '',
        'version' => ''
    );
    protected $_model;
    protected $_detailsModel;

    public static $classificationRanks = array(
        'kingdom',
        'phylum',
        'class',
        'order',
        'superfamily',
        'family',
        'genus',
        'subgenus',
        'species',
   		'infraspecies'
    );

    public function query(Zend_Controller_Request_Abstract $request)
    {
        if (is_null($this->_filter)) {
            throw new ACI_Model_Exception(
                'No filter defined for the webservice output'
            );
        }
        $this->_model = new ACI_Model_WebserviceSearch($this->_db);
        try {
            $this->_process($this->_validate($request));
        } catch(Zend_Db_Exception $e) {
            $this->_setError('Database query failed: '.$e);
        } catch (ACI_Model_Webservice_Exception $e) {
            $this->_setError($e->getMessage());
        }
        return $this->_filter->filter($this->_response);
    }

    protected function _getDetailsModel()
    {
        if(is_null($this->_detailsModel)) {
            $this->_detailsModel = new ACI_Model_Details($this->_db);
        }
        return $this->_detailsModel;
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
        $this->_naturalKey = $request->getParam('id', '');
        $this->_response['id'] = $this->naturalKeyToId($this->_naturalKey);
        $this->_response['name'] =
            str_replace('%', '*' , (string)$request->getParam('name', ''));
        // Ruud 15-09-12: wildcards are limited to the end of the name only
        if (substr_count($this->_response['name'], '*') > 1 ||
        	strpos($this->_response['name'], '*') !== false &&
         	strpos($this->_response['name'], '*') !== strlen($this->_response['name']) - 1) {
        	throw new ACI_Model_Webservice_Exception('Wildcards are allowed only at the end of the name ("name*").');
        }
        $this->_response['start'] = (int)$request->getParam('start');
        $this->_response['rank'] = strtolower($request->getParam('rank'));
        $this->_response['version'] = $this->_setVersion();

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
                self::REQUEST_NAME_MIN_STRLEN . ' characters'
            );
        }
        // id must be a valid positive integer
        /*
        $positiveIntId = Zend_Validate::is($this->_response['id'], 'Digits') &&
            $this->_response['id'] >= 0;
        if ($this->_response['id'] != '' && !$positiveIntId) {
            throw new ACI_Model_Webservice_Exception(
                'Invalid ID given. The ID must be a positive integer'
            );
        }*/

        // length of natural key must be 32 characters
        $positiveIntId = Zend_Validate::is($this->_naturalKey, 'Digits') &&
            $this->_naturalKey >= 0;
        if ($this->_naturalKey != '' && strlen($this->_naturalKey) != 32 && !$positiveIntId) {
            throw new ACI_Model_Webservice_Exception(
                'Invalid ID given. The ID must be a positive integer or a 32-character string'
            );
        }

        // response param (if set) must be one of the keys of the defined
        // $this->_responseLimits
        if (!array_key_exists($responseFormat, $this->_responseLimits)) {
            throw new ACI_Model_Webservice_Exception(
                'Unknown response format: ' . $responseFormat
            );
        }
        // Ruud 12-10-15: rank must be in predefined array
        if (!empty($this->_response['rank']) &&
            !in_array($this->_response['rank'], self::$classificationRanks)) {
        	throw new ACI_Model_Webservice_Exception('Invalid rank given');
        }
        // Ruud 12-10-15: rank can only be used in combination with name search
        if (!empty($this->_response['rank']) && !empty($this->_response['id'])) {
        	throw new ACI_Model_Webservice_Exception('ID and rank are given. ' .
        	   'Rank can only be used in combination with name search.');
        }

        // reset validated params
        $request->setParam('id', $this->_response['id']);
        $request->setParam('name', $this->_response['name']);
        $request->setParam('response', $responseFormat);
        $request->setParam('start', $this->_response['start']);
        $request->setParam('rank', $this->_response['rank']);

        return $request;
    }

    public static function paramsExist(array $requestParams)
    {
        $intersect = array_intersect(
            self::$_params, array_keys($requestParams)
        );
        return !empty($intersect);
    }

    protected function _process(Zend_Controller_Request_Abstract $request)
    {
        $res = $this->_model->taxa(
            $request->getParam('id'),
            $request->getParam('name'),
            $request->getParam('rank'),
            $this->_responseLimits[$request->getParam('response')], // LIMIT
            $request->getParam('start') // OFFSET
        );
        $numRows = count($res);
        if ($numRows == 0) {
            throw new ACI_Model_Webservice_Exception('No names found');
        }
        $this->_response['number_of_results_returned'] = $numRows;
        $this->_response['total_number_of_results'] =
            $this->_model->getFoundRows();
        $names = $this->_processResults(
            $res, $request->getParam('response') == 'full' ? true : false
        );
        $this->_response['results'] = $names;
        // Ruud 20-05-14: set id back to natural key if this has been searched for
        if (!empty($this->_naturalKey)) {
            $this->_response['id'] = $this->_naturalKey;
        }
    }

    protected function _processResults(array $res, /*bool*/$full)
    {
        $results = array();
        foreach ($res as $row) {
            switch($row['status']) {
                case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                    $item = $this->_processCommonName($row, $full);
                break;
                default:
                    $item = $this->_processScientificName($row, $full);
                break;
            }
            $results[] = $this->_setNaturalKeys($item);
        }
        return $results;
    }

    private function _setNaturalKeys ($d)
    {
        foreach ($d as $k => $v) {
            if (is_array($v)) {
                $d[$k] = $this->_setNaturalKeys($v);
            } else if ($k == 'id') {
                $d[$k] = $this->idToNaturalKey($v);
            }
        }
        return $d;
   }

    protected function _processCommonName(array $row, /*bool*/$full)
    {
        $languageAndCountry = $this->_model->getLanguageAndCountry($row['record_id']);
        $sourceDatabase = $this->_getSourceDatabase($row['source_database_id']);
        $item = array(
            'name' => $row['name'],
            'name_status' => $this->_getNameStatusById($row['status']),
            'language' => $languageAndCountry['language'],
            'country' => $languageAndCountry['country'],
            'url' => self::getTaxaUrl(
                $row['record_id'], $row['rank_id'], $row['status'],
                $row['sn_id']
            ),
            'source_database' => $sourceDatabase['full_name'],
            'source_database_url' => $sourceDatabase['web_site'],
            'accepted_name' => $this->_getScientificName(
                $row['sn_id'], $full, true
            )
        );

        if (!$full) {
            return $item;
        }

        // full response =+ references
        $item['references'] =
            $this->_getReferences($row['record_id']);

        return $item;
    }

    protected function _processScientificName(array $row, /*bool*/$full)
    {
    	// Higher taxon
        if (ACI_Model_Table_Taxa::isRankHigherThanSpecies($row['rank_id'])) {
            $sn = array(
                'id' => $row['record_id'],
                'name' => $row['name'],
                'rank' => $this->checkRank($row['rank']),
                'name_status' => $this->_getNameStatusById($row['status']),
                'name_html' => $row['name_html'],
                'url' => self::getTaxaUrl(
                    $row['record_id'], $row['rank_id'], $row['status']
                ),
                'is_extinct' => $row['is_extinct'] == 0 ? 'false' : 'true'
            );
            if($full) {
                $sn['classification'] =
                    $this->_model->getHigherTaxonClassification($row['record_id']);
                $sn['child_taxa'] =
                    $this->_model->getChildTaxa($row['record_id']);
            }
            return $sn;
        }
        // Species and infraspecies
        $sn = $this->_getScientificName($row['record_id'], $full, false);
        if (!ACI_Model_Table_Taxa::isAcceptedName($row['status'])) {
            unset($sn['is_extinct']);
            $sn['accepted_name'] = $this->_getScientificName(
                $row['sn_id'], $full, true
            );
        }
        return $sn;
    }

    protected function _getScientificName($id, /*bool*/ $full,
        /*bool*/ $acceptedName)
    {
        $an = $this->_model->scientificName($id, $acceptedName);
        if (!$an) {
            return array();
        }
        $db = $this->_getSourceDatabase($an['source_database_id']);
        $an['source_database'] = $db['full_name'];
        $an['source_database_url'] = $db['web_site'];
        $an['bibliographic_citation'] = $this->_setCredit($db);
        $an['online_resource'] = $this->_getOnlineResource($an['id']);
        $an['record_scrutiny_date'] = $this->_model->getScrutinyDate($an['id']);
        $an['name_html'] =
            ACI_Model_Table_Taxa::getAcceptedScientificName(
                $an['genus'],
                isset($an['subgenus']) ? $an['subgenus'] : null, // @TODO subgenus
                $an['species'],
                $an['infraspecies'],
                $an['infraspecies_marker'],
                $an['author']
            );
        $an['rank'] = $this->_getRankNameById($an['rank_id']);
        $an['name_status'] = $this->_getNameStatusById($an['status']);
        $an['url'] = self::getTaxaUrl(
            $an['id'], $an['rank_id'], $an['status'], $an['sn_id']
        );

        $an['is_extinct'] = $an['is_extinct'] == 0 ? 'false' : 'true';
        $status = $an['status'];
        unset($an['rank_id'], $an['status'], $an['source_database_id'], $an['sn_id']);

        if (!$full) {
            $this->_arrayFilterKeys(
                $an, array('id', 'name', 'rank', 'name_status', 'name_html',
                'url', 'source_database', 'source_database_url',
                'online_resource', 'is_extinct', 'bibliographic_citation')
            );
            return $an;
        }
        // full response
        $an['distribution'] = $this->_getDistribution($an['id']);
        $an['references'] = $this->_getReferences($an['id']);
        if(ACI_Model_Table_Taxa::isAcceptedName($status)) {
            $an['classification'] = $this->_model->getSpeciesClassification($an['id']);
            $an['child_taxa'] = $this->_model->getChildTaxa($an['id']);
            $an['synonyms'] = $this->_getSynonyms($an['id']);
            $an['common_names'] = $this->_getCommonNames($an['id']);
        }
        return $an;
    }

    protected function _getReferences(/*mixed*/ $rCode)
    {
        $dm = $this->_getDetailsModel();

        if (is_array($rCode)) {
            $refs = array();
            foreach ($rCode as $refId) {
                $ref = $dm->getReferenceById($refId);
                if ($ref) {
                    $refs[] = $ref;
                }
            }
        } else {
            $refs = $dm->getReferencesByTaxonId($rCode);
        }
        // Reset to 'old' array keys
        $refs = $this->_resetReferences($refs);
        $this->_arrayFilterKeys(
            $refs, array('author', 'title', 'year', 'source')
        );
        return $refs;
    }

    protected function _getDistribution($id)
    {
        $dm = $this->_getDetailsModel();
        $distributions = $dm->distributions($id);
        if(!is_array($distributions)) {
        	return (string) $distributions;
        }
        $result = '';
        foreach($distributions as $i => $d) {
        	if($i !== 0) {
        		$result .= '; ';
        	}
        	if(is_array($d)) {
        		$result .= implode(' ', $d);
        	}
        	else {
        		$result .= $d;
           	}
        }
        return $result;
    }

    protected function _getTaxaFromSpeciesId($snId)
    {
        $searchModel = new ACI_Model_Search($this->_db);
        return $searchModel->getTaxaFromSpeciesId($snId);
    }

    protected function _getSynonyms($id)
    {
        $synonyms = $this->_model->synonyms($id);
        foreach ($synonyms as &$syn) {
            $syn['name_html'] =
                ACI_Model_Table_Taxa::getAcceptedScientificName(
                    $syn['genus'],
                    isset($syn['subgenus']) ? $syn['subgenus'] : null,
                    $syn['species'],
                    $syn['infraspecies'],
                    $syn['infraspecies_marker'],
                    $syn['author']
                );
            $syn['rank'] = $this->_getRankNameById($syn['rank_id']);
            $syn['name_status'] = $this->_getNameStatusById($syn['status']);
            $syn['url'] = self::getTaxaUrl(
                $syn['id'], $syn['rank_id'], $syn['status'], $id
            );
            $syn['references'] = $this->_getReferences($syn['id']);
            unset($syn['rank_id'], $syn['status'], $syn['source_database_id'],
                $syn['distribution'], $syn['sn_id']);
        }
        return $synonyms;
    }

    protected function _getCommonNames($id)
    {
        $dm = $this->_getDetailsModel();
        $commonNames = $dm->commonNames($id);
        foreach($commonNames as &$cn) {
            $refIds = explode(',', $cn['references']);
            $refs = array();
            foreach($refIds as $refId) {
                // some references do not exist (inconsistent data)
                // check first if we can really obtain that reference
                if($ref = $dm->getReferenceById($refId)) {
                    $refs[] = $ref;
                }
            }
            // Reset to 'old' array keys
            $refs = $this->_resetReferences($refs);
            $this->_arrayFilterKeys(
                $refs, array('author', 'title', 'year', 'source')
            );
            $cn = array(
                'name' => $cn['common_name'],
                'language' => $cn['language'],
                'country' => $cn['country'],
                'references' => $refs
            );
        }
        return $commonNames;
    }

    protected function _arrayFilterKeys(array &$array, array $whitelist)
    {
        foreach ($array as $k => &$v) {
            if (is_array($v)) {
                $this->_arrayFilterKeys($v, $whitelist);
            } else if (!in_array($k, $whitelist)) {
                unset($array[$k]);
            }
        }
    }

    public static function getTaxaUrl($taxaId, $rankId, $statusId, $snId = null)
    {
        $ws = new ACI_Model_Webservice(Zend_Registry::get('db'));
        $config = Zend_Registry::get('config');
        $url = $config->eti->application->location . '/';
        if ($statusId == ACI_Model_Table_Taxa::STATUS_COMMON_NAME) {
            $url .= 'details/species/id/' . $ws->idToNaturalKey($snId) . '/common/' .
                $ws->idToNaturalKey($taxaId);
        } else if (!in_array($statusId,
            array(
                ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                ACI_Model_Table_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME
            ))) {
            $url .= 'details/species/id/' . $ws->idToNaturalKey($snId) . '/synonym/' .
                $ws->idToNaturalKey($taxaId);
        } else {
            // species or infraspecies
            if ($rankId >= ACI_Model_Table_Taxa::RANK_SPECIES) {
                $url .= 'details/species/id/' . $ws->idToNaturalKey($taxaId);
            } else  { // higher taxa
                $url .= 'browse/tree/id/' . $ws->idToNaturalKey($taxaId);
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
            case ACI_Model_Table_Taxa::RANK_SUBGENUS:
                return 'Subgenus';
        }
        return 'unknown';
    }

    protected function _getSourceDatabase($id) {
       $db = new ACI_Model_Table_Databases($this->_db);
       return $db->get($id);
    }

    protected function _getOnlineResource($id) {
       $resources = array();
       $details = new ACI_Model_Details($this->_db);
       $urls = $details->getUrls($id);
       foreach ($urls as $url) {
           $resources[] = $url['url'];
       };
       return implode('; ', $resources);
    }

    // Reset array keys to the original values
    private function _resetReferences($refs) {
        $parsedRefs = array();
        foreach ($refs as $ref) {
            $parsedRefs[] = array(
                'author' => $ref['authors'],
                'year' => $ref['year'],
                'title' => $ref['title'],
                'source' => $ref['text']
            );
        }
        return $parsedRefs;
    }

    // Simplify rank to infraspecies if necessary and set first character to uppercase
    public static function checkRank($rank) {
        if (!in_array($rank, self::$classificationRanks)) {
            $rank = 'infraspecies';
        }
        return ucfirst($rank);
    }

    public function setFilter(Zend_Filter_Interface $filter)
    {
        $this->_filter = $filter;
    }

    protected function _setError($message)
    {
        $this->_response['error_message'] = $message;
        $this->_response['id'] = $this->_naturalKey;
    }
}