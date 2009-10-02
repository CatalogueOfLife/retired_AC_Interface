<?php
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