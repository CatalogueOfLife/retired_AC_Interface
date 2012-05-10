<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class BHLController
 * Handles retrieving/displaying data from BHL Europe
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class BHLController extends AController {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
	}
	
	public function synopsisAction() 
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$this->_response->setHeader('Content-Type', 'text/plain', true);
		
		$genus = $this->_request->getParam('genus');
		$species = $this->_request->getParam('species');		
		$urlPattern = Bootstrap::instance()->getOption('bhl.urlpattern');
		$url = sprintf($urlPattern, "{$genus}%20{$species}");
			
		$data = file_get_contents($url);
		$response = simplexml_load_string($data);
		
		$numFound = (int) $response->result['numFound'];
		
		$references = array();
		foreach($response->result->doc as $doc) {
			$reference = new stdClass();
			$reference->pid = self::_xpathGet($doc->xpath("str[@name='PID']"));
			$reference->title = self::_xpathGet($doc->xpath("arr[@name='mods_title']/str"));
			$reference->publisher = self::_xpathGet($doc->xpath("arr[@name='mods_publisher']/str"));
			$author = array_reduce($doc->xpath("arr[@name='mods_name']/str"), array('BHLController', '_reduce'), null);
 			$reference->author = $author;
			$references[] = $reference;
		}
		
		
		$obj = new stdClass();
		$obj->numFound = $numFound;
		$obj->references = $references;
		
		echo '<pre>';
		print_r($obj);
		echo '</pre>';
		die();
		
		$this->_response->setBody($numFound);
		$this->_response->sendResponse();
		exit;
	}
	
	private static function _xpathGet($xpath) {
		return (string) $xpath[0];
	}
	
	private static function _reduce($thusfar, SimpleXMLElement $sxe) {
		$thusfar = $thusfar === null ? (string) $sxe : $thusfar . '; ' . $sxe;
		return $thusfar;
	}

}
