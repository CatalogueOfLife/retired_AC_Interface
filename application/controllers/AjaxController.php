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
    const TIMEOUT = 5;
    private $_species; // Name parameter containing species name to be queried
    private $_errors = array();
    private $_remoteData = array();
    private $_emptyChannelResult = array(
        'source' => '', 
        'src' => '', 
        'href' => '', 
        'width' => 0, 
        'height' => 0, 
        'photographer' => '', 
        'caption' => ''
    );
    private $_jsonChannelResults = array();
    private $_json = array(
        'errors' => '', 
        'numberOfChannels' => 0, 
        'results' => array()
    );
    
    // Channels queried with queryWebservices should be in multi-dimensional array as below
    // Species name in url with be replaced with sprintf %s, optional key with str_replace [key]
    private $_imageChannels = array(
        array(
            'channel' => 'ARKive', 
            'url' => 'http://www.arkive.org/api/[key]/portlet/latin/%s/1?media=images', 
            'key' => 'ED41047V5D', 
            'link' => ''
        ), 
        array(
            'channel' => 'YahooImages', 
            'url' => 'http://search.yahooapis.com/ImageSearchService/V1/imageSearch?appid=YahooDemo&query=%s&type=phrase&output=php&results=1', 
            'key' => '', 
            'link' => 'http://images.search.yahoo.com/search/images?p=%s'
        )
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
            $this->_createErrorJson();
            exit();
        }
        $this->_remoteData = $this->queryWebservices($this->_imageChannels);
        foreach ($this->_imageChannels as $k => $v) {
            $channel = $v['channel'];
            $method = '_parse' . $channel;
            // Dynamically call the json parsers
            if (method_exists(
                $this, $method)) {
                $this->_jsonChannelResults[] = $this->$method();
            }
        }
        $json = $this->_createJson();
        $this->_response->setBody($json);
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
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, AjaxController::TIMEOUT);
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

    private function _parseARKive ()
    {
        $remoteData = $this->_remoteData['ARKive'];
        if (isset($remoteData) && !empty($remoteData)) {
            $channelResult = (object) $this->_emptyChannelResult;
            $data = json_decode($remoteData);
            if ($data->error == '') {
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
            }
            return $channelResult;
        }
        return false;
    }

    private function _parseYahooImages ()
    {
        $remoteData = $this->_remoteData['YahooImages'];
        if (isset($remoteData) && !empty($remoteData)) {
            $channelResult = (object) $this->_emptyChannelResult;
            $data = unserialize($remoteData);
            // Only a single result is required
            $data = $data['ResultSet']['Result'][0];
            $channelResult->source = 'Yahoo Images';
            $href = $this->_imageChannels[$this->_getLinkKey('YahooImages')]['link'];
            $channelResult->href = sprintf($href, urlencode($this->_species));
            $channelResult->src = $data['Thumbnail']['Url'];
            $channelResult->width = $data['Thumbnail']['Width'];
            $channelResult->height = $data['Thumbnail']['Height'];
            $channelResult->caption = $data['Summary'];
            return $channelResult;
        }
        return false;
    }

    private function _createErrorJson ()
    {
        $this->_json['errors'] = $this->_errors;
        return Zend_Json::encode($this->_json);
    }

    private function _createJson ()
    {
        $this->_json['numberOfChannels'] = count($this->_jsonChannelResults);
        $this->_json['results'] = $this->_jsonChannelResults;
        return Zend_Json::encode($this->_json);
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
            $this_errors[] = 'Name parameter with species name empty or missing';
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
                $this_errors[] = 'Species name contains invalid character';
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
        else {
            return false;
        }
    }
}