<?php
class AC_Form_Search extends Zend_Form
{
    public function init()
    {
        $this->setAction('');
        $this->setMethod('post');

        $searchfield = $this->createElement('text','search_string');
        $searchfield->setRequired(true);

        $match_whole_words = $this->createElement('checkbox','whole_words');
        $match_whole_words->setValue('1');
        $translator = Zend_Registry::get('translator');
        $submit = $this->createElement('submit',$translator->translate('Search'));
        
        // Add elements to form:
        $this->addElement($searchfield)
             ->addElement($match_whole_words)
             ->addelement($submit);
    }
}