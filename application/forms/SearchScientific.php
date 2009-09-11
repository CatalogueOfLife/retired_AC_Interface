<?php
class ACI_Form_SearchScientific extends Zend_Form
{
    public function init ()
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
        
            $el = $this->createElement('text', $rank, array('size' => 40));
            $el->setLabel($translator->translate($label))
                ->setRequired(true)
                ->addErrorMessage(
                    $translator->translate('Error_key_too_short')
                );
            $this->addElement($el);
        }
        
        $submit = $this->createElement('submit', 'search')
            ->setLabel($translator->translate('Search') . ' >>');
            
        $this->addElement($submit);
        
        $this->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag' => 'div', 'class' => 'search_form')),
                    'Form'
            )
        );
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