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
            if(!isset($row['rank']))
            {
                $row['rank'] = $this->_getRank($row);
            }
            if(!is_int($row['status']))
            {
                if($row['status'] == 'common name')
                    $row['status'] = 6;
            }
            // get accepted species data if yet not there
            $this->_addAcceptedName($row);
            // create links
            if (!in_array($row['rank'], array(
                ACI_Model_Table_Taxa::RANK_KINGDOM,
                ACI_Model_Table_Taxa::RANK_PHYLUM,
                ACI_Model_Table_Taxa::RANK_CLASS,
                ACI_Model_Table_Taxa::RANK_ORDER,
                ACI_Model_Table_Taxa::RANK_SUPERFAMILY,
                ACI_Model_Table_Taxa::RANK_FAMILY,
                ACI_Model_Table_Taxa::RANK_GENUS,
                ACI_Model_Table_Taxa::RANK_SUBGENUS
            ))) {
                $res[$i]['link'] = $translator->translate('Show_details');
                if (ACI_Model_Table_Taxa::isSynonym($row['status'])) {
                    $res[$i]['url'] = '/details/species/id/' . $row['accepted_species_id'];
                } else {
                $res[$i]['url'] =
                    '/details/species/id/' . ($row['status'] != 6 ?
                        $row['id'] : '');
                }
                if ($row['status'] == 6) {
                        $res[$i]['url'] .= $row['taxa_id'] .'/common/' . $row['id'];
                } elseif (in_array($row['status'],array(2,3,5))) {
                    $res[$i]['url'] .= '/synonym/'.$row['id'];
                }
            } else {
                $res[$i]['link'] = $translator->translate('Show_tree');
                $res[$i]['url'] = '/browse/tree/id/' . $row['id'];
            }
            if(!isset($row['name']))
            {
                if(isset($row['taxon_name'])) {
                    $row['name'] = $row['taxon_name'];
                } else {
                    $row['name'] = 
                    ($row['genus'] ? $row['genus'] .
                        ($row['subgenus'] ? ' ('.$row['subgenus'].')' : '') .
                        ($row['species'] ? ' '.$row['species'] : '') .
                        ($row['infraspecies'] ? ' '.$row['infraspecies'] : '') :
                        ($row['family'] ? $row['family'] :
                            ($row['superfamily'] ? $row['superfamily'] :
                                ($row['order'] ? $row['order'] :
                                    ($row['class'] ? $row['class'] :
                                        ($row['phylum'] ? $row['phylum'] :
                                            $row['kingdom'])))))
                    );
                }
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
                $row['status'] == 6 ?
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
            if ((isset($row['is_accepted_name']) && !$row['is_accepted_name']) ||
                (isset($row['accepted_species_id']) && $row['accepted_species_id'])) {
                $res[$i]['status'] = sprintf(
                    $res[$i]['status'],
                    $this->_appendTaxaSuffix(
                        $this->_wrapTaxaName(
                            (isset($row['accepted_species_name']) ?
                                $row['accepted_species_name'] : $row['name']),
                            ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                            $row['rank']
                        ),
                        ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                        (isset($row['accepted_species_author']) ?
                            $row['accepted_species_author'] : $row['author'])
                    )
                );
            }
            // Database
            $res[$i]['dbLogo'] = '/images/databases/' .
                (isset($row['db_thumb']) ? $row['db_thumb'] :
                    str_replace(' ','_',(isset($row['db_name']) ? $row['db_name'] :
                $row['source_database_name'])).'.gif');
            $res[$i]['dbLabel'] = (isset($row['db_name']) ? $row['db_name'] :
                $row['source_database_name']);
            $res[$i]['dbUrl'] =
                '/details/database/id/' . (isset($row['db_id']) ?
                    $row['db_id'] : $row['source_database_id']);
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
    
    private function _getRank($row)
    {
        if($row['infraspecies'])
            return ACI_Model_Table_Taxa::RANK_INFRASPECIES;
        elseif($row['species'])
            return ACI_Model_Table_Taxa::RANK_SPECIES;
        elseif($row['subgenus'])
            return ACI_Model_Table_Taxa::RANK_SUBGENUS;
        elseif($row['genus'])
            return ACI_Model_Table_Taxa::RANK_GENUS;
        elseif($row['family'])
            return ACI_Model_Table_Taxa::RANK_FAMILY;
        elseif($row['superfamily'])
            return ACI_Model_Table_Taxa::RANK_SUPERFAMILY;
        elseif($row['order'])
            return ACI_Model_Table_Taxa::RANK_ORDER;
        elseif($row['class'])
            return ACI_Model_Table_Taxa::RANK_CLASS;
        elseif($row['phylum'])
            return ACI_Model_Table_Taxa::RANK_PHYLUM;
        elseif($row['kingdom'])
            return ACI_Model_Table_Taxa::RANK_KINGDOM;
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
        $dbDetails['label'] = $dbDetails['abbreviated_name'];
        $dbDetails['name'] = $dbDetails['label'] . ': ' . $dbDetails['name'];
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
     
    public function formatDatabaseResultPage(array $dbDetails)
    {
        $dbDetails['name'];
        $dbDetails['label'] = $dbDetails['abbreviation'];
        $dbDetails['accepted_species_names'] =
            number_format($dbDetails['total_species']);
        $dbDetails['url'] = '/details/database/id/'.$dbDetails['id'];
        $dbDetails['thumb'] = '/images/databases/' .
            str_replace(' ', '_', $dbDetails['label']) . '.gif';
        $dbDetails['database_name_displayed'] = $dbDetails['abbreviation'] .
            ': ' . $dbDetails['name'];
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
        $output = $this->_formatTaxonCoverage($taxonCoverage);
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
    
    protected function _formatTaxonCoverage($taxonCoverage)
    {
        $kingdom = $phylum = $class = $order= $output = '';
        $sameRank = false;
        $seperatorDifferentRank = ' - ';
        $seperatorSameRank = ', ';
        foreach($taxonCoverage as $taxa)
        {
            if($class != '' && $class != $taxa['class_id'])
            {
                $output .= '<br />';
                $sameRank = false;
            }
            if($class != $taxa['class_id'])
            {
                $output .= 
                    $this->_getLinkToTree($taxa['kingdom_id'],$taxa['kingdom']) .
                    $seperatorDifferentRank .
                    $this->_getLinkToTree($taxa['phylum_id'],$taxa['phylum']) .
                    $seperatorDifferentRank .
                    $this->_getLinkToTree($taxa['class_id'],$taxa['class']);
                $class = $taxa['class_id'];
            }
            if($order != $taxa['order_id'])
            {
                $output .= ($sameRank == true ? $seperatorSameRank :
                    $seperatorDifferentRank) .
                    $this->_getLinkToTree($taxa['order_id'],$taxa['order']);
                $order = $taxa['order_id'];
                $sameRank = true;
            }
        }
        return $output;
    }
    
    protected function _getLinkToTree($id,$name)
    {
        $link = $this->getFrontController()->getBaseUrl() .
            '/browse/classification/id/' . $id;
        return '<a href="'.$link.'">'.$name.'</a>';
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
        if ((!isset($row['accepted_species_id']) || (
            isset($row['accepted_species_id']) &&
            !$row['accepted_species_id'])) &&
            isset($row['accepted_name_code'])) {
            $row = array_merge(
                $row,
                $this->getActionController()->getHelper('Query')
                     ->getAcceptedSpecies($row['accepted_name_code'])
            );
        }
        if (isset($row['is_accepted_name']) && $row['is_accepted_name']) {
            $row['id'] = $row['accepted_species_id'];
        }
    }
    
    protected function _appendTaxaSuffix($source, $status, $suffix)
    {
        if ($suffix) {
            switch($status) {
                case 'common name':
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