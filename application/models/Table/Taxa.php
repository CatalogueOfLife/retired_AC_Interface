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
    
    public static $markers =
        array(
            'subsp.',
            'var.',
            'forma',
            'ab.',
            'm.',
            'nothosubsp.',
            'nothovar.',
            'subvar.',
            'lusus',
            'race',
            'subforma',
            'nothof.',
            'col. var.',
            'prol.',
            'convar.',
            'mut.',
            'sublus.',
            'monstr.',
            'notst',
            'convar',
            'provar'
        );
    
    public $id;
    public $kingdom;
    public $genus;
    public $species;
    public $familyId;
    public $infra;
    public $infraMarker;
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
    public $taxaAuthor;   //synonyms only
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
     
    /**
     * Returns a string for the status what can be translated
     *
     * @param int $id
     * @param bool $phrased
     * @return string
     */
    public static function getStatusString($id, $phrased = true)
    {
        $statuses = array(
            self::STATUS_ACCEPTED_NAME =>
                'STATUS_ACCEPTED_NAME',
            self::STATUS_AMBIGUOUS_SYNONYM => $phrased ?
                'STATUS_AMBIGUOUS_SYNONYM_FOR' : 'STATUS_AMBIGUOUS_SYNONYM',
            self::STATUS_MISAPPLIED_NAME => $phrased ?
                'STATUS_MISAPPLIED_NAME_FOR' : 'STATUS_MISAPPLIED_NAME',
            self::STATUS_PROVISIONALLY_ACCEPTED_NAME =>
                'STATUS_PROVISIONALLY_ACCEPTED_NAME',
            self::STATUS_SYNONYM => $phrased ?
                'STATUS_SYNONYM_FOR' : 'STATUS_SYNONYM',
            self::STATUS_COMMON_NAME => $phrased ?
                'STATUS_COMMON_NAME_FOR' : 'STATUS_COMMON_NAME'
        );
        return isset($statuses[$id]) ? $statuses[$id] : '';
    }
    
    /**
     * Returns a string for the rank what can be translated
     *
     * @param int $id
     * @return string
     */
    public static function getRankString($id)
    {
        $ranks = self::getRanks();
        return isset($ranks[$id]) ? $ranks[$id] : '';
    }
    
    public static function getRanks()
    {
        $ranks = array(
            self::RANK_KINGDOM => 'RANK_KINGDOM',
            self::RANK_PHYLUM => 'RANK_PHYLUM',
            self::RANK_CLASS => 'RANK_CLASS',
            self::RANK_ORDER => 'RANK_ORDER',
            self::RANK_SUPERFAMILY => 'RANK_SUPERFAMILY',
            self::RANK_FAMILY => 'RANK_FAMILY',
            self::RANK_GENUS => 'RANK_GENUS',
            self::RANK_SPECIES => 'RANK_SPECIES',
            self::RANK_INFRASPECIES => 'RANK_INFRASPECIES'
        );
        return $ranks;
    }
    
    public function hasSynonyms()
    {
        return (bool)count($this->synonyms);
    }
        
    public function hasCommonNames()
    {
        return (bool)count($this->commonNames);
    }
    
    public function __get($name)
    {
        switch ($name) {
            case 'name':
                $this->name =
                    self::getAcceptedScientificName(
                        $this->genus,
                        $this->species,
                        $this->infra,
                        $this->rank,
                        $this->author
                    );
                return $this->name;
                break;
            case 'taxaFullName':
                $this->taxaFullName =
                    self::getTaxaFullName(
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
    
    public static function getTaxaFullName($name, $status, $author, $language)
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
                $taxaFullName = '<i>' . $name . '</i>';
                if ($author) {
                    $taxaFullName .= ' ' . $author;
                }
                break;
        }
        return $taxaFullName;
    }
    
    public static function isSynonym($status = null)
    {
        $synonymStatuses = array(
           self::STATUS_SYNONYM,
           self::STATUS_AMBIGUOUS_SYNONYM
        );
        $status = is_null($status) ? $this->status : (int)$status;
        return in_array($status, $synonymStatuses);
    }
    
    public static function isAcceptedName($status = null)
    {
        $anStatuses = array(
           self::STATUS_ACCEPTED_NAME,
           self::STATUS_PROVISIONALLY_ACCEPTED_NAME
        );
        $status = is_null($status) ? $this->status : (int)$status;
        return in_array($status, $anStatuses);
    }
    
    public static function getAcceptedScientificName($genus, $species,
        $infraspecies, $rank, $author)
    {
        $name  = "<i>$genus $species";
        if ($infraspecies) {
            if (self::getInfraSpecificMarker($rank)) {
                $name .= "</i> ".self::getInfraSpecificMarker($rank)." <i>$infraspecies";
            } else {
                $name .= " $infraspecies";
            }
        }
        $name .= '</i>';
        $name .= $author ? " $author" : '';
        return $name;
    }
    
    public static function getInfraSpecificMarker($rank)
    {
        switch ($rank) {
            case 19://form
                return 'form';
                break;
            case 49://infraspecies
                return 'infrasp.';
                break;
            case 104://subspecies
                return 'subsp.';
                break;
            case 129://variety
                return 'var.';
                break;
            default:
                return false;
                break;
        }
    }
    
    public function __set($key, $value)
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
        } else {
            Zend_Registry::get('logger')->debug("UNDEFINED attribute $key");
        }
        $this->$key = $value;
    }
}