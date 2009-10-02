<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_SearchScientific
 * Search for scientific names dojo-enabled form
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_SearchScientific extends ACI_Form_Dojo_AMultiCombo
{
    public function init()
    {
        $this->setAttribs(array('id' => 'searchScientificForm'));
        $this->_combos = array(
            'genus' => 'Genus',
            'species' => 'Species',
            'infraspecies' => 'Infraspecies'
        );
        parent::init();
    }
}