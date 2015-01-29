<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Form_Dojo_Search
 * Search dojo-enabled form
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */
class ACI_Form_Dojo_Search extends ACI_Form_Dojo_Abstract
{
    protected $_action;

    public function __construct($action)
    {
        $this->_action = (string)$action;
        parent::__construct();
    }

    public function init ()
    {
        $this->setAttribs(
            array(
                'id' => 'searchForm',
                'name' => 'searchForm'
            )
        );
        $this->setMethod(Zend_Form::METHOD_POST);
        $translator = Zend_Registry::get('Zend_Translate');

        $key = $this->createElement('TextBox', 'key');

        $key->setLabel($translator->translate('Search_for') . ':')
            ->addErrorMessage(null);

        $this->addErrorMessage('Error_key_too_short');

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
                ->setChecked($this->_getCookie('treeExtinct'))
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
        $submit = $this->createElement('SubmitButton', 'search')
            ->setLabel($translator->translate('Search'));

        $this->addElement($key)->addElement($match)->addElement($submit);

        if ($this->_action == "all" && $this->_moduleEnabled("fuzzy_search"))
        {
            $fuzzy = $this->createElement('CheckBox', 'fuzzy')->setValue(0)
                ->setLabel('Use_fuzzy_search');
            $fuzzy->getDecorator('label')->setOption('placement', 'append');
            $this->addElement($fuzzy);
        }

        $this->addDisplayGroup(array('key'), 'keyGroup');
        if ($this->_moduleEnabled("fossils")) {
            $this->addDisplayGroup(array('fossil'), 'fossilGroup');
        }
        $this->addDisplayGroup(array('match', 'fuzzy'), 'matchGroup');
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

        $this->setAttrib('onsubmit', 'submitSearchForm');
    }

    public function getInputElements()
    {
        return ($this->_action == "all") ? array('key', 'match', 'fuzzy', 'fossil') :
            array('key', 'match', 'fossil');
    }

    /**
     * Validates the form
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
        if (!isset($data['key'])) {
            $this->markAsError();
            return false;
        }
        $validator = new Eti_Validate_AlphaNumStringLength(2);
        $valid = $validator->isValid($data['key']);
        if (!$valid) {
            $this->markAsError();
            return false;
        }
        return true;
    }

    public function render(Zend_View_Interface $view = null)
    {
        if (null === $view) {
            $view = $this->getView();
        }
        $loader = $view->getPluginLoader('helper');
        if ($loader->getPaths('Zend_Dojo_View_Helper')) {
            $loader->removePrefixPath('Zend_Dojo_View_Helper');
        }
        return parent::render($view);
    }

    public function getErrorMessage()
    {
        $em = $this->getErrorMessages();
        return $em ?
            Zend_Registry::get('Zend_Translate')->translate(current($em)) :
            null;
    }
}