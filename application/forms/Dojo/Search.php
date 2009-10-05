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
class ACI_Form_Dojo_Search extends Zend_Dojo_Form
{
    public function init ()
    {
        $this->setMethod('post');
        $this->setAttribs(array('id' => 'searchForm'));
        $translator = Zend_Registry::get('Zend_Translate');
        
        $key = $this->createElement(
            'TextBox', 'key', array('style' => 'width: 300px')
        );
        $key->setLabel($translator->translate('Search_for') . ':')
            ->addValidator('stringLength', false, array(2))
            ->setRequired(true)
            ->addErrorMessage($translator->translate('Error_key_too_short'));
        
        $match = $this->createElement('CheckBox', 'match')->setValue(1);
        $match->setLabel($translator->translate('Match_whole_words_only'));
        $match->getDecorator('label')->setOption('placement', 'append');
        
        $submit = $this->createElement('submit', 'search')
            ->setLabel($translator->translate('Search') . ' >>');
            
        $this->addElement($key)->addElement($match)->addElement($submit);
        
        $this->addDisplayGroup(array('key'), 'keyGroup');
        $this->addDisplayGroup(array('match'), 'matchGroup');
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
    
    public function getInputElements()
    {
        return array('key', 'match');
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
}