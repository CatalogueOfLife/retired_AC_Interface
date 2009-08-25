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
    public $family_id;
    public $genus;
    public $species;
    public $infraspecies;
    public $infraspecies_marker;
    public $author;
    public $kingdom;
    public $status_id;
    public $name_code;
    public $synonyms = array();
    public $accepted_name;
     
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
            case 'accepted_name_code':
                if($this->isAcceptedName()) {
                    return $this->name_code;
                }
                return $this->accepted_name['name_code'];
            break;
            case 'accepted_scientific_name':
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
                if($this->isAcceptedName()) {
                    $this->accepted_scientific_name = $this->species .
                        ($this->infraspecies_marker ?
                            ' ' . $this->infraspecies_marker : '' .
                        ($this->infraspecies ?
                            ' ' . $this->infraspecies : ''));
                }
                else {
                    $this->accepted_scientific_name =
                        $this->accepted_name['species'] .
                        ($this->accepted_name['infraspecies_marker'] ?
                            ' ' . $this->accepted_name['infraspecies_marker'] :
                            '' .
                        ($this->accepted_name['infraspecies'] ?
                            ' ' . $this->accepted_name['infraspecies'] : ''));
                }
            break;
            default:
                if($this->isAcceptedName()) {
                    $this->accepted_scientific_name =
                        '<i>' . $this->genus . ' ' . $this->species . '</i>' .
                        ($this->infraspecies_marker ?
                            ' ' . $this->infraspecies_marker : '' .
                        ($this->infraspecies ?
                            ' <i>' . $this->infraspecies . '</i>' : '' .
                        ($this->author ? ' ' . $this->author : '')));
                }
                else {
                    $this->accepted_scientific_name =
                        '<i>' . $this->accepted_name['genus'] . ' ' .
                        $this->accepted_name['species'] . '</i>' .
                        ($this->accepted_name['infraspecies_marker'] ?
                            ' ' . $this->accepted_name['infraspecies_marker'] :
                            '' .
                        ($this->accepted_name['infraspecies'] ?
                            ' <i>' . $this->accepted_name['infraspecies'] .
                            '</i>' : '' .
                        ($this->accepted_name['author'] ?
                            ' ' . $this->accepted_name['author'] : '')));
                }
            break;
        }
        return $this->accepted_scientific_name;
    }
}