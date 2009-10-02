<?php
/**
 * Annual Checklist Interface
 *
 * Abstract class ACI_Form_Dojo_MultiCombo
 * Multi combo dojo-enabled form model
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
abstract class ACI_Form_Dojo_MultiCombo extends Zend_Dojo_Form
{
    protected $_combos;
    
    public function init()
    {
        foreach ($this->_combos as $comboId => $comboLabel) {
             
            $comboBox = $this->createElement(
                'ComboBox',
                $comboId,
                array(
                    'required' => false,
                    'autoComplete' => false,
                    'labelType' => 'html',
                    'labelAttr' => 'label',
                    'storeId' => $comboId . 'Store',
                    'storeType' => 'ACI.dojo.TxReadStore',
                    'storeParams' => array(
                        'url' => 'scientific/fetch/' . $comboId
                    ),
                    'dijitParams' => array(
                        'searchAttr' => 'name',
                        'hasDownArrow'   => true,
                        'highlightMatch' => 'none',
                        'queryExpr' => '*${0}*',
                        'searchAttr' => 'name',
                        'searchDelay' => 500,
                        'onChange' => 'updateParams'
                    ),
                    'style' => 'width: 300px'
                )
            )->setLabel($comboLabel);
            
            $this->addElement($comboBox);
            $this->addDisplayGroup(
                array($comboId),
                $comboId . 'Group',
                array('class' => 'searchGroup')
            );
        }
        
        $this->addElement(
            $this->createElement('hidden', 'key')
                 ->setValue(
                      Zend_Json::encode(
                          array_fill_keys(array_keys($this->_combos), '')
        )));
        
        $translator = Zend_Registry::get('Zend_Translate');
        
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