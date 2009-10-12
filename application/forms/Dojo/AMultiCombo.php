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
abstract class ACI_Form_Dojo_AMultiCombo extends Zend_Dojo_Form
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
                        'onChange' => 'updateKey'
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
        
        $match = $this->createElement('CheckBox', 'match')->setValue(1)
            ->setLabel('Match_whole_words_only');
        $match->getDecorator('label')->setOption('placement', 'append');
        
        $translator = Zend_Registry::get('Zend_Translate');
        
        $clear = new Zend_Form_Element_Button('clear');
        $clear
            ->setOptions(array('onclick' => 'clearForm()'))
            ->setLabel('Clear_form');
        
        $submit = $this->createElement('submit', 'search')
            ->setLabel($translator->translate('Search') . ' >>');
        
        $this->addElement($match)
             ->addElement($clear)
             ->addElement($submit);
        
        $this->addDisplayGroup(array('match'), 'matchGroup');
        $this->addDisplayGroup(array('clear', 'search'), 'submitGroup');
        
        $this->addErrorMessage($translator->translate('Error_empty_key'));
        
        $this->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag' => 'div', 'class' => 'search-form')),
                    'Form'
            )
        );
        
        $this->setAttrib('onSubmit', 'showLoader');
    }
    
    public function getInputElements()
    {
        return array_merge(array('match'), array_keys($this->_combos));
    }
    
    /**
     * Validates the form, making input mandatory in at least one of the
     * combo boxes
     * @see library/Zend/Zend_Form#isValid($data)
     * @param array $value
     * @return boolean
     */
    public function isValid($data)
    {
        // Form not submited
        if(!isset($data['key'])) {
            return true;
        }
        $empty = true;
        foreach(array_keys($this->_combos) as $comboId) {
            if(isset($data[$comboId]) && strlen(trim($data[$comboId])) > 0) {
                $empty = false;
            }
        }
        $this->_errorsExist = $empty;
        return !$empty;
    }
    
    public function getErrorMessage()
    {
        $em = $this->getErrorMessages();
        return $em ?
            Zend_Registry::get('Zend_Translate')->translate(current($em)) :
            null;
    }
}