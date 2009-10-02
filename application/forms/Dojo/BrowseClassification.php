<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_BrowseClassification
 * Browse taxonomic classification dojo-enabled form
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_BrowseClassification extends ACI_Form_Dojo_AMultiCombo
{
    public function init()
    {
        $this->setAttribs(array('id' => 'browseClassificationForm'));
        $this->_combos = array(
            'kingdom' => 'Top_level_group',
            'phylum' => 'Phylum',
            'class' => 'Class',
            'order' => 'Order',
            'superfamily' => 'Superfamily',
            'family' => 'Family',
            'genus' => 'Genus',
            'species' => 'Species',
            'infraspecies' => 'Infraspecies'
        );
        parent::init();
    }
}