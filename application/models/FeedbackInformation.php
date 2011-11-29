<?php
require_once 'AModel.php';
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_FeedbackInformation
 * Search queries builder
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_FeedbackInformation extends AModel
{
    public function selectScientificNames($taxonId)
    {
        $select = new Eti_Db_Select($this->_db);
        $select->where('dss.`id` = ?',array($taxonId));
        $select->from(
            array('dss' => '_search_scientific'),
            array('infraspecies','species','subgenus','genus','family','superfamily','order','class','phylum','kingdom')
        );
        return $select->query()->fetch();
    }
}