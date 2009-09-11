<?php
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
        
        foreach($ranks as $rank => $label) {
             
            $comboBox = $this->createElement(
                'ComboBox',
                $rank,
                array(
                    'required' => false,
                    'autoComplete' => true,
                    'labelType' => 'html',
                    'labelAttr' => 'highlightedName',
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
        
        $submit = $this->createElement(
            'submit',
            'search')->setLabel($translator->translate('Search') . ' >>');
        
        $this->addElement($submit);
    }
}