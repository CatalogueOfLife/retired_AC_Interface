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
class ACI_Form_Dojo_SearchScientific extends Zend_Dojo_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAttribs(array('id' => 'searchScientificForm'));
        
        $translator = Zend_Registry::get('Zend_Translate');
        
        $ranks = array(
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
                        'url' => 'scientific/fetch/' . $rank,
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
        }
        
        $submit = $this->createElement('submit', 'search')
            ->setLabel($translator->translate('Search') . ' >>');
        
        $this->addElement($submit);
        
        $this->addDisplayGroup(array('genus'), 'genusGroup');
        $this->addDisplayGroup(array('species'), 'speciesGroup');
        $this->addDisplayGroup(array('infraspecies'), 'infraspeciesGroup');
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