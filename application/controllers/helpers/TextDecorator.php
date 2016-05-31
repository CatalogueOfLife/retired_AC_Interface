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
        $f = array('<span class="matchHighlight">', '</span>');
        $r = array(chr(4), chr(7));
        $haystack = str_replace($f, $r, $haystack);

        if (is_array($needle)) {
            foreach ($needle as $n) {
                $haystack = $this->highlightMatch($haystack, $n, $wrapWords);
            }
            return str_replace($r, $f, $haystack);
        }
        if (trim($needle) == '') {
            return $haystack;
        }
        $needle = $this->_stripSearchParams($needle);

        if ($wrapWords == true && !preg_match('#&\#[0-9]{1,5};#', $needle)) {
            $prefix = '\b';
            $suffix = '\b';
            $replace = '';
        } else {
            $prefix = $suffix = $replace = '';
        }
        /* Pre ACI-620 situation, explicitly uses word boundary
        $regexp = '/\b(' .
            str_replace('*', $replace, $prefix . $needle . $suffix) . ')\b/mi';
        */
        $regexp = '/(' .
            str_replace('*', $replace, $prefix . $needle . $suffix) . ')/mi';

        $haystack = preg_replace(
            $regexp,
            '<span class="matchHighlight">$1</span>',
            $haystack
        );

        // Restore temporarily replaced tags
        return str_replace($r, $f, $haystack);
    }

    public function getEmptyField()
    {
        return self::EMPTY_FIELD;
    }

    public function decorateComboLabel($label, $value = null)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $label = is_null($value) ?
            $translator->translate($label) :
            sprintf($translator->translate($label), $value);
        return "<span class=\"disabledLabel\">" . $label . "</span>";
    }

    public function createLink($linkText, $target = '_self')
    {
        if (!$linkText) {
            return self::EMPTY_FIELD;
        }
        $pattern = '#(^[^ \(\)]*\b)(.*)#';
        $replacement ='<a href="$1" target="' . $target . '">$1</a>$2';
        $link = preg_replace($pattern, $replacement, trim($linkText, "#"));
        return $link;
    }

    private function _stripSearchParams ($s) {
        $p = array('%', '*', '/', '"');
        return str_replace($p, '', stripslashes($s));
    }
}