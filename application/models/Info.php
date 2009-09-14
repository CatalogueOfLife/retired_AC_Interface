<?php
require_once 'AModel.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Info
 * Search queries builder
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Info extends AModel
{
    public static function getRightColumnName($columName)
    {
        $columMap = array(
            'source' => 'database_name_displayed',
            'group' => 'taxa',
            'names' => 'accepted_species_names DESC'
        );
        return isset($columMap[$columName]) ?
            $columMap[$columName] : null;
    }
}