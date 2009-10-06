<?php
class ACI_Helper_DataFormatter extends Zend_Controller_Action_Helper_Abstract
{
    public function getDataFromPaginator(Zend_Paginator $paginator)
    {
        $res = array();
        $i = 0;
        $translator = Zend_Registry::get('Zend_Translate');
        $textDecorator =
            $this->getActionController()->getHelper('TextDecorator');
        
        foreach ($paginator as $row) {
            
            if ($row['rank'] >= ACI_Model_Table_Taxa::RANK_SPECIES) {
                $res[$i]['link'] = $translator->translate('Show_details');
                $res[$i]['url'] =
                    '/details/species/id/' . $row['accepted_species_id'];
                if (!$row['is_accepted_name']) {
                    if ($row['status'] ==
                        ACI_Model_Table_Taxa::STATUS_COMMON_NAME) {
                        $res[$i]['url'] .= '/common/' . $row['taxa_id'];
                    } else {
                        $res[$i]['url'] .= '/taxa/' . $row['taxa_id'];
                    }
                }
            } else {
                $res[$i]['link'] = $translator->translate('Show_tree');
                $res[$i]['url'] = '/browse/tree/id/' . $row['taxa_id'];
            }
            $res[$i]['name'] = $this->_getTaxaSuffix(
                $this->_wrapTaxaName(
                    $textDecorator->highlightMatch(
                        $row['name'], $this->getRequest()->getParam('key')
                    ),
                    $row['status'],
                    $row['rank']
                ),
                $row['status'],
                $row['status'] == ACI_Model_Table_Taxa::STATUS_COMMON_NAME ?
                $row['language'] : $row['author']
            );
            $res[$i]['rank'] = $translator->translate(
                ACI_Model_Table_Taxa::getRankString($row['rank'])
            );
            
            $res[$i]['status'] = $translator->translate(
                ACI_Model_Table_Taxa::getStatusString($row['status'])
            );
            
            $res[$i]['group'] = $row['kingdom'];
            
            if (!$row['is_accepted_name']) {
                $res[$i]['status'] = sprintf(
                    $res[$i]['status'],
                    '<span class="taxonomicName">' .
                    $row['accepted_species_name'] . '</span> ' .
                    $row['accepted_species_author']
                );
            }
            
            $res[$i]['dbLogo'] = '/images/databases/' .
                $row['db_thumb'];
            $res[$i]['dbLabel'] = $row['db_name'];
            $res[$i]['dbUrl'] =
                '/details/database/id/' . $row['db_id'];
            if(isset($row['distribution']))
            {
                $res[$i]['distribution'] = $textDecorator->highlightMatch(
                    $row['distribution'], $this->getRequest()->getParam('key')
                );
            }
            $i++;
        }
        return $res;
    }
    
    protected function _getTaxaSuffix($source, $status, $suffix)
    {
        switch($status && $suffix != "") {
            case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                $source .= ' (' . $suffix . ')';
                break;
            default:
                $source .= '  ' . $suffix;
                break;
        }
        return $source;
    }

    protected function _wrapTaxaName($source, $status, $rank)
    {
        if ($status != ACI_Model_Table_Taxa::STATUS_COMMON_NAME &&
            $rank >= ACI_Model_Table_Taxa::RANK_SPECIES) {
            $source = '<span class="taxonomicName">' . $source . '</span>';
        }
        return $source;
    }
}