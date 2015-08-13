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
abstract class ACI_Form_Dojo_AMultiCombo extends ACI_Form_Dojo_Abstract
{
    protected $_combos;
    protected $_fetchUrl;

    public function init()
    {
		$config = Zend_Registry::get('config');
		$translator = Zend_Registry::get('Zend_Translate');
    	$this->addAttribs(array('class' => 'multi-search'));

        foreach ($this->_combos as $comboId => $comboLabel) {

            $comboBox = $this->createElement(
                'ComboBox',
                $comboId,
                array(
                    'required' => false,
                    'autoComplete' => false,
                    'regExp' => '.*',
                    'labelType' => 'html',
                    'labelAttr' => 'label',
                    'storeId' => $comboId . 'Store',
                    'storeType' => 'ACI.dojo.TxReadStore',
                    'storeParams' => array(
                        'url' => $this->_fetchUrl . '/' . $comboId
                    ),
                    'dijitParams' => array(
                        'searchAttr' => 'name',
                        'hasDownArrow'   => true,
                        'highlightMatch' => 'none',
                        'queryExpr' => '${0}*',
                        'searchAttr' => 'name',
                        'searchDelay' => 500,
                        'onKeyPress' => 'keyPress'
                    ),
                    'style' => 'width: 300px'
                )
            )->setLabel($translator->translate(
                $comboLabel == 'Top_level_group' ? $comboLabel : 'RANK_' . strtoupper($comboLabel))
            );

            $comboBox->removeValidator('NotEmpty');

            $this->addElement($comboBox);
            $this->addDisplayGroup(
                array($comboId),
                $comboId . 'Group',
                array('class' => 'searchGroup')
            );
        }

        $this->addElement(
            $this->createElement('hidden', 'key')->setValue(
                Zend_Json::encode(
                    array_fill_keys(array_keys($this->_combos), '')
                )
            )
        );

        if ($this->_moduleEnabled("fossils"))
        {
            /*
             $fossil = $this->createElement('CheckBox', 'fossil')
                ->setValue(0)
                ->setLabel('Include_extinct_taxa');
            */

            // Tried to solve ACI-612 but didn't really work yet
            // Cookie isn't read properly; initialisation problem?
            $fossil = $this->createElement('CheckBox', 'fossil')
                ->setChecked($this->_getTreeExtinct())
                ->setLabel('Include_extinct_taxa')
                ->setAttribs(array(
                    'onClick' => 'showOrHideExtinct(false)')
                );
            $fossil->getDecorator('label')->setOption('placement', 'append');
            $this->addElement($fossil);
        }

        $match = $this->createElement('CheckBox', 'match')->setValue(1)
            ->setLabel('Match_whole_words_only');
/*        $match = $this->createElement('radio','match')->setValue(2)
          ->addMultiOption(2,'Match_starts_with')
          ->addMultiOption(1,'Match_whole_words_only')
          ->addMultiOption(0,'Match_all');*/
        $match->getDecorator('label')->setOption('placement', 'append');

        $translator = Zend_Registry::get('Zend_Translate');

        $clear = $this->createElement('Button', 'clear')
            ->setOptions(array('onclick' => 'clearForm()'))
            ->setLabel('Clear_form');

        $submit = $this->createElement('SubmitButton', 'search')
            ->setLabel($translator->translate('Search'));

        $this->addElement($match)
             ->addElement($clear)
             ->addElement($submit);

        if ($this->_moduleEnabled("fossils")) {
            $this->addDisplayGroup(array('fossil'), 'fossilGroup');
        }
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

        $this->setAttrib('onsubmit', 'submitMultiSearchForm');
    }

    public function getInputElements()
    {
        return array_merge(array('match', 'fossil'), array_keys($this->_combos));
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
        if (!isset($data['match'])) {
            return true;
        }
        $empty = true;
        foreach (array_keys($this->_combos) as $comboId) {
            if (isset($data[$comboId]) && strlen(trim($data[$comboId])) > 0) {
                $empty = false;
            }
        }
        if ($empty) {
            $this->markAsError();
            return false;
        }
        return true;
    }

    public function getErrorMessage()
    {
        $em = $this->getErrorMessages();
        return $em ?
            Zend_Registry::get('Zend_Translate')->translate(current($em)) :
            null;
    }

    protected function _moduleEnabled ($module)
    {
        return Bootstrap::instance()->getOption('module.' . $module);
    }
}