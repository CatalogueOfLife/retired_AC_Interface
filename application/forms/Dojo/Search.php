<?php
class ACI_Form_Dojo_Search extends Zend_Dojo_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setName('searchForm');

        $translator = Zend_Registry::get('Zend_Translate');
        
        $key = $this->createElement(
            'validationTextBox', 
            'key', 
            array(                
                'required' => true,
                'filters' => array(
                    'StringTrim'
                ), 
                'regExp' => '.{2,150}',                             
                'invalidMessage' => $translator->translate('At_least_two_chars')                
            )
        )->setLabel('Search_for');
        
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
       
        $this->addElement($key)
             ->addElement($match)             
             ->addelement($submit);
    }
}