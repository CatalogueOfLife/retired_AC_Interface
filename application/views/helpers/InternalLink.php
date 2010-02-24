<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_View_Helper_InternalLink
 * Internal links builder for views
 *
 * @category    ACI
 * @package     application
 * @subpackage  views/helpers
 *
 */
class ACI_View_Helper_InternalLink extends Zend_View_Helper_Abstract
{
    public function internalLink($str = '')
    {   
        $matches = $find = $replace = array();

        // links to databases
        preg_match_all('#\[link:db:([0-9]+)\]#', $str, $matches);
        
        if (isset($matches[1])) {
            $t = Zend_Registry::get('Zend_Translate');
            $new = ' <span class="new">' . $t->translate('NEW') . '</span>';
            $dbModel = new ACI_Model_Table_Databases();
            foreach ($matches[1] as $match) {
                $db = $dbModel->get($match);
                $find[] = '#\[link:db:(' . $match . ')\]#';
                $replace[] = '<a href="' . $this->view->baseUrl() .
                    '/details/database/id/' . $match . '"
                    alt="' . $db['database_name'] . '"
                    title="' . $db['database_name_displayed'] . '">'.
                    $db['database_name'] .'</a>' . ($db['is_new'] ? $new : '');
            }
        }
        
        return preg_replace($find, $replace, $str);
    }
}