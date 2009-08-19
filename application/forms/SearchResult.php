<?php
class AC_Form_SearchResult extends Zend_Form
{
    public function init()
    {
        $this->setAction('');
        $this->setMethod('post');

        $translator = Zend_Registry::get('Zend_Translate');

        //TODO: Create this line "Show <input text> records per page <input submit>"
        
        $show = $this->createElement('text','show');
        $show->setRequired(true);
        $show->setValue('10');
        $show->setLabel($translator->translate('Show'));

        $key = $this->createElement('hidden','key');

        $match = $this->createElement('hidden','match');
        
        $submit = $this->createElement('submit',$translator->translate('Update'));
//        $submit->setLabel($translator->translate('records_per_page'));
        
        // Add elements to form:
        $this->addElement($show)
             ->addelement($key)
             ->addelement($match)
             ->addelement($submit);
    }
}