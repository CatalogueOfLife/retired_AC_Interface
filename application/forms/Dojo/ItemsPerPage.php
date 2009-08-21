<?php
class ACI_Form_Dojo_ItemsPerPage extends Zend_Dojo_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setName('itemsPerPage');

        $translator = Zend_Registry::get('Zend_Translate');

        $items = $this->createElement(
            'NumberSpinner', 
            'items', 
            array(                
                'places' => 3,
                'min' => 1,
                'max' => 500,
                'required' => true,
                'invalidMessage' => $translator->translate('Invalid_value'),
                'rangeMessage' => $translator->translate('Value_out_of_range')
            ),
            array()
        )
        ->setLabel('Show')
        ->setDescription('records_per_page');        
        
        $submit = $this->createElement(
            'SubmitButton', 
            'submit',
            array(
                'required'   => false,
                'ignore'     => true                
            )
        )->setLabel('Update');
       
        $this->addElement($items)
             ->addElement($this->createElement('hidden', 'key'))
             ->addElement($this->createElement('hidden', 'match'))
             ->addElement($submit);
    }
}