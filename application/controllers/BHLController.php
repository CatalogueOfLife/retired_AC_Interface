<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class DetailsController
 * Defines the detail pages
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
	
	public function synopsisAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$this->_response->setHeader('Content-Type', 'text/plain', true);
		$this->_response->setBody('1');
		$this->_response->sendResponse();
		exit;
	}

}
