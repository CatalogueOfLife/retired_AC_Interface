<?php
class ACI_Form_JQuery_SearchScientific extends ZendX_JQuery_Form
{   
    public function init ()
    {
        $elem = new ZendX_JQuery_Form_Element_AutoComplete(
            'query', 
            array(
                'Label' => 'Search' , 
                'required' => true , 
                'filters' => array(
                    'StripTags') , 
                'validators' => array(
                    array(
                        'validator' => 'StringLength' , 
                        'options' => array(
                            'min' => '3') , 
                        'breakChainOnFailure' => true) , 
                    array(
                        'Alnum'))));
        
        $elem->setJQueryParams(
            array(
                'data' => array() , 
                'url' => 'scientific/fetch/genus',
                'minChars' => 1 , 
                'onChangeInterval' => 500));
        $elementDecorators = array(
            array(
                'UiWidgetElement' , 
                array(
                    'tag' => '')) , 
            array('Errors' , 
                array(
                    'tag' => 'div' , 
                    'class' => 'error')) , 
            array('Label') , 
            array('HtmlTag' , 
                array(
                    'tag' => 'div')));
        $elem->setDecorators($elementDecorators);
        $this->addElement($elem);
    }

}