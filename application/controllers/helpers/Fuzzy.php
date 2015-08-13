<?php

/**
 * Annual Checklist Interface
 *
 * Class ACI_Helper_Fuzzy
 * Fuzzy building helper
 *
 * @category    ACI
 * @package     application
 * @subpackage  helpers
 *
 */
class ACI_Helper_Fuzzy extends Zend_Controller_Action_Helper_Abstract
{
    protected $_db;
    protected $_logger;
    
    public function __construct()
    {
        $this->_db = Zend_Registry::get('db');
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
     * get all possible matches against the catalogue of life
     *
     * @param String $searchtext taxon string(s) to search for
     * @return array result of all searches
     */
    public function getMatches ($searchtext)
    { 
        $searchresult = array();
        $sort1 = $sort2 = array();
        $lev = array();

        if (strpos(trim($searchtext), ' ') === false) { // Uninomial form
            $uninomial           = ucfirst(trim($searchtext));
            $lenUninomial        = mb_strlen(trim($searchtext), "UTF-8");
            
            /**
             * do the actual calculation of the distances
             * and decide if the result should be kept
             */
            
            // Genus
            $select = $this->_db->select()
                                ->from(
                                array('sg' => '_search_genera'),
                                        array(
                                            'genus' => 'sg.genus',
                                            'mdld' => "mdld('" . $uninomial . "', sg.genus, 2, 4)"
                                        )
                                );
            
            $rows = $this->_db->query($select);
            
            while ($row = $rows->fetch()) {
                $limit = min($lenUninomial, strlen($row['genus'])) / 2;     // 1st limit of the search
                if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {           // 2nd limit of the search
                    $searchresult[] = array('taxon'    => $row['genus'],
                                            'distance' => $row['mdld'],
                                            'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['genus'], "UTF-8"), $lenUninomial));
                }
            }

            // Family
            $select = $this->_db->select()
                                ->from(
                                array('sf' => '_search_family'),
                                        array(
                                            'family' => 'sf.family',
                                            'mdld' => "mdld('" . $uninomial . "', sf.family, 2, 4)"
                                        )
                                )
                                ->group('sf.family');
            
            $rows = $this->_db->query($select);
            
            while ($row = $rows->fetch()) {
                $limit = min($lenUninomial, strlen($row['family'])) / 2;     // 1st limit of the search
                if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {            // 2nd limit of the search
                    $searchresult[] = array('taxon'    => $row['family'],
                                            'distance' => $row['mdld'],
                                            'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['family'], "UTF-8"), $lenUninomial));
                }
            }

            // Superfamily
            $select = $this->_db->select()
                                ->from(
                                array('sf' => '_search_family'),
                                        array(
                                            'superfamily' => 'sf.superfamily',
                                            'mdld' => "mdld('" . $uninomial . "', sf.superfamily, 2, 4)"
                                        )
                                )
                                ->group('sf.superfamily');
            
            $rows = $this->_db->query($select);
            
            while ($row = $rows->fetch()) {
                $limit = min($lenUninomial, strlen($row['superfamily'])) / 2;     // 1st limit of the search
                if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {                 // 2nd limit of the search
                    $searchresult[] = array('taxon'    => $row['superfamily'],
                                            'distance' => $row['mdld'],
                                            'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['superfamily'], "UTF-8"), $lenUninomial));
                }
            }

            // Order
            $select = $this->_db->select()
                                ->from(
                                array('sf' => '_search_family'),
                                        array(
                                            'order' => 'sf.order',
                                            'mdld' => "mdld('" . $uninomial . "', sf.order, 2, 4)"
                                        )
                                )
                                ->group('sf.order');
            
            $rows = $this->_db->query($select);
            
            while ($row = $rows->fetch()) {
                $limit = min($lenUninomial, strlen($row['order'])) / 2;     // 1st limit of the search
                if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {           // 2nd limit of the search
                    $searchresult[] = array('taxon'    => $row['order'],
                                            'distance' => $row['mdld'],
                                            'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['order'], "UTF-8"), $lenUninomial));
                }
            }

            // Class
            $select = $this->_db->select()
                                ->from(
                                array('sf' => '_search_family'),
                                        array(
                                            'class' => 'sf.class',
                                            'mdld' => "mdld('" . $uninomial . "', sf.class, 2, 4)"
                                        )
                                )
                                ->group('sf.class');
            
            $rows = $this->_db->query($select);
            
            while ($row = $rows->fetch()) {
                $limit = min($lenUninomial, strlen($row['class'])) / 2;     // 1st limit of the search
                if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {           // 2nd limit of the search
                    $searchresult[] = array('taxon'    => $row['class'],
                                            'distance' => $row['mdld'],
                                            'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['class'], "UTF-8"), $lenUninomial));
                }
            }

            // Phylum
            $select = $this->_db->select()
                                ->from(
                                array('sf' => '_search_family'),
                                        array(
                                            'phylum' => 'sf.phylum',
                                            'mdld' => "mdld('" . $uninomial . "', sf.phylum, 2, 4)"
                                        )
                                )
                                ->group('sf.phylum');
            
            $rows = $this->_db->query($select);
            
            while ($row = $rows->fetch()) {
                $limit = min($lenUninomial, strlen($row['phylum'])) / 2;     // 1st limit of the search
                if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {            // 2nd limit of the search
                    $searchresult[] = array('taxon'    => $row['phylum'],
                                            'distance' => $row['mdld'],
                                            'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['phylum'], "UTF-8"), $lenUninomial));
                }
            }

            // Kingdom
            $select = $this->_db->select()
                                ->from(
                                array('sf' => '_search_family'),
                                        array(
                                            'kingdom' => 'sf.kingdom',
                                            'mdld' => "mdld('" . $uninomial . "', sf.kingdom, 2, 4)"
                                        )
                                )
                                ->group('sf.kingdom');
            
            $rows = $this->_db->query($select);
            
            while ($row = $rows->fetch()) {
                $limit = min($lenUninomial, strlen($row['kingdom'])) / 2;     // 1st limit of the search
                if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {            // 2nd limit of the search
                    $searchresult[] = array('taxon'    => $row['kingdom'],
                                            'distance' => $row['mdld'],
                                            'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['kingdom'], "UTF-8"), $lenUninomial));
                }
            }

            // If there's more than one hit, sort them (faster here than within the db)
            if (count($searchresult) > 1) {
                foreach ($searchresult as $key => $row) {
                    $sort[$key] = $row['taxon'];
                }
                array_multisort($sort, SORT_ASC, $searchresult);
            }
        } else { // Multinomial form
            // Parse the taxon string
            $parts = $this->_tokenizeTaxa($searchtext);

            // Distribute the parsed string to different variables and calculate the (real) length
            $genus[0]    = ucfirst($parts['genus']);
            $lenGenus[0] = mb_strlen($parts['genus'], "UTF-8");
            $genus[1]    = ucfirst($parts['subgenus']);              // Subgenus (if any)
            $lenGenus[1] = mb_strlen($parts['subgenus'], "UTF-8");   // Real length of subgenus
            $epithet     = $parts['epithet'];
            $lenEpithet  = mb_strlen($parts['epithet'], "UTF-8");
            $rank        = $parts['rank'];
            $epithet2    = $parts['subepithet'];
            $lenEpithet2 = mb_strlen($parts['subepithet'], "UTF-8");

            /**
             * first do the search for the genus and subgenus
             * to speed things up we chekc first if there is a full hit
             * (it may not be very likely but the penalty is quite low)
             */
            for ($i = 0; $i < 2; $i++) {
                
                /**
                 * do the actual calculation of the distances
                 * and decide if the result should be kept
                 */
                
                $select = $this->_db->select()
                                    ->from(
                                    array('sg' => '_search_genera'),
                                            array(
                                                'genus' => 'sg.genus',
                                                'mdld' => "mdld('" . $genus[$i] . "', sg.genus, 2, 4)"
                                            )
                                    );
                
                $rows = $this->_db->query($select);

                while ($row = $rows->fetch()) {
                    $limit = min($lenGenus[$i], strlen($row['genus'])) / 2;     // 1st limit of the search
                    if ($row['mdld'] <= 3 && $row['mdld'] < $limit) {           // 2nd limit of the search
                        $lev[] = array('taxon'    => $row['genus'],
                                       'distance' => $row['mdld'],
                                       'ratio'    => 1 - $row['mdld'] / max(mb_strlen($row['genus'], "UTF-8"), $lenGenus[$i]));
                    }
                }
                if (empty($genus[1])) break;    // No subgenus, we're finished here
            }

            // If there's more than one hit, sort them (faster here than within the db)
            if (count($lev) > 1) {
                foreach ($lev as $key => $row) {
                    $sort[$key] = $row['taxon'];
                }
                array_multisort($sort, SORT_ASC, $lev);
            }

            /**
             * second do the search for the species and supspecies (if any)
             * if neither species nor subspecies are given, all species are returned
             * only genera which passed the first test will be used here
             */
            foreach ($lev as $key => $val) {
                $sql = "SELECT species, infraspecies, infraspecific_marker, author";
                if ($epithet) {  // If an epithet was given, use it
                    $sql .= ", mdld('" . $epithet . "', species, 4, 5)  as mdld";
                    if ($epithet2 && $rank) {  // If a subepithet was given, use it
                        $sql .= ", mdld('" . $epithet2 . "', infraspecies, 4, 5) as mdld2";
                    }
                }
                $sql .= " FROM _search_scientific
                          WHERE genus = '" . $val['taxon'] . "'";
                if (!($epithet2 && $rank)) {
                    $sql .= " AND (infraspecies IS NULL OR infraspecies = '')";
                }
                
                $rows = $this->_db->query($sql);
                
                while ($row = $rows->fetch()) {
                    $name = trim($row['species']);
                    $found = false;
                    if ($epithet) {
                        $distance = $row['mdld'];
                        $limit = min($lenEpithet, mb_strlen($row['species'], "UTF-8")) / 2;                   // 1st limit of the search
                        if (($distance + $val['distance']) <= 4 && $distance <= 4 && $distance <= $limit) {   // 2nd limit of the search
                            if ($epithet2 && $rank) {
                                $limit2 = min($lenEpithet2, mb_strlen($row['infraspecies'], "UTF-8")) / 2;    // 3rd limit of the search
                                if ($row['mdld2'] <= 4 && $row['mdld2'] <= $limit2) {                         // 4th limit of the search
                                    $found = true;
                                    $ratio = 1
                                           - $distance / max(mb_strlen($row['species'], "UTF-8"), $lenEpithet)
                                           - $row['mdld2'] / max(mb_strlen($row['infraspecies'], "UTF-8"), $lenEpithet2);
                                    $distance += $row['mdld2'];
                                }
                            } else {
                                $found = true;
                                $ratio = 1 - $distance / max(mb_strlen($row['species'], "UTF-8"), $lenEpithet);
                            }
                        }
                    } else {
                        $found = true;
                        $ratio = 1;
                        $distance = 0;
                    }

                    // If we've found anything valuable, we put everything together
                    if ($found) {                        
                        // Format the taxon-output
                        $taxon = $val['taxon'];
                        $taxon .= $row['species'] ? " " . $row['species'] : "";
                        $taxon .= $row['infraspecific_marker'] ? " " . $row['infraspecific_marker'] : "";
                        $taxon .= $row['infraspecies'] ? " " . $row['infraspecies'] : "";
                                    
                        // Put everything into the output-array
                        $searchresult[] = array('taxon'    => $taxon,
                                                'distance' => $distance + $val['distance'],
                                                'ratio'    => $ratio * $val['ratio']);
                    }
                }
            }            
            
            // If there's more than one hit, sort them (faster here than within the db)
            if (count($searchresult) > 1) {
                foreach ($searchresult as $key => $row) {
                    $sort[$key] = $row['ratio'];
                }
                array_multisort($sort, SORT_ASC, $searchresult);
            }
        }

        return $searchresult;
    }

    /**
     * parses and atomizes the Namestring
     *
     * @param string $taxon taxon string to parse
     * @return array parts of the parsed string
     */
    protected function _tokenizeTaxa($taxon)
    {
        $result = array('genus'      => '',
                        'subgenus'   => '',
                        'epithet'    => '',
                        'author'     => '',
                        'rank'       => 0,
                        'subepithet' => '',
                        'subauthor'  => '');

        $taxon = ' ' . trim($taxon);
        $atoms = $this->_atomizeString($taxon, ' ');
        $maxatoms = count($atoms);
        $pos = 0;

        // Check for any noise at the beginning of the taxon
        if ($this->_isEqual($atoms[$pos]['sub'], $this->_getTaxonExcludes()) !== false) $pos++;
        if ($pos >= $maxatoms) return $result;

        // Get the genus
        $result['genus'] = $atoms[$pos++]['sub'];
        if ($pos >= $maxatoms) return $result;

        // Check for any noise between genus and epithet
        if ($this->_isEqual($atoms[$pos]['sub'], $this->_getTaxonExcludes()) !== false) $pos++;
        if ($pos >= $maxatoms) return $result;

        // Get the subgenus (if it exists)
        if (substr($atoms[$pos]['sub'], 0, 1) == '(' && substr($atoms[$pos]['sub'], -1, 1) == ')') {
            $result['subgenus'] = substr($atoms[$pos]['sub'], 1, strlen($atoms[$pos]['sub']) - 2);
            $pos++;
            if ($pos >= $maxatoms) return $result;
        }

        // Get the epithet
        $result['epithet'] = $atoms[$pos++]['sub'];
        if ($pos >= $maxatoms) return $result;

        $sub = $this->_findInAtomizedArray($atoms, $this->_getTaxonRankTokens());
        if ($sub) {
            $result['rank'] = intval($sub['key']);
            $subpos  = $sub['pos'];
        } else {
            $result['rank'] = 0;
            $subpos = $maxatoms;
        }

        // Check if the next word has a lowercase beginning and there is no rank -> infraspecies with missing keyword
        $checkLetter = mb_substr($atoms[$pos]['sub'], 0, 1);
        if (mb_strtoupper($checkLetter) != $checkLetter && $result['rank'] == 0) {
            $result['author'] = '';
            $result['rank'] = 1;
            $result['subepithet'] = $atoms[$pos++]['sub'];
            if ($pos >= $maxatoms) return $result;

            // Subauthor auslesen
            while ($pos < $maxatoms) {
                $result['subauthor'] .= $atoms[$pos++]['sub'] . ' ';
            }
            $result['subauthor'] = trim($result['subauthor']);
        } else {  // Normal operation
            // Get the author
            while ($pos < $subpos) {
                $result['author'] .= $atoms[$pos++]['sub'] . ' ';
            }
            $result['author'] = trim($result['author']);
            if ($pos >= $maxatoms) return $result;

            if ($result['rank']) {
                $pos = $subpos + 1;
                if ($pos >= $maxatoms) return $result;

                // Get the subepithet
                $result['subepithet'] = $atoms[$pos++]['sub'];
                if ($pos >= $maxatoms) return $result;

                // Subauthor auslesen
                while ($pos < $maxatoms) {
                    $result['subauthor'] .= $atoms[$pos++]['sub'] . ' ';
                }
                $result['subauthor'] = trim($result['subauthor']);
            }
        }

        return $result;
    }

    /**
     * localises a delimiter within a string and returns the positions
     *
     * Localises a delimiter within a string. Returns the positions of the
     * first character after the delimiter, the number of characters to the
     * next delimiter or to the end of the string and the substring. Skips
     * delimiters at the beginning (if desired) and at the end of the string.
     *
     * @param string $string string to atomize
     * @param string $delimiter delimiter to use
     * @param bool $trim skip delimiters at the beginning
     * @return array {'pos','len','sub'} of the atomized string
     */
    protected function _atomizeString($string, $delimiter, $trim = true)
    {
        if (strlen($string) == 0) return array(array('pos' => 0, 'len' => 0, 'sub' => ''));

        $result = array();
        $pos1 = 0;
        $pos2 = strpos($string, $delimiter);
        if ($trim && $pos2 === 0) {
            do {
                $pos1 = $pos2 + strlen($delimiter);
                $pos2 = strpos($string, $delimiter, $pos1);
            } while ($pos1 == $pos2);
        }

        while ($pos2 !== false) {
            $result[] = array('pos' => $pos1, 'len' => $pos2 - $pos1, 'sub' => substr($string, $pos1, $pos2 - $pos1));
            do {
                $pos1 = $pos2 + strlen($delimiter);
                $pos2 = strpos($string, $delimiter, $pos1);
            } while ($pos1 == $pos2);
        }

        if ($pos1 < strlen($string)) {
            $result[] = array('pos' => $pos1, 'len' => strlen($string) - $pos1, 'sub' => substr($string, $pos1, strlen($string) - $pos1));
        }

        return $result;
    }

    /**
     * checks if a given text is equal with one item of an array
     *
     * Tests every array item with the text and returns the array-key
     * if they are euqal. If no match is found it returns "false".
     *
     * @param string $text to be compared with
     * @param array $needle items to compare
     * @return mixed|bool key of found match or false
     */
    protected function _isEqual($text, $needle)
    {
        foreach ($needle as $key => $val) {
            if ($text == $val) return $key;
        }

        return false;
    }

    /**
     * compares a stack of needles with an array and returns the first match
     *
     * Compares each item of the needles array with each 'sub'-item of an atomized
     * string and returns the position of the first match ('pos') and the key
     * of the found needle or false if no match was found.
     *
     * @param array $haystack result of 'atomizeString'
     * @param array $needle stack of needles to search for
     * @return array|bool found match {'pos','key'} or false
     */
    protected function _findInAtomizedArray($haystack, $needle)
    {
        foreach ($haystack as $hayKey => $hayVal) {
            foreach ($needle as $neeKey => $neeVal) {
                if ($neeVal == $hayVal['sub']) return array('pos' => $hayKey, 'key' => $neeKey);
            }
        }

        return false;
    }

    /**
     * Get taxon excludes
     * @return array 
     */
    private function _getTaxonExcludes()
    {
        return array('aff', 'aff.', 'cf', 'cf.', 'cv', 'cv.', 'agg', 'agg.', 'sect', 'sect.', 'ser', 'ser.', 'grex');
    }

    /**
     * Get taxon rank tokens
     * @return array 
     */
    private function _getTaxonRankTokens()
    {
        return array('1a' => 'subsp.',  '1b' => 'subsp',
                                        '2a' => 'var.',    '2b' => 'var',
                                        '3a' => 'subvar.', '3b' => 'subvar',
                                        '4a' => 'forma',
                                        '5a' => 'subf.',   '5b' => 'subf',   '5c' => 'subforma');
    }

}