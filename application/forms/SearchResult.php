<?php
class AC_Form_SearchResult extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $translator = Zend_Registry::get('Zend_Translate');

        //TODO: Create this line "Show <input text> records per page <input submit>"
        
        $items = $this->createElement('text', 'items')->setRequired(true);
        $items->setLabel($translator->translate('Show'));
                
        // Add elements to form:
        $this->addElement($items)
             ->addelement($this->createElement('hidden', 'key'))
             ->addelement($this->createElement('hidden', 'match'))
             ->addelement(
                 $this->createElement(
                     'submit', $translator->translate('Update')
                 )
             );
    }
}