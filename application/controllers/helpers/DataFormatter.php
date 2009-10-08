<?php
class ACI_Helper_DataFormatter extends Zend_Controller_Action_Helper_Abstract
{
    public function formatSearchResults(Zend_Paginator $paginator)
    {
        $res = array();
        $i = 0;
        $translator = Zend_Registry::get('Zend_Translate');
        $textDecorator =
            $this->getActionController()->getHelper('TextDecorator');
        
        foreach ($paginator as $row) {
            
            if ($row['rank'] >= ACI_Model_Table_Taxa::RANK_SPECIES) {
                $res[$i]['link'] = $translator->translate('Show_details');
                if(ACI_Model_Table_Taxa::isSynonym($row['status'])) {
                    $res[$i]['url'] = '/details/species/id/' . $row['id'];
                }
                else {
                $res[$i]['url'] =
                    '/details/species/id/' . $row['accepted_species_id'];
                }
                if ($row['status'] ==
                        ACI_Model_Table_Taxa::STATUS_COMMON_NAME) {
                        $res[$i]['url'] .= '/common/' . $row['taxa_id'];
                }
            } else {
                $res[$i]['link'] = $translator->translate('Show_tree');
                $res[$i]['url'] = '/browse/tree/id/' . $row['taxa_id'];
            }
            $res[$i]['name'] = $this->_getTaxaSuffix(
                $this->_wrapTaxaName(
                    $textDecorator->highlightMatch(
                        $row['name'],
                        $this->getRequest()->getParam('key'),
                        (bool)$this->getRequest()->getParam('match')
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
                    $row['distribution'],
                    $this->getRequest()->getParam('key'),
                    (bool)$this->getRequest()->getParam('match')
                );
            }
            $i++;
        }
        return $res;
    }
    
    /**
     * Formats the species details information
     *
     * @param ACI_Model_Table_Taxa $speciesDetails
     * @return ACI_Model_Table_Taxa
     */
    public function formatSpeciesDetails(ACI_Model_Table_Taxa $speciesDetails)
    {
        $preface = '';
        $translator = Zend_Registry::get('Zend_Translate');
        
        if ($speciesDetails->taxaStatus) {
            $preface =
                sprintf(
                    $translator->translate('You_selected'),
                    $speciesDetails->taxaFullName
                ) .
                (strrpos($speciesDetails->taxaFullName, '.') ==
                    strlen($speciesDetails->taxaFullName) - 1 ? ' ' : '. ');
            switch($speciesDetails->taxaStatus) {
                case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                    $preface .= $translator->translate(
                        'This_is_a_common_name_for'
                    ) . ':';
                    break;
                case ACI_Model_Table_Taxa::STATUS_SYNONYM:
                    $preface .= $translator->translate(
                        'This_is_a_synonym_for'
                    ) . ':';
                    break;
                case ACI_Model_Table_Taxa::STATUS_AMBIGUOUS_SYNONYM:
                    $preface .= $translator->translate(
                        'This_is_an_ambiguous_synonym_for'
                    ) . ':';
                    break;
                case ACI_Model_Table_Taxa::STATUS_MISAPPLIED_NAME:
                    $preface .= $translator->translate(
                        'This_is_a_misapplied_name_for'
                    ) . ':';
                    break;
            }
        }
        $speciesDetails->name .= ' (' .
            $translator->translate(
                ACI_Model_Table_Taxa::getStatusString($speciesDetails->status)
            ) . ')';
        
        // TODO: optimize the following code:
        if (empty($speciesDetails->synonyms)) {
            $speciesDetails->synonyms = '-';
        }
        if (empty($speciesDetails->commonNames)) {
            $speciesDetails->commonNames = '-';
        }
        if ($speciesDetails->hierarchy == '') {
            $speciesDetails->hierarchy = '-';
        }
        if ($speciesDetails->distribution == '') {
            $speciesDetails->distribution = '-';
        } else {
            $speciesDetails->distribution = implode(
                '; ', $speciesDetails->distribution
            );
        }
        if ($speciesDetails->comment == '') {
            $speciesDetails->comment = '-';
        }
        if ($speciesDetails->dbId == '' && $speciesDetails->dbName = '' &&
            $speciesDetails->dbVersion = '') {
            $speciesDetails->dbName = '-';
        }
        if ($speciesDetails->scrutinyDate == '' &&
            $speciesDetails->specialistName = '') {
            $speciesDetails->scrutinyDate = '-';
        }
        if ($speciesDetails->webSite == '') {
            $speciesDetails->webSite = '-';
        }
        if ($speciesDetails->lsid == '') {
            $speciesDetails->lsid = '-';
        }
        $speciesDetails->preface = $preface;
        return $speciesDetails;
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