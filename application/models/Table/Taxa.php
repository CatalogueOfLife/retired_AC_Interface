<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_Taxa
 * Species data storage model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_Taxa
{
    const STATUS_ACCEPTED_NAME = 1;
    const STATUS_AMBIGUOUS_SYNONYM = 2;
    const STATUS_MISAPPLIED_NAME = 3;
    const STATUS_PROVISIONALLY_ACCEPTED_NAME = 4;
    const STATUS_SYNONYM = 5;
    const STATUS_COMMON_NAME = 6;

    const RANK_KINGDOM = 54;
    const RANK_PHYLUM = 76;
    const RANK_CLASS = 6;
    const RANK_ORDER = 72;
    const RANK_SUPERFAMILY = 112;
    const RANK_FAMILY = 17;
    const RANK_GENUS = 20;
    const RANK_SUBGENUS = 96;
    const RANK_SPECIES = 83;
    const RANK_INFRASPECIES = 49;

    const POINT_OF_ATTACHMENT_TOP = 'CoL'; // Top level in Species details > Classification > Point of attachment

    //TODO: get this dynamically.
    public static $markers = array(
        'ab.',
        'agsp.',
        'agvar.',
        'col. var.',
        'f.',
        'lusus',
        'm.',
        'microgene',
        'nm.',
        'nothof.',
        'nothosp.',
        'nothosubsp.',
        'nothovar.',
        'notst',
        'provar',
        'status',
        'staxon',
        'strain',
        'subsp.',
        'subtaxon',
        'subvar.',
        'var.'
    );

    public $id;
    public $kingdom;
    public $phylum;
    public $class;
    public $order;
    public $superfamily;
    public $family;
    public $genus;
    public $subgenus;
    public $species;
    public $familyId;
    public $infra;
    public $kingdom_id;
    public $phylum_id;
    public $class_id;
    public $order_id;
    public $superfamily_id;
    public $family_id;
    public $genus_id;
    public $subgenus_id;
    public $species_id;
    public $infra_id;
    public $kingdom_lsid;
    public $phylum_lsid;
    public $class_lsid;
    public $order_lsid;
    public $superfamily_lsid;
    public $family_lsid;
    public $genus_lsid;
    public $subgenus_lsid;
    public $species_lsid;
    public $infra_lsid;
    public $infraspecific_marker;
    public $isAcceptedName;
    public $nameCode;
    public $acceptedNameCode;
    public $author;
    public $status;
    public $specialistName;
    public $lsid;
    public $comment;
    public $taxaId;
    public $taxaName;
    public $taxaAuthor; //synonyms only
    public $taxaLanguage; //common names only
    public $taxaStatus;
    public $snTaxaId;
    public $rank;
    public $dbId;
    public $dbName;
    public $dbImage;
    public $dbVersion;
    public $webSite;
    public $urls = array();
    public $scrutinyDate;
    public $hierarchy = array();
    public $synonyms = array();
    public $infraspecies = array();
    public $commonNames = array();
    public $distribution = array();
    public $references = array();
    public $lifezones = array();

    // Added in 1.7
    public $pointOfAttachmentId;
    public $pointOfAttachment = self::POINT_OF_ATTACHMENT_TOP;
    public $pointOfAttachmentLinkId = 0;
    public $images = array();
    public $dbCoverage = '';
    public $dbCompleteness = 0;
    public $dbConfidence = 0;
    public $dbCoverageIcon;
    public $dbConfidenceIcons = array();

    // Added in 1.8
    public $distributionString;

    // Added in 1.10
    public $has_preholocene;
    public $has_modern;
    public $is_extinct;

    /**
     * Returns a string for the status what can be translated
     *
     * @param int $id
     * @param bool $phrased
     * @return string
     */
    public static function getStatusString ($id, $phrased = true)
    {
        $statuses = array(
            self::STATUS_ACCEPTED_NAME => 'STATUS_ACCEPTED_NAME',
            self::STATUS_AMBIGUOUS_SYNONYM => $phrased ? 'STATUS_AMBIGUOUS_SYNONYM_FOR' : 'STATUS_AMBIGUOUS_SYNONYM',
            self::STATUS_MISAPPLIED_NAME => $phrased ? 'STATUS_MISAPPLIED_NAME_FOR' : 'STATUS_MISAPPLIED_NAME',
            self::STATUS_PROVISIONALLY_ACCEPTED_NAME => 'STATUS_PROVISIONALLY_ACCEPTED_NAME',
            self::STATUS_SYNONYM => $phrased ? 'STATUS_SYNONYM_FOR' : 'STATUS_SYNONYM',
            self::STATUS_COMMON_NAME => $phrased ? 'STATUS_COMMON_NAME_FOR' : 'STATUS_COMMON_NAME'
        );
        return isset($statuses[$id]) ? $statuses[$id] : '';
    }

    /**
     * Returns a string for the rank what can be translated
     *
     * @param int $id
     * @return string
     */
    public static function getRankString ($id)
    {
        $ranks = self::getRanks();
        return isset($ranks[$id]) ? $ranks[$id] : '';
    }

    public static function getRanks ()
    {
        $ranks = array(
            self::RANK_KINGDOM => 'RANK_KINGDOM',
            self::RANK_PHYLUM => 'RANK_PHYLUM',
            self::RANK_CLASS => 'RANK_CLASS',
            self::RANK_ORDER => 'RANK_ORDER',
            self::RANK_SUPERFAMILY => 'RANK_SUPERFAMILY',
            self::RANK_FAMILY => 'RANK_FAMILY',
            self::RANK_GENUS => 'RANK_GENUS',
            self::RANK_SUBGENUS => 'RANK_SUBGENUS',
        	self::RANK_SPECIES => 'RANK_SPECIES',
            self::RANK_INFRASPECIES => 'RANK_INFRASPECIES'
        );
        return $ranks;
    }

	private static $_ranksDescending = array (
			self::RANK_KINGDOM,
			self::RANK_PHYLUM,
			self::RANK_CLASS,
			self::RANK_ORDER,
			self::RANK_SUPERFAMILY,
			self::RANK_FAMILY,
			self::RANK_GENUS,
			self::RANK_SUBGENUS,
			self::RANK_SPECIES
	);

	/**
	 * Returns 0 if the specified ranks are equal; -1 if the first rank is
	 * lower than the second; +1 if the first rank is higher than the second.
	 * Infra-specific ranks are all considered equal.
	 *
	 * @param int $rankId1 The ID of the 1st rank (e.g. 83 for species)
	 * @param int $rankId2 The ID of the 2nd rank
	 */
	public static function compareRanks($rankId1, $rankId2) {
		$rank1 = array_search($rankId1,self::$_ranksDescending);
		$rank2 = array_search($rankId2,self::$_ranksDescending);

		if($rank1 === $rank2) {
			return 0;
		}
		if($rank1 === false) {
			// $rankdId1 not found in self::$_ranksDescending - must be some infra-specific rank
			return -1;
		}
		if($rank1 === false) {
			// $rankdId2 not found in self::$_ranksDescending - must be some infra-specific rank
			return 1;
		}

		// tricky: higher ranks come first in self::$_ranksDescending
		return $rank1 < $rank2 ? 1 : -1;
	}

	// For the lazy
	public static function isRank1HigherThanRank2($rankId1, $rankId2) {
		return self::compareRanks($rankId1, $rankId2) === 1;
	}

	// For the lazy
	public static function isRank1LowerThanRank2($rankId1, $rankId2) {
		return self::compareRanks($rankId1, $rankId2) === -1;
	}

	public static function isRankHigherThanSpecies($rankId) {
		return self::compareRanks($rankId, self::RANK_SPECIES) === 1;
	}

	public function hasSynonyms ()
    {
        return (bool) count($this->synonyms);
    }

    public function hasCommonNames ()
    {
        return (bool) count($this->commonNames);
    }

    public function __get ($name)
    {
        switch ($name) {
            case 'name':
                $this->name = self::getAcceptedScientificName(
                    $this->genus,
                    isset($this->subgenus) ? $this->subgenus : null,
                    $this->species,
                    $this->infra,
                    $this->rank,
                    $this->author,
                    $this->infraspecific_marker
                );
                return $this->name;
                break;
            case 'taxaFullName':
                $this->taxaFullName = self::getTaxaFullName(
                    $this->taxaName,
                    $this->taxaStatus,
                    $this->taxaAuthor,
                    $this->taxaLanguage
                );
                return $this->taxaFullName;
                break;
        }
        return null;
    }

    public static function getTaxaFullName ($name, $status, $author, $language)
    {
        if (!$status) {
            return '';
        }
        switch ($status) {
            case self::STATUS_COMMON_NAME:
                $taxaFullName = $name;
                if ($language) {
                    $taxaFullName .= ' (' . $language . ')';
                }
                break;
            default:
                $taxaFullName = self::italicizeName(ucfirst($name));
                if ($author) {
                    $taxaFullName .= ' ' . $author;
                }
                break;
        }
        return $taxaFullName;
    }

    public static function isSynonym ($status = null)
    {
        $synonymStatuses = array(
            self::STATUS_SYNONYM,
            self::STATUS_AMBIGUOUS_SYNONYM,
            self::STATUS_MISAPPLIED_NAME
        ); //TODO: self::STATUS_SYNONYM below was $this->status, but get fatal error since it's static
        $status = is_null($status) ? self::STATUS_SYNONYM : (int) $status;
        return in_array($status, $synonymStatuses);
    }

    public static function isAcceptedName ($status = null)
    {
        $anStatuses = array(
            self::STATUS_ACCEPTED_NAME,
            self::STATUS_PROVISIONALLY_ACCEPTED_NAME
        );
        $status = is_null($status) ? $this->status : (int) $status;
        return in_array($status, $anStatuses);
    }

    public static function getAcceptedScientificName ($genus, $subgenus, $species,
        $infraspecies, $rank, $author, $marker = '', $kingdom = '')
    {
        $name = "<i>" . ucfirst($genus) . ' ' .
            (!empty($subgenus) ? '(' . ucfirst($subgenus) . ') ' : '') .
            $species;
        if ($infraspecies) {
            if ($marker && strtolower($kingdom) != 'animalia') {
                $name .= "</i> " . $marker . " <i>$infraspecies";
            }
            else {
                $name .= " $infraspecies";
            }
        }
        $name .= '</i>';
        $name .= $author ? " $author" : '';
        return $name;
    }

    public static function italicizeName ($name)
    {
        $name = '<i>' . $name . '</i>';
        foreach (self::$markers as $marker) {
            if (strstr($name, $marker) !== FALSE) {
                $name = str_replace($marker, '</i>' . $marker . '<i>', $name);
            }
        }
        return $name;
    }

    public static function getInfraSpecificMarker ($rank)
    {
        switch ($rank) {
            case 19: //form
                return 'form';
                break;
            case 49: //infraspecies
                return 'infrasp.';
                break;
            case 104: //subspecies
                return 'subsp.';
                break;
            case 129: //variety
                return 'var.';
                break;
            default:
                return false;
                break;
        }
    }

    public function __set ($key, $value)
    {
        if (strpos($key, '_')) {
            $nameParts = explode('_', $key);
            $key = '';
            $i = 0;
            foreach ($nameParts as $part) {
                $part = strtolower($part);
                if ($i > 0) {
                    $part = ucfirst($part);
                }
                $key .= $part;
                $i++;
            }
        }
        else {
            Zend_Registry::get('logger')->debug("UNDEFINED attribute $key");
        }
        $this->$key = $value;
    }
}