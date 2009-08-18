<?php
class AC_FormScientific_Search extends Zend_Form
{
    public function init()
    {
        $this->setAction('');
        $this->setMethod('post');

        $genus        = $this->createElement('text','search_genus');
        $species      = $this->createElement('text','search_species');
        $infraspecies = $this->createElement('text','search_infraspecies');
        
        $match_whole_words = $this->createElement('checkbox','whole_words');
        $match_whole_words->setValue('1');

        $translator = Zend_Registry::get('translator');
        $clear = $this->createElement('reset',$translator->translate('Clear_form'));
        $submit = $this->createElement('submit',$translator->translate('Search'));
        
        // Add elements to form:
        $this->addElement($genus)
             ->addElement($species)
             ->addElement($infraspecies)
             ->addElement($match_whole_words)
             ->addElement($clear)
             ->addElement($submit);
    }
}