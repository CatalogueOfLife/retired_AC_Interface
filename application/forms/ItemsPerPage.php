<?php
class ACI_Form_ItemsPerPage extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        $translator = Zend_Registry::get('Zend_Translate');
        
        $items = $this->createElement('text', 'items', array('size' => 4));
        $items->setLabel($translator->translate('Show'))
              ->setDescription($translator->translate('records_per_page'));
              
        $this->addElement($items)
             ->addElement(
                 $this->createElement(
                     'submit', 'update'
                 )->setLabel($translator->translate('Update'))
             );
        
        $this->setDecorators(
            array(
                'FormElements',
                array(
                    'HtmlTag',
                    array('tag' => 'div', 'class' => 'items_form')),
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