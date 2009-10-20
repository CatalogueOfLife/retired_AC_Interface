<?php 
class ACI_View_Helper_Placeholder extends Zend_View_Helper_Abstract
{
    public function placeholder($text='')
    {
        $find = array(
            '#\[new\]#',
            '#\[b\]#',
            '#\[b:([a-z]*)\]#',
            '#\[/b\]#',
            '#\[span:([a-z]*)\]#',
            '#\[/span\]#'
        );
        $replace = array(
            '<span class="new">NEW!</span>',
            '<b>',
            '<b class="$1">',
            '</b>',
            '<span class="$1">',
            '</span>'
        );
        $matches = array();
        $dbId = '';
        preg_match_all(
            '#\[link:db:([0-9]+)\]#',
            $text,
            $matches
        );
        if(isset($matches[1]))
        {
            foreach($matches[1] as $match)
            {
                $find[] = '#\[link:db:(' . $match . ')\]#';
                $replace[] = '<a href="http://localhost/aci/details/database/id/' .
                $match . '">'. $this->getDatabaseFromId($match) .'</a>';
            }
        }
        return preg_replace($find,$replace,$text);
    }
    
    private function getDatabaseFromId($id)
    {
        $db = new ACI_Model_Table_Databases;
        $dbData = $db->get($id);
        return $dbData['database_name'];
    }
}

?>