<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_InfoNavigator
 * Navigator for the info pages
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_InfoNavigator extends Zend_Dojo_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_GET);
        $pages = $this->createElement('Select', 'page');
        $next = $this->createElement('Button', 'next', array('onclick', 'javascript:alert("hoi")'))
            ->setLabel('Next');
        $previous = $this->createElement('Button', 'previous')
            ->setLabel('Previous');
        $this->addElement($previous)->addElement($pages)->addElement($next);
    }
}