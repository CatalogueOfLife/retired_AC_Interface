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
     * The needle can be an array for multiple highlights
     *
     * @param string $haystack
     * @param mixed $needle
     * @return string
     */
    public function highlightMatch($haystack, $needle)
    {
        if(is_array($needle)) {
            foreach($needle as $n) {
                $haystack =
                    preg_replace('/(' . $n . ')/i', "<*$1*>", $haystack);
            }
            return str_replace(
                '*>', '</span>', str_replace(
                    '<*', '<span class="matchHighlight">', $haystack
                )
            );
        }
        return preg_replace(
            '/(' . $needle . ')/i',
            "<span class=\"matchHighlight\">$1</span>",
            $haystack
        );
    }
}