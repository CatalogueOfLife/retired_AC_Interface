<?php
/**
 *
 * @author Ayco Holleman
 * @version 
 */

/**
 * Setting helper
 * 
 * Displays a setting from (currently only) config.ini
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_Setting extends Zend_View_Helper_Abstract {
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 */
	public function setting($setting) {
		return Bootstrap::instance()->getOption($setting);
	}
	
	/**
	 * Sets the view field
	 * 
	 * @param $view Zend_View_Interface        	
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
