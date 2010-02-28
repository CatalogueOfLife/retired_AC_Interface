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
        
        foreach ($it as $row) {
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
                        $this->_wrapTaxaName(
                            $row['accepted_species_name'],
                            ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                            $row['rank']
                        ),
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
    
    public function formatPlainRow(array $row)
    {
        $translator = Zend_Registry::get('Zend_Translate');
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
        foreach ($row as &$r) {
            $r = '"' . str_replace('"', '\"', $r) . '"';
        }
        return $row;
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
            
        $textDecorator = $this->getActionController()
            ->getHelper('TextDecorator');
            
        if (!empty($speciesDetails->synonyms)) {
            foreach ($speciesDetails->synonyms as &$synonym) {
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
        } else {
            $speciesDetails->latestScrutiny = trim(trim(
                implode(
                    ', ',
                    array(
                        $speciesDetails->specialistName,
                        $speciesDetails->scrutinyDate
                    )
                ), ',')
            );
        }
        if (!$speciesDetails->lsid) {
            $speciesDetails->lsid = $textDecorator->getEmptyField();
        }
        $speciesDetails->webSite =
            $textDecorator->createLink($speciesDetails->webSite, '_blank');
            
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
        $dbDetails['total_synonyms'] =
            number_format($dbDetails['species_synonyms'] +
                $dbDetails['infraspecies_synonyms']
            );
        $dbDetails['taxonomic_coverage'] =
            $this->getTaxonLinksInDatabaseDetailsPage(
                $dbDetails['taxonomic_coverage']
            );
        
        // raw links text
        $links = explode(';', $dbDetails['web_site']);
        unset($dbDetails['web_site']);
        $dbDetails['web_link'] = $links[0];
        // formatted link
        foreach ($links as $link) {
            $dbDetails['web_sites'][] = $this->getActionController()
                ->getHelper('TextDecorator')->createLink($link, '_blank');
        }
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
        switch ($numReferences) {
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
        $ignoreItems = array (
            '\(.*\)', // Ignore everything within parenthesis ()
            '^.*\:', // Ignore everything before the colon :
            'superfamily',
            'superfamilies',
            '^family',
            'genera',
            '^genus',
            'NA', // Not Available, it shouldn't show a link
            'pro parte'
        );
        
        $firstKingdom = true;
        $output = '';
        $splitByKingdom = explode(';', $taxonCoverage);
        // iterate each taxonomic hierarchy
        foreach ($splitByKingdom as $kingdom) {
            $firstRank = true;
            if ($firstKingdom == true) {
                $firstKingdom = false;
            } else {
                // break line after each hierarchy
                $output .= '<br />';
            }
            
            $splitByRank = explode('-', $kingdom);
            // iterate each definition in the hierarchy
            foreach ($splitByRank as $rank) {
                if ($firstRank == true) {
                    $firstRank = false;
                } else {
                    // dash separator for each definition
                    $output .= ' - ';
                }
                
                $firstSameRank = true;
                $splitBySameRank = preg_split('#[,&]#', $rank);
                // iterate each string splitted by comma and ampersand
                foreach ($splitBySameRank as $sameRank) {
                    if ($firstSameRank == true) {
                        $firstSameRank = false;
                    } else {
                        // comma separator for each part
                        $output .= ', ';
                    }
                    $trimmedRank = $sameRank;
                    $prefix = '';
                    $suffix = '';
                    $foundItem[0] = '';
                    
                    // iterate ignored items
                    foreach ($ignoreItems as $item) {
                        if (preg_match('#' . $item . '#', $trimmedRank) == true) {
                            preg_match(
                                '#' . $item . '#', $trimmedRank, $foundItem
                            );
                            strpos($trimmedRank, $foundItem[0]) < 2  ?
                                $prefix = $foundItem[0] . ' ' :
                                $suffix = ' ' . $foundItem[0];
                        }
                        $trimmedRank = preg_replace(
                            '#' . $item . '#', '', $trimmedRank
                        );
                    }
                    
                    $t = Zend_Registry::get('Zend_Translate');
                    $new = ' <span class="new">' . $t->translate('NEW') .
                        '</span> ';
                    $updated = ' <span class="new">' .
                        $t->translate('UPDATED') . '</span> ';
                        
                    $prefix = strcasecmp('(NEW!) ', $prefix) === 0 ? $new : (
                        strcasecmp('(UPDATED!) ', $prefix) === 0 ?
                        $updated : $prefix
                    );
                    $suffix = strcasecmp(' (NEW!)', $suffix) === 0 ? $new : (
                        strcasecmp(' (UPDATED!)', $suffix) === 0 ?
                        $updated : $suffix
                    );
                    
                    $trimmedRank = trim($trimmedRank);
                    
                    if (strstr($trimmedRank, ' ')) {
                        $output .= $trimmedRank;
                    } else {
                        // link to taxonomic browser
                        $link = $this->getFrontController()->getBaseUrl() .
                            '/browse/classification/name/' . $trimmedRank;
                        $output .= $prefix . '<a href="' . $link . '">' .
                            $trimmedRank . '</a>' . $suffix;
                    }
                }
            }
        }
        return $output;
    }
    
    public function splitByMarkers($name)
    {
        $nameArray = explode(' ', $name);
        foreach ($nameArray as &$n) {
            $n = array($n, in_array($n, ACI_Model_Table_Taxa::$markers));
        }
        return $nameArray;
    }
    
    protected function _formatInfraspeciesName($name)
    {
        $nameArray = $this->splitByMarkers($name);
        $name = '';
        foreach ($nameArray as $n) {
            $name .= $n[1] ?
                ' <span class="marker">' . $n[0] . '</span> ' :
                ' ' . $n[0] . ' ';
        }
        return trim($name);
    }
    
    protected function _addAcceptedName(array &$row)
    {
        if (!$row['accepted_species_id']) {
            $row = array_merge(
                $row,
                $this->getActionController()->getHelper('Query')
                     ->getAcceptedSpecies($row['accepted_name_code'])
            );
        }
        if ($row['is_accepted_name']) {
            $row['id'] = $row['accepted_species_id'];
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
        if ($status != ACI_Model_Table_Taxa::STATUS_COMMON_NAME) {
            if ($rank >= ACI_Model_Table_Taxa::RANK_GENUS) {
                if ($rank == ACI_Model_Table_Taxa::RANK_INFRASPECIES) {
                    $source = $this->_formatInfraspeciesName($source);
                }
                $source = '<i>' . $source . '</i>';
            }
        }
        return $source;
    }
}