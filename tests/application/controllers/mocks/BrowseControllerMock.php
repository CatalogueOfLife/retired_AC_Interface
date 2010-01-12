<?php
require_once 'application/controllers/BrowseController.php';
/**
 * Annual Checklist Interface
 *
 * Class BrowseControllerMock
 * Mock of the BrowseController that allows to modify the persistance flag for
 * the tree and gives public interface to the persistTree method
 *
 * @category    ACI
 * @package     tests
 * @subpackage  mocks
 *
 */
class BrowseControllerMock extends BrowseController
{
    /**
     * @override AController::init()
     */
    public function init() {
        
    }
    public function persistTree(&$id) {
        return $this->_persistTree($id);
    }
    
    public function setTreePersistance($enabled) {
        $this->_persistTree = (bool) $enabled;
    }
}