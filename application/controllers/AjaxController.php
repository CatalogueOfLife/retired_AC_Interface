<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class AjaxController
 * Defines the Ajax requests for the AC
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class AjaxController extends AController
{
    private $_species; // Name parameter containing species name to be queried
    private $_errors = array();
    private $_remoteData = array();
    private $_jsonChannelResults = array();
    private $_jsonOutput = array(
        'errors' => '', 
        'numberOfResults' => 0, 
        'results' => array()
    );
    
    // Channels queried with queryWebservices should be in multi-dimensional array as below
    // Species name in url will be replaced with sprintf %s, optional key with str_replace [key]
    private $_imageChannels = array(
        array(
            'channel' => 'ARKive', 
            'url' => 'http://www.arkive.org/api/[key]/portlet/latin/%s/1?media=images', 
            'key' => 'ED41047V5D', 
            'link' => ''
        ) /*, 
        array(
            'channel' => 'YahooImages', 
            'url' => 'http://search.yahooapis.com/ImageSearchService/V1/imageSearch?appid=YahooDemo&query=%s&type=phrase&output=php&results=1', 
            'key' => '', 
            'link' => 'http://images.search.yahoo.com/search/images?p=%s'
        )*/
    );
    // Empty container to fill with data, in this case specific for image query
    // Array will be cast to object on access
    private $_emptyImageChannelResult = array(
        'source' => '', 
        'src' => '', 
        'href' => '', 
        'width' => 0, 
        'height' => 0, 
        'photographer' => '', 
        'caption' => ''
    );

    public function init ()
    {
        parent::init();
        $this->_response->setHeader('Content-Type', 'application/json');
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }

    public function indexAction ()
    {
    }

    public function imagesAction ()
    {
        $this->_setSpecies();
        if (!empty($this->_errors)) {
            $this->_response->setBody($this->_createErrorJson());
            return false;
        }
        $this->_remoteData = $this->queryWebservices($this->_imageChannels);
        foreach ($this->_imageChannels as $k => $v) {
            $channel = $v['channel'];
            $method = '_parse' . $channel;
            // Dynamically call the json parsers
            if (method_exists(
                $this, $method) && $parsedChannel = $this->$method()) {
                $this->_jsonChannelResults[] = $parsedChannel;
            }
        }
        $this->_response->setBody($this->_createJsonOutput());
    }

    public function feedbackAction ()
    {
        $config = Zend_Registry::get('config');
        $params = $this->_getAllParams();
        $feedbackUrl = $config->module->feedbackUrl . '?';
        foreach (array(
            'ID', 
            'Comment', 
            'CommentType', 
            'UserName', 
            'UserMail', 
            'TaxonString'
        ) as $v) {
            $feedbackUrl .= $v . '=' . urlencode($params[$v]) . '&';
        }
        $feedbackUrl .= 'COLEdition=' . $config->eti->application->edition;
        $ctx = stream_context_create(
            array(
                'http' => array(
                    'timeout' => 10
                )
            ));
        $result = @file_get_contents($feedbackUrl, 0, $ctx);
        if ($result == '1') {
            echo $this->view->translate('feedback_success');
            exit();
        }
        echo $this->view->translate('feedback_failure');
    }

    public function regionAction ()
    {
        $id = $this->_getParam('region');
        $regionModel = new ACI_Model_Table_Regions($this->_db);
        $region = $regionModel->getRegion($id);
        echo json_encode($region);
    }

    public function regionsAction ()
    {
        $id = $this->_getParam('taxon');
        $rank = $this->_getParam('rank');
        $distributionModel = new ACI_Model_Table_Distributions($this->_db);
        $regionIds = $distributionModel->getRegionsByTaxonId($id, $rank);
        /*    	$regionModel = new ACI_Model_Table_Regions($this->_db);
    	$regions = array(); 
    	foreach($regionIds as $id) {
    		$regions[] = $regionModel->getRegion($id);
    	}
    	die( json_encode($regions));*/
        echo json_encode($regionIds);
    }

    public function regionlistAction ()
    {
        $id = $this->_getParam('regionStandard');
        $regionModel = new ACI_Model_Table_Regions($this->_db);
        $region = $regionModel->getRegions($id);
        echo json_encode($region);
    }

    public function queryWebservices ($channels = array())
    {
        $mh = curl_multi_init();
        for ($i = 0; $i < count($channels); $i++) {
            $ch[$i] = curl_init();
            $url = sprintf($channels[$i]['url'], urlencode($this->_species));
            if (strstr($url, '[key]') !== false) {
                $url = str_replace('[key]', $channels[$i]['key'], $url);
            }
            curl_setopt($ch[$i], CURLOPT_URL, $url);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$i], CURLOPT_HEADER, false);
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, $this->_webserviceTimeout);
            curl_multi_add_handle($mh, $ch[$i]);
        }
        
        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);
        
        // Place results in results array and close handlers
        for ($i = 0; $i < count($channels); $i++) {
            $this->_remoteData[$channels[$i]['channel']] = curl_multi_getcontent($ch[$i]);
            curl_multi_remove_handle($mh, $ch[$i]);
        }
        curl_multi_close($mh);
        return $this->_remoteData;
    }

    // Parsers should be named _parse + $_imageChannels[$i][channel] 
    // so they can be accessed automatically
    private function _parseARKive ()
    {
        $remoteData = $this->_remoteData['ARKive'];
        if (isset($remoteData) && !empty($remoteData)) {
            $data = json_decode($remoteData);
            if (empty($data->error)) {
                $channelResult = (object) $this->_emptyImageChannelResult;
                $image_link = $data->results[0];
                $channelResult->source = 'ARKive';
                $channelResult->href = $this->getAttribute('href', 
                    $image_link);
                $channelResult->src = $this->getAttribute('img src', 
                    $image_link);
                $size = getimagesize($channelResult->src);
                $channelResult->width = $size[0];
                $channelResult->height = $size[1];
                $channelResult->caption = $this->getAttribute('alt', 
                    $image_link);
                return $channelResult;
            }
        }
        return false;
    }

    private function _parseYahooImages ()
    {
        $remoteData = $this->_remoteData['YahooImages'];
        if (isset($remoteData) && !empty($remoteData)) {
            $channelResult = (object) $this->_emptyImageChannelResult;
            $data = unserialize($remoteData);
            if (!empty($data['ResultSet']['Result'])) {
                // Only a single result is required
                $data = $data['ResultSet']['Result'][0];
                $channelResult->source = 'Yahoo Images';
                $href = $this->_imageChannels[$this->_getLinkKey(
                    'YahooImages')]['link'];
                $channelResult->href = sprintf($href, 
                    urlencode($this->_species));
                $channelResult->src = $data['Thumbnail']['Url'];
                $channelResult->width = $data['Thumbnail']['Width'];
                $channelResult->height = $data['Thumbnail']['Height'];
                $channelResult->caption = $data['Summary'];
                return $channelResult;
            }
        }
        return false;
    }

    private function _createErrorJson ()
    {
        $this->_jsonOutput['errors'] = $this->_errors;
        return Zend_Json::encode($this->_jsonOutput);
    }

    private function _createJsonOutput ()
    {
        $this->_jsonOutput['numberOfResults'] = count($this->_jsonChannelResults);
        $this->_jsonOutput['results'] = $this->_jsonChannelResults;
        return Zend_Json::encode($this->_jsonOutput);
    }

    private function _getLinkKey ($source)
    {
        foreach ($this->_imageChannels as $k => $v) {
            if ($source == $v['channel']) {
                return $k;
            }
        }
    }

    private function _setSpecies ()
    {
        $this->_getSpecies();
        $this->_validateSpeciesName();
    }

    private function _getSpecies ()
    {
        if ($this->_getParam('name')) {
            $this->_species = $this->_getParam('name');
        }
        else {
            $this->_errors[] = 'Species name parameter empty or missing';
        }
    }

    private function _validateSpeciesName ()
    {
        $chars_to_skip = array(
            '-', 
            '+', 
            '/', 
            '"', 
            '*'
        );
        foreach ($chars_to_skip as $char) {
            if (strstr($this->_species, $char) !== false) {
                $this->_errors[] = 'Species name contains invalid character(s)';
            }
        }
    }

    public function getAttribute ($attrib, $tag)
    {
        //get attribute from html tag
        $re = '/' . $attrib . '=["\']?([^"\']*)["\']/is';
        preg_match($re, $tag, $match);
        if ($match) {
            return urldecode($match[1]);
        }
        return false;
    }
}