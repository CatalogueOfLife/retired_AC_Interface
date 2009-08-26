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
    public $taxa_id;
    public $family_id;
    public $genus;
    public $species;
    public $infraspecies;
    public $infraspecies_marker;
    public $author;
    public $kingdom;
    public $status_id;
    public $name_code;
    public $hierarchy = array();
    public $synonyms = array();
     
    /**
     * Returns a string for the status what can be translated
     *
     * @param int $id
     * @return string
     */
    public static function getStatus($id)
    {
    	$statuses = array(
		    ACI_Model_Taxa::STATUS_ACCEPTED_NAME => 'ACCEPTED_NAME',
		    ACI_Model_Taxa::STATUS_AMBIGUOUS_SYNONYM => 'AMBIGUOUS_SYNONYM',
		    ACI_Model_Taxa::STATUS_MISAPPLIED_NAME => 'MISAPPLIED_NAME',
		    ACI_Model_Taxa::STATUS_PROVISIONALLY_ACCEPTED_NAME =>
		      'PROVISIONALLY_ACCEPTED_NAME',
		    ACI_Model_Taxa::STATUS_SYNONYM => 'SYNONYM',
		    ACI_Model_Taxa::STATUS_COMMON_NAME => 'COMMON_NAME'
        );
        if(isset($statuses[$id]))
        {
             return $statuses[$id];
        }
   		return '';
    }
    
    /**
     * Returns a string for the rank what can be translated
     *
     * @param int $id
     * @return string
     */
    public static function getRank($id)
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
        if(isset($ranks[$id]))
        {
             return $ranks[$id];
        }
        return '';
    }
    
    public function isAcceptedName()
    {
        return in_array(
            $this->status_id,
            array(
                self::STATUS_ACCEPTED_NAME,
                self::STATUS_PROVISIONALLY_ACCEPTED_NAME
            )
        );
    }
    
    public function isSynonym()
    {
        return $this->status_id == self::STATUS_SYNONYM;
    }
    
    public function hasSynonyms()
    {
        return (bool)count($this->synonyms);
    }
    
    public function __get($name)
    {
        switch($name) {
            case 'name':
                return $this->getAcceptedScientificName();
            break;
        }
        return null;
    }
    
    public function getAcceptedScientificName()
    {
        $this->accepted_scientific_name = '';
        switch($this->kingdom)
        {
            case 'Viruses':
            case 'Subviral agents':
                $this->accepted_scientific_name = $this->species .
                    ($this->infraspecies_marker ?
                        ' ' . $this->infraspecies_marker : '' .
                    ($this->infraspecies ?
                        ' ' . $this->infraspecies : ''));
            break;
            default:
                $this->accepted_scientific_name =
                    '<i>' . $this->genus . ' ' . $this->species . '</i>' .
                    ($this->infraspecies_marker ?
                        ' ' . $this->infraspecies_marker : '' .
                    ($this->infraspecies ?
                        ' <i>' . $this->infraspecies . '</i>' : '' .
                    ($this->author ? ' ' . $this->author : '')));
            break;
        }
        return $this->accepted_scientific_name;
    }
}