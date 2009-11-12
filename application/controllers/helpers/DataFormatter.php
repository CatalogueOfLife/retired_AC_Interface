<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_DataFormatter
 * Data formatter helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
class ACI_Helper_DataFormatter extends Zend_Controller_Action_Helper_Abstract
{
    public function formatSearchResults(Zend_Paginator $paginator)
    {
        $res = array();
        $i = 0;
        $translator = Zend_Registry::get('Zend_Translate');
        $textDecorator =
            $this->getActionController()->getHelper('TextDecorator');
        $it = $paginator->getIterator();
        unset($paginator);
        
        foreach ($it as $k => $row) {
            // get accepted species data if yet not there
            $this->_addAcceptedName($row);
            // create links
            if ($row['rank'] >= ACI_Model_Table_Taxa::RANK_SPECIES) {
                $res[$i]['link'] = $translator->translate('Show_details');
                if (ACI_Model_Table_Taxa::isSynonym($row['status'])) {
                    $res[$i]['url'] = '/details/species/id/' . $row['id'];
                } else {
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
            
            $res[$i]['name'] = $this->_appendTaxaSuffix(
                $this->_wrapTaxaName(
                    $textDecorator->highlightMatch(
                        $row['name'],
                        $this->getRequest()->getParam('key', false) ?
                        $this->getRequest()->getParam('key') :
                        array(
                            $this->getRequest()->getParam('genus'),
                            $this->getRequest()->getParam('species'),
                            $this->getRequest()->getParam('infraspecies')
                        ),
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
            
            // Status + accepted name
            if (!$row['is_accepted_name']) {
                $res[$i]['status'] = sprintf(
                    $res[$i]['status'],
                    $this->_appendTaxaSuffix(
                        $row['accepted_species_name'],
                        ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                        $row['accepted_species_author']
                    )
                );
            }
            // Database
            $res[$i]['dbLogo'] = '/images/databases/' .
                $row['db_thumb'];
            $res[$i]['dbLabel'] = $row['db_name'];
            $res[$i]['dbUrl'] =
                '/details/database/id/' . $row['db_id'];
            if (isset($row['distribution'])) {
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
    
    public function formatPlain(array $data)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        foreach ($data as &$row) {
            $this->_addAcceptedName($row);
            $row['name'] = $this->_appendTaxaSuffix(
                $row['name'], $row['status'],
                $row['status'] == ACI_Model_Table_Taxa::STATUS_COMMON_NAME ?
                $row['language'] : $row['author']
            );
            $row['rank'] = $translator->translate(
                ACI_Model_Table_Taxa::getRankString($row['rank'])
            );
            $row['status'] = $translator->translate(
                ACI_Model_Table_Taxa::getStatusString($row['status'], false)
            );
            $row['accepted_species_name'] = $this->_appendTaxaSuffix(
                $row['accepted_species_name'],
                ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                $row['accepted_species_author']
            );
            // Enclose values between double quotes
            foreach($row as &$r) {
                if(strpos($r, '"') !== false) {
                    $r = str_replace('"', '\"', $r);
                }
                $r = '"' . $r . '"';
            }
        }
        return $data;
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
        $numRefs = count($speciesDetails->references);
        $speciesDetails->referencesLabel = $numRefs ?
            $this->getReferencesLabel(
                $numRefs, strip_tags($speciesDetails->name)
            ) : null;
        $speciesDetails->name .= ' (' .
            $translator->translate(
                ACI_Model_Table_Taxa::getStatusString($speciesDetails->status)
            ) . ')';
            
        $textDecorator = $this->getActionController()->getHelper('TextDecorator');
            
        if (!empty($speciesDetails->synonyms)) {
            foreach ($speciesDetails->synonyms as &$synonym) {
                $synonym['name'] = $this->_appendTaxaSuffix(
                    $synonym['name'],
                    $synonym['status'],
                    $synonym['author']
                );
                $synonym['referenceLabel'] = $this->getReferencesLabel(
                    $synonym['num_references'], strip_tags($synonym['name'])
                );
            }
        } else {
            $speciesDetails->synonyms = $textDecorator->getEmptyField();
        }
        // TODO: optimize the following code:
        if (!empty($speciesDetails->commonNames)) {
            foreach ($speciesDetails->commonNames as &$common) {
                $common['referenceLabel'] = $this->getReferencesLabel(
                    $common['num_references'],
                    strip_tags($common['common_name'])
                );
            }
        } else {
            $speciesDetails->commonNames = $textDecorator->getEmptyField();
        }
        if (!$speciesDetails->hierarchy) {
            $speciesDetails->hierarchy = $textDecorator->getEmptyField();
        }
        if (!$speciesDetails->distribution) {
            $speciesDetails->distribution = $textDecorator->getEmptyField();
        } else {
            $speciesDetails->distribution = implode(
                '; ', $speciesDetails->distribution
            );
        }
        if (!$speciesDetails->comment) {
            $speciesDetails->comment = $textDecorator->getEmptyField();
        }
        if (!$speciesDetails->dbId && !$speciesDetails->dbName &&
            !$speciesDetails->dbVersion) {
            $speciesDetails->dbName = $textDecorator->getEmptyField();
        }
        if (!$speciesDetails->scrutinyDate &&
            !$speciesDetails->specialistName) {
            $speciesDetails->latestScrutiny = $textDecorator->getEmptyField();
        }
        else {
            $speciesDetails->latestScrutiny = trim(trim(
                implode(', ', array(
                    $speciesDetails->specialistName, $speciesDetails->scrutinyDate
                )), ',')
            );
        }
        if (!$speciesDetails->lsid) {
            $speciesDetails->lsid = $textDecorator->getEmptyField();
        }
        $speciesDetails->webSite =
            $textDecorator->createLink($speciesDetails->webSite);
            
        $speciesDetails->preface = $preface;
        
        return $speciesDetails;
    }
    
    public function formatDatabaseDetails(array $dbDetails)
    {
        $dbDetails['name'] = $dbDetails['database_name_displayed'];
        $dbDetails['label'] = $dbDetails['database_name'];
        $dbDetails['accepted_species_names'] =
            number_format($dbDetails['accepted_species_names']);
        $dbDetails['accepted_infraspecies_names'] =
            number_format($dbDetails['accepted_infraspecies_names']);
        $dbDetails['common_names'] =
            number_format($dbDetails['common_names']);
        $dbDetails['total_names'] =
            number_format($dbDetails['total_names']);
        $dbDetails['taxonomic_coverage'] =
            $this->getTaxonLinksInDatabaseDetailsPage(
                $dbDetails['taxonomic_coverage']
            );
        // raw link text
        $dbDetails['web_link'] = $dbDetails['web_site'];
        // formatted link
        $dbDetails['web_site'] = $this->getActionController()
            ->getHelper('TextDecorator')->createLink($dbDetails['web_site']);
        return $dbDetails;
    }
     
    /**
     * Returns the references label based on the number of references and
     * the name of the species
     *
     * @param int $numReferences
     * @param string $name
     * @return string
     */
    public function getReferencesLabel($numReferences, $name = null)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        switch($numReferences) {
            case 0:
                $label = is_null($name) ?
                    $translator->translate('No_references_found') :
                    sprintf(
                        $translator->translate('No_references_for'),
                        $name
                    );
                break;
            case 1:
                $label = is_null($name) ?
                    $translator->translate('1_literature_reference') :
                    sprintf(
                        $translator->translate('1_literature_reference_for'),
                        $name
                    );
                break;
            default:
                $label = is_null($name) ?
                    sprintf(
                        $translator->translate('n_literature_references'),
                        $numReferences
                    ) :
                    sprintf(
                        $translator->translate('n_literature_references_for'),
                        $numReferences, $name
                    );
                break;
        }
        return $label;
    }
    
    public function getTaxonLinksInDatabaseDetailsPage($taxonCoverage)
    {
        $firstKingdom = true;
        $output = '';
        $splitByKingdom = explode(';', $taxonCoverage);
        foreach ($splitByKingdom as $kingdom) {
            $firstRank = true;
            if ($firstKingdom == true) {
                $firstKingdom = false;
            } else {
                $output .= ';<br />';
            }
            $splitByRank = explode('-', $kingdom);
            foreach ($splitByRank as $rank) {
                if ($firstRank == true) {
                    $firstRank = false;
                } else {
                    $output .= ' - ';
                }
                $firstSameRank = true;
                $splitBySameRank = explode(',', $rank);
                foreach ($splitBySameRank as $sameRank) {
                    ($firstSameRank == true ?
                        $firstSameRank = false : $output .= ', ');
                    $trimmedRank = trim($sameRank);
                    $output .= (!strstr($trimmedRank, ' ') ?
                        '<a href="' .
                        $this->getFrontController()->getBaseUrl() .
                        '/browse/classification/name/' . $trimmedRank . '">' .
                        $trimmedRank . '</a>' :
                        $trimmedRank
                    );
                }
            }
        }
        return $output;
    }
    
    public function splitByMarkers($name)
    {
        $nameArray = split(' ', $name);
        foreach($nameArray as &$n) {
            $n = array($n, in_array($n, ACI_Model_Table_Taxa::$markers));
        }
        return $nameArray;
    }
    
    protected function _addAcceptedName(&$row)
    {
        if(!$row['is_accepted_name'] && !$row['accepted_species_id']) {
            $row = array_merge(
                $row,
                $this->getActionController()->getHelper('Query')
                     ->getAcceptedSpecies($row['accepted_name_code'])
            );
        }
    }
    
    protected function _appendTaxaSuffix($source, $status, $suffix)
    {
        if ($suffix) {
            switch($status) {
                case ACI_Model_Table_Taxa::STATUS_COMMON_NAME:
                    $source .= ' (' . $suffix . ')';
                    break;
                default:
                    $source .= '  ' . $suffix;
                    break;
            }
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