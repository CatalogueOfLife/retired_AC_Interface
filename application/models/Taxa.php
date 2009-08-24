<?php
class ACI_Model_Taxa
{
    const ACCEPTED_NAME = 1;
    const AMBIGUOUS_SYNONYM = 2;
    const MISAPPLIED_NAME = 3;
    const PROVISIONALLY_ACCEPTED_NAME = 4;
    const SYNONYM = 5;
    
    public function isAcceptedName()
    {
        return $this->status == 'accepted name';
    }
    
    //TODO: create a method to add accepted name and synonyms as
    // arrays and convert them to Taxa objects before storing
}