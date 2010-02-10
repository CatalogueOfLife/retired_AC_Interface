<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_View_Helper_Placeholder
 * Placeholder handler for views
 *
 * @category    ACI
 * @package     application
 * @subpackage  views/helpers
 *
 */
class ACI_View_Helper_Placeholder extends Zend_View_Helper_Abstract
{
    public function placeholder($text = '')
    {
        $t = Zend_Registry::get('Zend_Translate');
        $new = ' <span class="new">' . $t->translate('NEW') . '</span>';
        
        $find = array(
            '#\[b\]#',
            '#\[b:([a-zA-Z]*)\]#',
            '#\[/b\]#',
            '#\[span:([a-zA-Z]*)\]#',
            '#\[/span\]#',
            '#\[a:([a-zA-Z0-9:/\-_\.]*)\]([a-zA-Z0-9:/\-_\. \(\)]*)\[/a\]#'
        );
        $replace = array(
            '<b>',
            '<b class="$1">',
            '</b>',
            '<span class="$1">',
            '</span>',
            '<a href="$1">$2</a>'
        );
        $matches = array();
        $dbId = '';
        preg_match_all(
            '#\[link:db:([0-9]+)\]#',
            $text,
            $matches
        );
        if(isset($matches[1])) {
            $db = new ACI_Model_Table_Databases();
            foreach($matches[1] as $match) {
                $dbData = $db->get($match);
                $find[] = '#\[link:db:(' . $match . ')\]#';
                $replace[] = '<a href="' . $this->view->baseUrl() .
                    '/details/database/id/' . $match . '"
                    alt="' . $dbData['database_name'] . '"
                    title="' . $dbData['database_name_displayed'] . '">'.
                    $dbData['database_name'] .'</a>' .
                    ($dbData['is_new'] ? $new : '');
            }
        }
        return preg_replace($find, $replace, $text);
    }
}