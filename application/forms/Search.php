<?php
class AC_Form_Search extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $translator = Zend_Registry::get('Zend_Translate');
        
        $key = $this->createElement('text', 'key')->setRequired(true);
        $key->setLabel($translator->translate('Search_for'));
        
        $match = $this->createElement('checkbox', 'match')->setValue('1');
        $match->setLabel($translator->translate('Match_whole_words_only'));
        
        // Add elements to form:
        $this->addElement($key)
             ->addElement($match)
             ->addelement(
                 $this->createElement(
                     'submit', $translator->translate('Search')
                 )
             );
    }
}