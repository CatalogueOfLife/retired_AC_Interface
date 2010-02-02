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
    /**
     * Maps the interface column names to the real names in the database
     *
     * @param string $columName
     * @return string
     */
    public static function getRightColumnName($columName)
    {
        $columMap = array(
            'source' => 'database_name_displayed',
            'group' => 'taxa',
            'names' => 'accepted_species_names'
        );
        return isset($columMap[$columName]) ?
            $columMap[$columName] : null;
    }
    
    /**
     * Returns the totals used as statistics in the info pages either from the
     * database or from the cache
     *
     * @return array
     */
    public function getStatistics()
    {
        $cache = Zend_Registry::get('cache');
        $cacheKey = 'statistics';
        $res = false;
        if($cache) {
            $res = $cache->load($cacheKey);
        }
        if(!$res) {
            $res = $this->_calculateStatistics();
            if($cache) {
                $cache->save($res, $cacheKey);
            }
        }
        return $res;
    }
    
    /**
     * Queries the database to collect the following statistics:
     * databases => Total count of source databases
     * common_names => Total count of common names
     * synonyms => Total count of synonyms
     * infraspecific_taxa => Total count of infraspecific taxa
     * species => Total count of species
     *
     * @return array
     */
    protected function _calculateStatistics()
    {
        $stats = array();
        // Number of databases
        $databases = new ACI_Model_Table_Databases();
        $stats['databases'] =
            number_format($databases->countWithAcceptedNames());
        // Number of common names
        $commonNames = new ACI_Model_Table_CommonNames();
        $stats['common_names'] = number_format($commonNames->count());
        // Number of synonyms
        $scientificNames = new ACI_Model_Table_ScientificNames();
        $stats['synonyms'] = number_format($scientificNames->countSynonyms());
        // Number of infraspecific taxa
        $stats['infraspecific_taxa'] =
            number_format($scientificNames->countInfraspecificTaxa());
        // Number of accepted names
        $stats['species'] = number_format($scientificNames->countSpecies());
        return $stats;
    }
}