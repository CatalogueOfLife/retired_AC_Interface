<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_Export
 * Export form (simply a submit button)
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_Export extends Zend_Dojo_Form
{
    public function init ()
    {
        $this->setAttribs(
            array(
                'id' => 'exportForm',
                'name' => 'exportForm'
            )
        );
        $this->setMethod(Zend_Form::METHOD_GET);
        $this->addElement(
            $this->createElement('SubmitButton', 'export')
                ->setLabel('Export_to_CSV')
        );
    }
}