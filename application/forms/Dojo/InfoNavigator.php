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
        $this->setMethod(Zend_Form::METHOD_POST);
        $translator = Zend_Registry::get('Zend_Translate');
        $pages = $this->createElement('Select', 'page');
        $next = $this->createElement('Button', 'next')
            ->setLabel($translator->translate('Next') . ' >>');
        $previous = $this->createElement('Button', 'previous')
            ->setLabel('<< ' . $translator->translate('Previous'));
        $this->addElement($previous)->addElement($pages)->addElement($next);
    }
}