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
                    'storeType' => 'ACI.dojo.TxReadStore',
                    'storeParams' => array(
                        'url' => 'scientific/fetch/' . $rank
                    ),
                    'dijitParams' => array(
                        'searchAttr' => 'name',
                        'hasDownArrow'   => true,
                        'highlightMatch' => 'none', //highlight is done manually
                        'queryExpr' => '*${0}*',
                        'searchAttr' => 'name',
                        'onChange' => 'updateParams'
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
        
        $this->addElement(
            $this->createElement('hidden', 'params')
            ->setValue(
                Zend_Json::encode(
                    array(
                        'genus' => '',
                        'species' => '',
                        'infraspecies' => ''
                    )
                )
            )
        );
        
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