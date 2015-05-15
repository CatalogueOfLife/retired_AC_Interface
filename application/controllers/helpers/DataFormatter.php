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
            if(!isset($row['rank'])) {
                $row['rank'] = $this->_getRank($row);
            }
            if(!is_int($row['status']) && $row['status'] == 'common name') {
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
                    $res[$i]['url'] = '/details/species/id/' .
                    $this->idToNaturalKey($row['accepted_species_id']);
                } else {
                $res[$i]['url'] =
                    '/details/species/id/' . ($row['status'] != 6 ?
                    $this->idToNaturalKey($row['id']) : '');
                }
                if ($row['status'] == 6) {
                    $res[$i]['url'] .= $this->idToNaturalKey($row['taxa_id']) .'/common/' .
                    $this->idToNaturalKey($row['id']);
                } elseif (ACI_Model_Table_Taxa::isSynonym($row['status'])) {
                    $res[$i]['url'] .= '/synonym/' . $this->idToNaturalKey($row['id']);
                }
            } else {
                $res[$i]['link'] = $translator->translate('Show_tree');
                $res[$i]['url'] = '/browse/tree/id/' . $this->idToNaturalKey($row['id']);
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
            $res[$i]['name'] =
                $this->_appendTaxaSuffix(
                    $this->_wrapTaxaName(
                        (!isset($row['distribution']) ?
                            $textDecorator->highlightMatch(
                                ucfirst(trim($row['name'])),
                                $this->getRequest()->getParam('key', false) ?
                                explode(' ',$this->getRequest()->getParam('key')) :
                                array(
                                    $this->getRequest()->getParam('genus'),
                                    $this->getRequest()->getParam('subgenus'),
                                	$this->getRequest()->getParam('species'),
                                    $this->getRequest()->getParam('infraspecies')
                                ),
                                (bool)$this->getRequest()->getParam('match')
                            ) : ucfirst(trim($row['name']))
                        ),
                        $row['status'],
                        $row['rank']
                    ),
                    $row['status'],
                    $row['status'] == 6 && $row['author'] != '' ?
                    '(' . $row['author'] . ')' : $row['author']
                );
            $res[$i]['fossil_marker'] = $this->_setFossilMarker('', $row,
                $translator->translate('Extinct_tip'));
            $res[$i]['rank'] = $translator->translate(
                ACI_Model_Table_Taxa::getRankString($row['rank'])
            );

            $res[$i]['status'] = $translator->translate(
                ACI_Model_Table_Taxa::getStatusString($row['status'])
            );

            $res[$i]['group'] = ucfirst($row['kingdom']);

            // Status + accepted name
            if ((isset($row['is_accepted_name']) && !$row['is_accepted_name']) ||
                (isset($row['accepted_species_id']) && $row['accepted_species_id'])) {
                $res[$i]['status'] = sprintf(
                    $res[$i]['status'],
                    $this->_appendTaxaSuffix(
                        $this->_wrapTaxaName(
                            ucfirst(trim((isset($row['accepted_species_name']) ?
                                $row['accepted_species_name'] : $row['name']))),
                            ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                            $row['rank']
                        ),
                        ACI_Model_Table_Taxa::STATUS_ACCEPTED_NAME,
                        (isset($row['accepted_species_author']) ?
                            $row['accepted_species_author'] : (isset($row['author']) ?
                                $row['author'] : ''))
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

    private function _setToolTip ($row) {
        if (isset($row['is_extinct']) && $row['is_extinct'] == 1 ||
            isset($row['fossil']) && $row['fossil'] == 1) {
            $translator = Zend_Registry::get('Zend_Translate');
            return ucfirst($translator->translate('is_extinct') . ': ' .
                $translator->translate('y') . '; ' .
                $translator->translate('has_preholocene') . ': ' .
                ($row['has_preholocene'] == 1 ?
                    $translator->translate('y') :
                    $translator->translate('n')) . '; ' .
                $translator->translate('has_modern') . ': ' .
                ($row['has_modern'] == 1 ?
                    $translator->translate('y') :
                    $translator->translate('n')));
        }
        return '';
    }

    private function _getRank($row)
    {
        if(isset($row['infraspecies']) && $row['infraspecies'])
            return ACI_Model_Table_Taxa::RANK_INFRASPECIES;
        elseif(isset($row['species']) && $row['species'])
            return ACI_Model_Table_Taxa::RANK_SPECIES;
        elseif(isset($row['subgenus']) && $row['subgenus'])
            return ACI_Model_Table_Taxa::RANK_SUBGENUS;
        elseif(isset($row['genus']) && $row['genus'])
            return ACI_Model_Table_Taxa::RANK_GENUS;
        elseif(isset($row['family']) && $row['family'])
            return ACI_Model_Table_Taxa::RANK_FAMILY;
        elseif(isset($row['superfamily']) && $row['superfamily'])
            return ACI_Model_Table_Taxa::RANK_SUPERFAMILY;
        elseif(isset($row['order']) && $row['order'])
            return ACI_Model_Table_Taxa::RANK_ORDER;
        elseif(isset($row['class']) && $row['class'])
            return ACI_Model_Table_Taxa::RANK_CLASS;
        elseif(isset($row['phylum']) && $row['phylum'])
            return ACI_Model_Table_Taxa::RANK_PHYLUM;
        elseif(isset($row['kingdom']) && $row['kingdom'])
            return ACI_Model_Table_Taxa::RANK_KINGDOM;
    }

    private function _setFossilMarker ($s, array $row, $customTip = '')
    {
        if (isset($row['is_extinct']) && $row['is_extinct'] == 1 ||
            isset($row['fossil']) && $row['fossil'] == 1) {
            return "<span class='dagger help' title='" .
                (!empty($customTip) ? $customTip : $this->_setToolTip($row))  . "'>†</span>$s";
        }
        return $s;
    }

    public function formatPlainRow(array $row)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $this->_addAcceptedName($row);
        $row['name'] = $this->_setFossilMarker(
            $this->_appendTaxaSuffix(
                $row['name'], $row['status'],
                $row['status'] == ACI_Model_Table_Taxa::STATUS_COMMON_NAME && isset($row['language']) ?
                $row['language'] : $row['author']
            ),
            $row,
            $translator->translate('Extinct_tip')
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

        $name = $speciesDetails->genus .
            (isset($speciesDetails->subgenus) && !empty($speciesDetails->subgenus) ?
                ' ('.ucfirst($speciesDetails->subgenus).')' : '') .  ' ' .
            $speciesDetails->species .
            (!empty($speciesDetails->infraspecific_marker) && strtolower($speciesDetails->kingdom) == 'plantae' ?
                ' </i>'. $speciesDetails->infraspecific_marker . '<i>': '') .
            (isset($speciesDetails->infra) ? ' '. $speciesDetails->infra : '');
        $speciesDetails->name = $this->_setFossilMarker(
            '<i>' . $name . '</i> ' .  $speciesDetails->author,
            (array) $speciesDetails,
            $translator->translate('Extinct_tip')
        );

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
        $speciesDetails->headTitle = $speciesDetails->taxaStatus ?
            strip_tags($speciesDetails->taxaFullName) : $name . ' ' . $speciesDetails->author;
        $numRefs = count($speciesDetails->references);
        $speciesDetails->referencesLabel = $numRefs ?
            $this->getReferencesLabel(
                $numRefs, strip_tags($speciesDetails->name)
            ) : null;

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
                if (!empty($common['country']) && !empty($common['region'])) {
                    $common['country'] = $common['country'] . '(' . $common['region'] . ')';
                } else if (empty($common['country']) && !empty($common['region'])) {
                    $common['country'] = $common['region'];
                }
                $common['transliteration'] = ucfirst($common['transliteration']);
            }
        } else {
            $speciesDetails->commonNames = $textDecorator->getEmptyField();
        }
        if (!$speciesDetails->hierarchy) {
            $speciesDetails->hierarchy = $textDecorator->getEmptyField();
        } else {
            foreach ($speciesDetails->hierarchy as $i => $row) {
                // Old version neatly displayed three flags; disabled as wished by Yuri
                // $speciesDetails->hierarchy[$i]['tooltip'] = $this->_setToolTip($row['tooltip']);
                $speciesDetails->hierarchy[$i]['tooltip'] =
                    isset($row['tooltip']['is_extinct']) && $row['tooltip']['is_extinct'] == 1 ?
                    $translator->translate('Extinct_tip') : '';
            }
        }
        if (!$speciesDetails->distribution) {
            $speciesDetails->distribution = $textDecorator->getEmptyField();
        } else {
        	$temp = array();
        	foreach($speciesDetails->distribution as $dist) {
        		if (!in_array($dist['distribution'], $temp)) {
        		    $temp[] = $dist['distribution'];
        		}
        	}
            $speciesDetails->distributionString = implode(
                '; ', $temp
            );
        }
        if (!$speciesDetails->comment) {
            $speciesDetails->comment = $textDecorator->getEmptyField();
        }
        if (!$speciesDetails->dbId && !$speciesDetails->dbName &&
            !$speciesDetails->dbVersion) {
            $speciesDetails->dbName = $textDecorator->getEmptyField();
        }
        $speciesDetails->dbVersion = $speciesDetails->dbVersion;

        if (empty($speciesDetails->scrutinyDate) && empty($speciesDetails->specialistName)) {
            $speciesDetails->latestScrutiny = $textDecorator->getEmptyField();
        } else {
            $speciesDetails->latestScrutiny = !empty($speciesDetails->specialistName) ?
                $speciesDetails->specialistName : '';
            $speciesDetails->latestScrutiny .= !empty($speciesDetails->scrutinyDate) ?
                (!empty($speciesDetails->specialistName) ? ', ' : '') . $speciesDetails->scrutinyDate : '';
        }

        if (!$speciesDetails->lsid) {
            $speciesDetails->lsid = $textDecorator->getEmptyField();
        }
        $speciesDetails->webSite =
            $textDecorator->createLink($speciesDetails->webSite, '_blank');

        $speciesDetails->preface = $preface;

        if (!empty($speciesDetails->images)) {
            $this->resizeThumbnails($speciesDetails);
        } else {
            $speciesDetails->images = $textDecorator->getEmptyField();
        }
        if (!empty($speciesDetails->dbCoverage)) {
            $speciesDetails->dbCoverageIcon = $this->_getDbCoverageIcon($speciesDetails->dbCoverage);
        }
        if (!empty($speciesDetails->dbConfidence)) {
            $speciesDetails->dbConfidenceIcons = $this->_getDbConfidenceIcons($speciesDetails->dbConfidence);
        }
        if (!empty($speciesDetails->lifezones)) {
            foreach ($speciesDetails->lifezones as $r) {
                $lz[] = $r['lifezone'];
            }
            $speciesDetails->lifezones = ucfirst(implode(',', $lz));
        } else {
            $speciesDetails->lifezones = $textDecorator->getEmptyField();
        }
        if ($speciesDetails->is_extinct == 1) {
            // Ruud 17-02-15: prettier text, let's hope it will return
 /*
  *         if ($speciesDetails->has_preholocene == 1 || $speciesDetails->has_modern == 1) {
                $speciesDetails->fossil = $translator->translate('This_extinct_species_is_known_to exist_during') . ' ';
                if ($speciesDetails->has_preholocene == 1) {
                    $speciesDetails->fossil .= 'pre-Holocene (< 11.700 BC)';
                }
                if ($speciesDetails->has_preholocene == 1 && $speciesDetails->has_modern == 1) {
                    $speciesDetails->fossil .= ' ' . $translator->translate('and');
                }
                if ($speciesDetails->has_modern == 1) {
                    $speciesDetails->fossil .= ' ' . $translator->translate('at_least_part_of_the_Modern_era') . ' (> 11.700 BC)';
                }
            } else {
                $speciesDetails->fossil = $translator->translate('This_is_an_extinct_species');
            }
*/
            // Yuri:
            $speciesDetails->fossil = $this->_setToolTip(
                array(
                    'is_extinct' => $speciesDetails->is_extinct,
                    'has_preholocene' => $speciesDetails->has_preholocene,
                    'has_modern' => $speciesDetails->has_modern
                 )
            );
        }
        return $speciesDetails;
    }

    // Resize images to the thumbnail with the smallest height
    public function resizeThumbnails(ACI_Model_Table_Taxa $speciesDetails) {
        $minHeight = 250;
        $maxWidth = 250;
        foreach ($speciesDetails->images as $image) {
            if ($image['height'] < $minHeight) {
                $minHeight = $image['height'];
            }
        }
        foreach ($speciesDetails->images as &$image) {
            if ($image['height'] > $minHeight || $image['width'] > $maxWidth) {
                $heightRatio = $minHeight / $image['height'];
                $widthRatio = $maxWidth / $image['width'];
                $heightRatio < $widthRatio ? $ratio = $heightRatio : $ratio = $widthRatio;
                $image['height'] = round($ratio * $image['height']);
                $image['width'] = round($ratio * $image['width']);
            }
        }
    }

    public function formatDate($date)
    {
        if($date == '0000-00-00') {
            return $date;
        }
        //changes yyyy-mm-dd into dd mon yyyy
        return date('d M Y', strtotime($date));
    }

    public function formatDatabaseDetails(array $dbDetails)
    {
        $dbDetails['label'] = $dbDetails['short_name'];
        $dbDetails['name'] = $dbDetails['label'] . ': ' . $dbDetails['full_name'];
        $dbDetails['details_name'] = str_replace($dbDetails['label'] . ': ', '', $dbDetails['full_name']);
        $dbDetails['accepted_species_names'] =
            number_format($dbDetails['accepted_species_names']);
        $dbDetails['accepted_infraspecies_names'] =
            number_format($dbDetails['accepted_infraspecies_names']);
        $dbDetails['common_names'] =
            number_format($dbDetails['common_names']);
        $dbDetails['total_names'] =
            number_format($dbDetails['total_names']);
        $dbDetails['total_synonyms'] =
        	number_format($dbDetails['synonyms']);
        $dbDetails['total_extant_names'] =
        	number_format($dbDetails['total_extant_names']);
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
        if (!empty($dbDetails['coverage'])) {
            $dbDetails['coverage_icon'] = $this->_getDbCoverageIcon($dbDetails['coverage']);
        }
        if (!empty($dbDetails['confidence'])) {
            $dbDetails['confidence_icons'] = $this->_getDbConfidenceIcons($dbDetails['confidence']);
        }
        $dbDetails['is_new'] = $this->decorateDbIsNew($dbDetails['is_new']);
        return $dbDetails;
    }

    public function decorateDbIsNew ($isNew)
    {
        $t = Zend_Registry::get('Zend_Translate');
        if ($isNew == 0) {
            return false;
        }
        if ($isNew == 1) {
            return $t->translate('NEW');
        }
        if ($isNew == 2) {
            return $t->translate('UPDATED');
        }
     }

    private function _getDbCoverageIcon($coverage = '') {
        if ($coverage == '') {
            return false;
        }
        $icon = 'global';
        if (strstr(strtolower($coverage), 'region') !== false) {
            $icon = 'regional';
        }
        return $icon;
    }

    private function _getDbConfidenceIcons($confidence = 0) {
        if ($confidence == 0){
            return array();
        }
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $confidence) {
                $confidence_icons[] = 'star';
            } else {
                $confidence_icons[] = 'no_star';
            }
        }
        return $confidence_icons;
    }

    public function formatDatabaseResultPage(array $dbDetails)
    {
        $dbDetails['name'];
        $dbDetails['label'] = $dbDetails['abbreviation'];
        $dbDetails['accepted_species_names'] =
            number_format($dbDetails['total_species']);
        $dbDetails['extinct_species'] =
            number_format($dbDetails['total_extinct_species']);
        $dbDetails['url'] = '/details/database/id/'.$dbDetails['id'];
        $dbDetails['thumb'] = '/images/databases/' .
            str_replace(' ', '_', $dbDetails['label']) . '.gif';
        $dbDetails['database_name_displayed'] = $dbDetails['abbreviation'] .
            ': ' . $dbDetails['name'];
        $dbDetails['is_new'] = $this->decorateDbIsNew($dbDetails['is_new']);
        return $dbDetails;
    }

    public function formatSpeciesTotals(array $input)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $previous = false;
        $total = count($input);
        $phyla = $totals = $c1 = $c2 = array();
        for ($i = 0; $i < $total; $i++) {
            $current = $input[$i]['kingdom'];
            if ($current != $previous) {
                if ($previous) {
                    $phyla[$previous] = $$previous;
                    $totals[$previous] = array(
                        'actual' => number_format(array_sum($c2)),
                        'estimate' => in_array(0, $c1) ? $translator->translate('Not available') :
                            number_format(array_sum($c1)),
                        'coverage' => $this->getCoverage($c2, $c1)
                    );
                    $c1 = $c2 = array();
                    unset($$previous);
                }
                $$current = array();
            }
            // Format estimate and percentage
            $estimate = $input[$i]['total_species_estimation'];
            $c1[] = $estimate;
            $actual = $input[$i]['total_species'];
            $c2[] = $actual;
            $output = array (
                'name' => $this->_getLinkToTree($input[$i]['taxon_id'], $input[$i]['name']),
                'estimate' => $estimate == 0 ? $translator->translate('Not available') :
                    number_format($estimate),
                'actual' => number_format($actual),
                'coverage' => $this->getCoverage($actual, $estimate),
                'source' => $this->_formatSourceImage($input[$i]['source'])
            );
            array_push($$current, $output);
            if ($i == $total - 1) {
                $phyla[$current] = $$current;
                $totals[$current] = array(
                    'actual' => number_format(array_sum($c2)),
                    'estimate' => in_array(0, $c1) ? $translator->translate('Not available') :
                        number_format(array_sum($c1)),
                    'coverage' => $this->getCoverage($c2, $c1)
                );
            }
            $previous = $current;
        }
        return array(
            'phyla' => $phyla,
            'totals' => $totals
        );
    }

    private function _formatSourceImage($reference) {
        if (empty($reference)) {
            return false;
        }
        $reference = htmlentities($reference);
        $src = $this->getFrontController()->getBaseUrl() . '/images/book.gif';
        $img = '<img src="'.$src.'" alt="'.$reference.'" title="'.$reference.'" />';
        return $img;
    }

    public function getCoverage ($actual, $estimate)
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $actual = is_array($actual) ? array_sum($actual) : $actual;
        $estimate = is_array($estimate) ? array_sum($estimate) : $estimate;
        if (empty($estimate) || empty($actual)) {
            return $translator->translate('Not available');
        }
        $coverage = $actual / $estimate * 100;
        if ($coverage > 100) {
            $coverage = '100';
        } else if ($coverage > 99 && $coverage < 100) {
            $coverage = '>99';
        } else if ($coverage > 0 && $coverage < 1) {
            $coverage = '<1';
        } else {
            $coverage = round($coverage);
        }
        return $coverage . '%';
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

/*    public function getTaxonLinksInDatabaseDetailsPage($taxonCoverage)
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
    }*/

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
                    $this->_getLinkToClassification($taxa['kingdom_id'],$taxa['kingdom']) .
                    $this->_getRankStatus($taxa['kingdom_status']) .
                    $seperatorDifferentRank .
                    $this->_getLinkToClassification($taxa['phylum_id'],$taxa['phylum']) .
                    $this->_getRankStatus($taxa['phylum_status']) .
                    $seperatorDifferentRank .
                    $this->_getLinkToClassification($taxa['class_id'],$taxa['class']) .
                    $this->_getRankStatus($taxa['class_status']);
                $class = $taxa['class_id'];
            }
            if($order != $taxa['order_id'])
            {
                $output .= ($sameRank == true ? $seperatorSameRank :
                    $seperatorDifferentRank) .
                    $this->_getLinkToClassification($taxa['order_id'],$taxa['order']) .
                    $this->_getRankStatus($taxa['order_status']);
                $order = $taxa['order_id'];
                $sameRank = true;
            }
        }
        return $output;
    }

    protected function _getRankStatus($status)
    {
        if($status == 1) {
            return ' <span class="new">NEW!</span>';
        } elseif($status == 2) {
            return ' <span class="new">UPDATED!</span>';
        }
        return '';
    }

    protected function _getLinkToClassification($id,$name)
    {
        $link = $this->getFrontController()->getBaseUrl() .
            '/browse/t/id/' . $this->idToNaturalKey($id);
        return '<a href="'.$link.'">'.$name.'</a>';
    }

    protected function _getLinkToTree($id,$name)
    {
        $link = $this->getFrontController()->getBaseUrl() .
            '/browse/tree/id/' . $this->idToNaturalKey($id);
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


    protected function _addCnRegion ($speciesDetails)
    {
        $total = count($speciesDetails->commonNames);
        for ($i = 0; $i < $total; $i++) {
            $cn = $speciesDetails->commonNames[$i];
            if (!empty($cn['country']) && !empty($cn['region'])) {
                $speciesDetails->commonNames[$i]['country'] = $cn['country'] . '(' . $cn['region'] . ')';
            } else if (empty($cn['country']) && !empty($cn['region'])) {
                $speciesDetails->commonNames[$i]['country'] = $cn['region'];
            }
        }
        return $speciesDetails;
    }

    public function idToNaturalKey ($id)
    {
       $search = new ACI_Model_Search(Zend_Registry::get('db'));
       return $search->idToNaturalKey($id);
    }

    public function naturalKeyToId ($hash)
    {
       $search = new ACI_Model_Search(Zend_Registry::get('db'));
       return $search->naturalKeyToId($hash);
    }
}