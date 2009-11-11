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
    const EMPTY_FIELD = '-';
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
        if (is_array($needle)) {
            foreach ($needle as $n) {
                $haystack = $this->highlightMatch($haystack, $n, $wrapWords);
            }
            return $haystack;
        }
        if (trim($needle) == '') {
            return $haystack;
        }
        if ($wrapWords == true) {
            $prefix = '\b';
            $suffix = '\b';
            $replace = '[^ \b]*';
        } else {
            $prefix = '';
            $suffix = '';
            $replace = '.*';
        }
        $regexp = '/(' .
            str_replace('*', $replace, $prefix . $needle . $suffix) . ')/i';
        return preg_replace(
            $regexp,
            "<span class=\"matchHighlight\">$1</span>",
            $haystack
        );
    }
    
    public function getEmptyField()
    {
        return self::EMPTY_FIELD;
    }
    
    public function decorateComboLabel($label)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        return "<span class=\"disabledLabel\">" .
            $translator->translate($label) . "</span>";
    }
    
    public function createLink($linkText)
    {
        if(!$linkText) {
            return self::EMPTY_FIELD;
        }
        $linkText = ltrim($linkText, "#");
        $link = '<a href="' . $linkText . '">' . $linkText . '</a>';
        return $link;
    }

    public function textDecoration($text)
    {
        $find = array(
            '[new]'
        );
        $replace = array(
            '<span class="new">NEW!</span>'
        );
        return str_replace($find,$replace,$text);
    }
}