<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_ItemsPerPage
 * Items per page dojo-enabled form
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_ItemsPerPage extends Zend_Dojo_Form
{
    public function init()
    {
        $this->setAttribs(
            array(
                'id' => 'itemsPerPage',
                'name' => 'itemsPerPage'
            )
        );
        
        $this->setMethod(Zend_Form::METHOD_GET);

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
                'style' => 'width: 70px',
                'maxLength' => 3
            ),
            array()
        )
        ->setLabel($translator->translate('Records_per_page') . ':');
        
        $submit = $this->createElement(
            'SubmitButton',
            'update',
            array(
                'required'   => false,
                'ignore'     => true
            )
        )->setLabel('Update');
       
        $this->addElement($items)->addElement($submit);
             
        $this->addDisplayGroup(array('items', 'update'), 'itemsGroup');
        
        $this->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag' => 'div', 'class' => 'items-form')),
                    'Form'
            )
        );
    }
}