<?php
require_once 'AController.php';
/**
 * Annual Checklist Interface
 *
 * Class SearchController
 * Defines the search actions
 *
 * @category    ACI
 * @package     application
 * @subpackage  controllers
 *
 */
class SearchController extends AController
{
    public function commonAction()
    {
        $this->view->title = $this->view->translate('Search_common_names');
        $this->view->headTitle($this->view->title, 'APPEND');

        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());

        if ($this->_hasParam('key') && $this->_getParam('submit', 1) &&
            $formIsValid) {
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        }
    }

    public function scientificAction()
    {
        $reset = $this->_getParam('reset', false);
        if($reset) {
            $this->getHelper('SessionHandler')->clear('genus');
            $this->getHelper('SessionHandler')->clear('species');
            $this->getHelper('SessionHandler')->clear('infraspecies');
            $this->getHelper('SessionHandler')->clear('match');
        }
        $this->view->controller = 'search';
        $this->view->action = 'scientific';
        // Search hint query request
        $fetch = $this->_getParam('fetch', false);
        if ($fetch) {
            $this->view->layout()->disableLayout();
            exit(
                $this->getHelper('Query')->fetchTaxaByRank(
                    $fetch, $this->_getParam('q'), $this->_getParam('p')
                )
            );
        }
        $this->view->title = $this->view->translate('Search_scientific_names');
        $this->view->headTitle($this->view->title, 'APPEND');

        $form = $this->_getSearchForm();
        $formIsValid = $form->isValid($this->_getAllParams());
        // Results page
        if ($this->_hasParam('match') && $this->_getParam('submit', 1) &&
            $formIsValid) {
                	$searchString = array();
            if($this->_getParam('kingdom') !== null) {
        		$searchString['kingdom'] = $this->_getParam('kingdom');
        	}
            if($this->_getParam('phylum') !== null) {
        		$searchString['phylum'] = $this->_getParam('phylum');
        	}
            if($this->_getParam('order') !== null) {
        		$searchString['order'] = $this->_getParam('order');
        	}
            if($this->_getParam('class') !== null) {
        		$searchString['class'] = $this->_getParam('class');
        	}
            if($this->_getParam('superfamily') !== null) {
        		$searchString['superfamily'] = $this->_getParam('superfamily');
        	}
            if($this->_getParam('family') !== null) {
        		$searchString['family'] = $this->_getParam('family');
        	}
            if($this->_getParam('genus') !== null) {
        		$searchString['genus'] = $this->_getParam('genus');
        	}
            if($this->_getParam('subgenus') !== null) {
        		$searchString['subgenus'] = $this->_getParam('subgenus');
        	}
            if($this->_getParam('species') !== null) {
        		$searchString['species'] = $this->_getParam('species');
        	}
            if($this->_getParam('infraspecies') !== null) {
        		$searchString['infraspecies'] = $this->_getParam('infraspecies');
        	}
            $this->_setSessionFromParams($form->getInputElements());
            $this->view->searchString = 'Search_results_for_scientific_names';
            $this->view->searchParams = $searchString;
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        // Form page
        } else {
            if (!$formIsValid && $this->_hasParam('match')) {
                $this->_setSessionFromParams($form->getInputElements());
            }
            if ($this->_getParam('submit', 1)) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        }
    }

    public function distributionAction()
    {
        $this->view->title = $this->view->translate('Search_distribution');
        $this->view->headTitle($this->view->title, 'APPEND');

        $form = $this->_getSearchForm();
        if ($form->isValid($this->_getAllParams()) &&
            $this->_hasParam('key') && $this->_getParam('submit', 1)) {
            //Normal Search
            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());

        } elseif ($form->isValid($this->_getAllParams()) &&
            $this->_hasParam('regions') && $this->_getParam('submit', 1)) {
            //Search by map
            $regionIds = explode(',',$this->_getParam('regions'));
            $regionModel = new ACI_Model_Table_Regions($this->_db);
    		$regions = array();
            foreach($regionIds as $regionId) {
            	$temp = $regionModel->getRegion($regionId);
            	$regions[] = $temp['name'];
            }
            if(count($regions) > 5) {
            	$regions = $regions[0].', '.$regions[1].', '.$regions[2].', '.$regions[3].' '.
            	str_replace('%s',(count($regions) - 4),$this->view->translate('and_x_other_regions'));
            } else {
	            $regions = implode(', ',$regions);
            }
            $this->view->searchString = $this->view->translate('Search_distribution') . ' - ' . str_replace('%s','"'.$regions.'"',$this->view->translate('Search_results_for'));

            $this->_setSessionFromParams(array('regions'));
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage(array('regions'));

        } else {
        	//No search
        	$this->view->mapSearchModuleEnabled = $this->_moduleEnabled(
            	'map_search');
        	if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($this->view->title, $form);
        }
    }

    public function allAction()
    {
        $this->view->title = $this->view->translate('Search_all_names');
        $this->view->headTitle($this->view->title, 'APPEND');
        $formHeader =
            sprintf(
                $this->view->translate('Search_fixed_edition'),
                '<span class="red">' .
                $this->view->translate('Annual_Checklist') . '</span>'
            );

        $form = $this->_getSearchForm();

        if ($form->isValid($this->_getAllParams()) &&
            $this->_hasParam('key') && $this->_getParam('submit', 1)) {
        	$this->_setParam('key', $this->_cleanSearchString($this->_getParam('key')));

            $this->_setSessionFromParams($form->getInputElements());
            $this->getHelper('Query')->tagLatestQuery();
            $this->_renderResultsPage($form->getInputElements());
        } else {
            if (!$this->_hasParam('key')) {
                $this->_setParamsFromSession($form->getInputElements());
            }
            $this->_renderFormPage($formHeader, $form);
        }
    }

    public function exportAction()
    {
        if ($this->_hasParam('export') &&
            $this->getHelper('Query')->getLatestQuery()) {
            $this->_exportResults();
        }
        $this->view->form = $this->getHelper('FormLoader')->getExportForm();
        $this->view->export_limit =
            $this->getHelper('Export')->getNumRowsLimit();
    }

    public function __call($name, $arguments)
    {
        $this->_forward('all');
    }

    private function _cleanSearchString($str) {
    	$find = array(
    		'(',
    		')',
    		'"',
    		"'"
    	);
    	return str_replace($find, '', $str);
    }

}