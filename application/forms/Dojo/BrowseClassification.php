<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_BrowseClassification
 * Browse taxonomic classsification dojo-enabled form
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_BrowseClassification extends Zend_Dojo_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAttribs(array('id' => 'searchScientificForm'));
        
        $translator = Zend_Registry::get('Zend_Translate');
        
        $ranks = array(
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
        
        foreach ($ranks as $rank => $label) {
             
            $comboBox = $this->createElement(
                'ComboBox',
                $rank,
                array(
                    'required' => false,
                    'autoComplete' => false,
                    'labelType' => 'html',
                    'labelAttr' => 'label',
                    'storeId' => $rank . 'Store',
                    'storeType' => 'dojox.data.QueryReadStore',
                    'storeParams' => array(
                        'url' => 'classification/fetch/' . $rank,
                    ),
                    'dijitParams' => array(
                        'searchAttr' => 'name',
                        'hasDownArrow'   => true,
                        'highlightMatch' => 'none', //highlight is done manually
                        'queryExpr' => '*${0}*',
                        'searchAttr' => 'name'
                    ),
                    'style' => 'width: 300px'
                )
            )->setLabel($label);
            
            $this->addElement($comboBox);
            $this->addDisplayGroup(
                array($rank), 
                $rank . 'Group', 
                array('class' => 'searchGroup')
            );
        }
        
        $submit = $this->createElement('submit', 'search')
            ->setLabel($translator->translate('Search') . ' >>');
        
        $this->addElement($submit);
        $this->addDisplayGroup(array('search'), 'submitGroup');
        
        $this->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag' => 'div', 'class' => 'search-form')),
                    'Form'
            )
        );
    }
}