<?php
class AC_FormScientific_Search extends Zend_Form
{
    public function init()
    {
        $this->setAction('');
        $this->setMethod('post');

        $searchfield = $this->createElement('text','search_genus');
        $searchfield = $this->createElement('text','search_speices');
        $searchfield = $this->createElement('text','search_infraspecies');
        
        $match_whole_words = $this->createElement('checkbox','whole_words');
        $match_whole_words->setValue('1');

        $translator = Zend_Registry::get('translator');
        $submit = $this->createElement('reset',$translator->translate('Clear_form'));
        $submit = $this->createElement('submit',$translator->translate('Search'));
        
        // Add elements to form:
        $this->addElement($searchfield)
             ->addElement($match_whole_words)
             ->addElement($submit);
    }
}