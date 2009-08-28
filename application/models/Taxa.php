<?php
class ACI_Model_Taxa
{
    const STATUS_ACCEPTED_NAME = 1;
    const STATUS_AMBIGUOUS_SYNONYM = 2;
    const STATUS_MISAPPLIED_NAME = 3;
    const STATUS_PROVISIONALLY_ACCEPTED_NAME = 4;
    const STATUS_SYNONYM = 5;
    const STATUS_COMMON_NAME = 6;
    
    const RANK_KINGDOM = 1;
    const RANK_PHYLUM = 2;
    const RANK_CLASS = 3;
    const RANK_ORDER = 4;
    const RANK_SUPERFAMILY = 5;
    const RANK_FAMILY = 6;
    const RANK_GENUS = 7;
    const RANK_SPECIES = 8;
    const RANK_INFRASPECIES = 9;
    
    public $id;
    public $genus;
    public $species;
    public $infraspecies;
    public $infraspecies_marker;
    public $is_accepted_name;
    public $author;
    public $status;
    public $specialist_name;
    public $lsid;
    public $comment;
    public $taxa_id;
    public $taxa_name;
    public $taxa_author;   //synonyms only
    public $taxa_language; //common names only
    public $taxa_status;
    public $db_id;
    public $db_name;
    public $db_image;
    public $hierarchy = array();
    public $synonyms = array();
    public $common_names = array();
    public $distributions = array();
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
            ACI_Model_Taxa::STATUS_ACCEPTED_NAME =>
                'STATUS_ACCEPTED_NAME',
            ACI_Model_Taxa::STATUS_AMBIGUOUS_SYNONYM => $phrased ?
                'STATUS_AMBIGUOUS_SYNONYM_FOR' : 'STATUS_AMBIGUOUS_SYNONYM',
            ACI_Model_Taxa::STATUS_MISAPPLIED_NAME => $phrased ?
                'STATUS_MISAPPLIED_NAME_FOR' : 'STATUS_MISAPPLIED_NAME',
            ACI_Model_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME =>
                'STATUS_PROVISIONALLY_ACCEPTED_NAME',
            ACI_Model_Taxa::STATUS_SYNONYM => $phrased ?
                'STATUS_SYNONYM_FOR' : 'STATUS_SYNONYM',
            ACI_Model_Taxa::STATUS_COMMON_NAME => $phrased ?
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
        $ranks = array(
            ACI_Model_Taxa::RANK_KINGDOM => 'RANK_KINGDOM',
            ACI_Model_Taxa::RANK_PHYLUM => 'RANK_PHYLUM',
            ACI_Model_Taxa::RANK_CLASS => 'RANK_CLASS',
            ACI_Model_Taxa::RANK_ORDER => 'RANK_ORDER',
            ACI_Model_Taxa::RANK_SUPERFAMILY => 'RANK_SUPERFAMILY',
            ACI_Model_Taxa::RANK_FAMILY => 'RANK_FAMILY',
            ACI_Model_Taxa::RANK_GENUS => 'RANK_GENUS',
            ACI_Model_Taxa::RANK_SPECIES => 'RANK_SPECIES',
            ACI_Model_Taxa::RANK_INFRASPECIES => 'RANK_INFRASPECIES'
        );
        return isset($ranks[$id]) ? $ranks[$id] : '';
    }
    
    public function hasSynonyms()
    {
        return (bool)count($this->synonyms);
    }
        
    public function hasCommonNames()
    {
        return (bool)count($this->common_names);
    }
    
    public function __get($name)
    {
        switch($name) {
            case 'name':
                return $this->getAcceptedScientificName();
            break;
            case 'taxa_full_name':
                return $this->getTaxaFullName();
            break;
        }
        return null;
    }
    
    public function getTaxaFullName()
    {
        if(!$this->taxa_status) {
            return '';
        }
        $this->taxa_full_name = '<i>' . $this->taxa_name . '</i>';
        switch($this->taxa_status) {
            case ACI_Model_Taxa::STATUS_COMMON_NAME:
                $this->taxa_full_name .= ' (' . $this->taxa_language . ')';
            break;
            default:
                $this->taxa_full_name .= ' ' . $this->taxa_author;
            break;
        }
        return $this->taxa_full_name;
    }
    
    public function getAcceptedScientificName()
    {
        $this->name = '';
        switch($this->kingdom)
        {
            case 'Viruses':
            case 'Subviral agents':
                $this->name = $this->species .
                    ($this->infraspecies_marker ?
                        ' ' . $this->infraspecies_marker : '' .
                    ($this->infraspecies ?
                        ' ' . $this->infraspecies : ''));
            break;
            default:
                $this->name =
                    '<i>' . $this->genus . ' ' . $this->species . '</i>' .
                    ($this->infraspecies_marker ?
                        ' ' . $this->infraspecies_marker : '' .
                    ($this->infraspecies ?
                        ' <i>' . $this->infraspecies . '</i>' : '' .
                    ($this->author ? ' ' . $this->author : '')));
            break;
        }
        return $this->name;
    }
}