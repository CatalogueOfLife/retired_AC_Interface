<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_TextDecorator
 * text decorator helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
class ACI_Helper_TextDecorator extends Zend_Controller_Action_Helper_Abstract
{
    public function highlightMatch($haystack, $needle)
    {
        return preg_replace(
            '/(' . $needle . ')/i',
            "<span class=\"matchHighlight\">$1</span>",
            $haystack
        );
    }
}