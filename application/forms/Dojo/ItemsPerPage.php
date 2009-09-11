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
                'max' => 999,
                'required' => true,
                'invalidMessage' => $translator->translate('Invalid_value'),
                'rangeMessage' => $translator->translate('Value_out_of_range'),
                'style' => 'width: 80px',
                'maxLength' => 3
            ),
            array()
        )
        ->setLabel($translator->translate('Records_per_page') . ':');
        
        $submit = $this->createElement(
            'submit',
            'update',
            array(
                'required'   => false,
                'ignore'     => true
            )
        )->setLabel('Update');
       
        $this->addElement($items)
             ->addElement($this->createElement('hidden', 'key'))
             ->addElement($this->createElement('hidden', 'match'))
             ->addElement($submit);
             
        $this->addDisplayGroup(array('items', 'update'), 'itemsGroup');
        
        $this->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag' => 'div', 'class' => 'items_form')),
                    'Form'
            )
        );
    }
}