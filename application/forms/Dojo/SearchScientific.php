<?php
class ACI_Form_Dojo_SearchScientific extends Zend_Dojo_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setName('searchFormBox');
        
        $ranks = array(
            'genus' => 'Genus', 
            'species' => 'Species', 
            'infraspecies' => 'Infraspecies'
        );
        
        foreach($ranks as $rank => $label) {
             
            $comboBox = $this->createElement(
                'ComboBox',
                $rank,
                array(
                    'autocomplete' => true,
                    'required' => false,
                    'storeId' => 'genusStore',
                    'storeType' => 'dojox.data.QueryReadStore',
                    'storeParams' => array(
                        'url' => 'scientific/fetch/' . $rank,
                    ),
                    'dijitParams' => array(
                        'searchAttr' => 'name',
                    )
                )
            )->setLabel($label);
            
            $this->addElement($comboBox);
        }
        
        $match = $this->createElement(
            'CheckBox',
            'match',
            array(
                'checked' => 'true'
            )
        )->setLabel('Match_whole_words_only');
        
        $submit = $this->createElement(
            'SubmitButton', 
            'submit',
            array(
                'required'   => false,
                'ignore'     => true                
            )
        )->setLabel('Search');
        
        $this->addElement($match)
             ->addElement($submit);
    }
}