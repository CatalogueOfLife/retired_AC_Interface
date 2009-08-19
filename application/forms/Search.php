<?php
class AC_Form_Search extends Zend_Form
{
    public function init()
    {
        $this->setAction('');
        $this->setMethod('post');

        $translator = Zend_Registry::get('Zend_Translate');
        
        $searchfield = $this->createElement('text','key');
        $searchfield->setRequired(true);
        $searchfield->setLabel($translator->translate('Search_for'));
        
        $match_whole_words = $this->createElement('checkbox','match');
        $match_whole_words->setValue('1');
        $match_whole_words->setLabel($translator->translate('Match_whole_words_only'));
        
        $submit = $this->createElement('submit',$translator->translate('Search'));
        
        // Add elements to form:
        $this->addElement($searchfield)
             ->addElement($match_whole_words)
             ->addelement($submit);
    }
}