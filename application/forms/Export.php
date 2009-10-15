<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Export
 * Export form (simply a submit button)
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Export extends Zend_Form
{
    public function init ()
    {
        $this->setAttribs(
            array(
                'id' => 'exportForm',
                'name' => 'exportForm'
            )
        );
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->addElement(
            $this->createElement('submit', 'export')->setLabel('Export_to_file')
        );
    }
}