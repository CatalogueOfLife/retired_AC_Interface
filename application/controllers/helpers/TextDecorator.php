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
    /**
     * Wraps the needle with styled spans in the haystack
     * The needle may contain the * wildcard
     *
     * @param string $haystack
     * @param string $needle
     * @return string
     */
    public function highlightMatch($haystack, $needle, $wrapWords = false)
    {
        if (trim($needle) == '') {
            return $haystack;
        }
        //TODO: review regexp
        $regexp = '/(' .
            str_replace('*', $wrapWords ? '[^ ]*' : '.*', $needle) . ')/i';
        return preg_replace(
            $regexp,
            "<span class=\"matchHighlight\">$1</span>",
            $haystack
        );
    }
}