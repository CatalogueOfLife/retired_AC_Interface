<?php
class AC_Form_ScientificSearch extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $translator = Zend_Registry::get('Zend_Translate');
                
        $searchGenus = $this->createElement('text','search_genus');
        $searchGenus->setLabel($translator->translate('Genus'));

        $searchSpecies = $this->createElement('text','search_speices');
        $searchSpecies->setLabel($translator->translate('Species'));

        $searchInfraspecies =
            $this->createElement('text','search_infraspecies');
        $searchInfraspecies->setLabel($translator->translate('Infraspecies'));
        
        $match_whole_words = $this->createElement('checkbox','whole_words');
        $match_whole_words->setValue('1');
        $match_whole_words->setLabel(
            $translator->translate('Match_whole_words_only')
        );

        $clear = $this->createElement(
            'reset',
            $translator->translate('Clear_form')
        );
        $submit = $this->createElement(
            'submit',
            $translator->translate('Search')
        );
        
        // Add elements to form:
        $this->addElement($searchGenus)
             ->addElement($searchSpecies)
             ->addElement($searchInfraspecies)
             ->addElement($match_whole_words)
             ->addElement($clear)
             ->addElement($submit);
    }
}