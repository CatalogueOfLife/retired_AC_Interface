<?php
/**
 * Annual Checklist Interface
 *
 * Class ACI_Model_Table_Databases
 * Databases table model
 *
 * @category    ACI
 * @package     application
 * @subpackage  models
 *
 */
class ACI_Model_Table_Databases extends Zend_Db_Table_Abstract
{
    protected $_name = '_source_database_details';
    protected $_primary = 'id';
    protected static $_numDatabases;
    protected static $_numDatabasesNew;
    protected static $_numDatabasesWithAcceptedNames;

    public function get($id)
    {
        $dbDetails = $this->find((int)$id);

        $res = $dbDetails->current();
        if (!$res) {
            return false;
        }
        return $this->_decorate($res->toArray());
    }

    public function getAll($order = null)
    {
        $rowset = $this->fetchAll(null, $order);
        if (!$rowset) {
            return false;
        }
        $results = array();
        foreach ($rowset as $row) {
            $results[] = $this->_decorate($row->toArray());
        }
        unset($rowset);
        return $results;
    }

    public function countWithAcceptedNames()
    {
        if (is_null(self::$_numDatabasesWithAcceptedNames)) {
            $select = $this->select();
            $select->from(
                $this, array('COUNT(DISTINCT source_database.id) AS total')
            )->joinLeft(array('t' => 'taxon'),
                'source_database.id = t.source_database_id',
                array()
            );
            $rows = $this->fetchAll($select);
            self::$_numDatabasesWithAcceptedNames = $rows[0]->total;
        }
        return self::$_numDatabasesWithAcceptedNames;
    }

    protected function _countAcceptedSpecies($id)
    {
        $select = $this->select();
        $select->from(
            $this,
            array('COUNT(*) AS total')
        )->joinRight(
            array('t' => 'taxon'),
            'source_database.id = t.source_database_id',
            array()
        )->where('source_database.id = ? AND t.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_SPECIES
        )
        ->bind(array($id));
        $rows = $this->fetchAll($select);
        return $rows[0]->total;
    }

    protected function _countAcceptedInfraSpecies($id)
    {
        $select = $this->select();
        $select->from(
            $this,
            array('COUNT(*) AS total')
        )->joinRight(
            array('t' => 'taxon'),
            'source_database.id = t.source_database_id',
            array()
        )->where('source_database.id = ? AND t.taxonomic_rank_id NOT IN (' .
            ACI_Model_Table_Taxa::RANK_KINGDOM.','.
            ACI_Model_Table_Taxa::RANK_PHYLUM.','.
            ACI_Model_Table_Taxa::RANK_CLASS.','.
            ACI_Model_Table_Taxa::RANK_ORDER.','.
            ACI_Model_Table_Taxa::RANK_SUPERFAMILY.','.
            ACI_Model_Table_Taxa::RANK_FAMILY.','.
            ACI_Model_Table_Taxa::RANK_GENUS.','.
            ACI_Model_Table_Taxa::RANK_SUBGENUS.','.
            ACI_Model_Table_Taxa::RANK_SPECIES.')'
        )
        ->bind(array($id));
        $rows = $this->fetchAll($select);
        return $rows[0]->total;
    }

    protected function _countCommonNames($id)
    {
        $select = $this->select();
        $select->from(
            $this,
            array('COUNT(*) AS total')
        )->joinRight(
            array('t' => 'taxon'),
            'source_database.id = t.source_database_id',
            array()
        )->joinRight(
            array('cn' => 'common_name'),
            't.id = cn.taxon_id',
            array()
        )->where('source_database.id = ?'
        )
        ->bind(array($id));
        $rows = $this->fetchAll($select);
        return $rows[0]->total;
    }

    protected function _countSpeciesSynonyms($id)
    {
        $select = $this->select();
        $select->from(
            $this,
            array('COUNT(*) AS total')
        )->joinRight(
            array('t' => 'taxon'),
            'source_database.id = t.source_database_id',
            array()
        )->joinRight(
            array('s' => 'synonym'),
            't.id = s.taxon_id',
            array()
        )->where('source_database.id = ? AND ' .
            '(SELECT COUNT(*) FROM synonym_name_element WHERE synonym_id = s.id) = 2'
        )
        ->bind(array($id));
        $rows = $this->fetchAll($select);
        return $rows[0]->total;
    }

    protected function _countInfraSpeciesSynonyms($id)
    {
        $select = $this->select();
        $select->from(
            $this,
            array('COUNT(*) AS total')
        )->joinRight(
            array('t' => 'taxon'),
            'source_database.id = t.source_database_id',
            array()
        )->joinRight(
            array('s' => 'synonym'),
            't.id = s.taxon_id',
            array()
        )->where('source_database.id = ? AND ' .
            '(SELECT COUNT(*) FROM synonym_name_element WHERE synonym_id = s.id) > 2'
        )
        ->bind(array($id));
        $rows = $this->fetchAll($select);
        return $rows[0]->total;
    }

    protected function _getTaxonomicCoverage($id)
    {
        $select = $this->select()->setIntegrityCheck(false);
        $select->from(
            $this,
            array(
                'kingdom' => 'dsdtc.kingdom',
                'phylum' => 'dsdtc.phylum',
                'class' => 'dsdtc.class',
                'order' => 'dsdtc.order',
                'kingdom_id' => 'dsdtc.kingdom_id',
                'phylum_id' => 'dsdtc.phylum_id',
                'class_id' => 'dsdtc.class_id',
                'order_id' => 'dsdtc.order_id',
                'kingdom_status' => 'dsdtc.kingdom_status',
                'phylum_status' => 'dsdtc.phylum_status',
                'class_status' => 'dsdtc.class_status',
                'order_status' => 'dsdtc.order_status'
                )
        )->joinRight(
            array('dsdtc' => '_source_database_taxonomic_coverage'),
            '_source_database_details.id = dsdtc.source_database_id'
        )->where('_source_database_details.id = ?'
        )
        ->bind(array($id));
        $rows = $this->fetchAll($select);
        $temp = array();
        foreach($rows as $row)
        {
            $temp[] = array(
                'kingdom' => $row['kingdom'],
                'phylum' => $row['phylum'],
                'class' => $row['class'],
                'order' => $row['order'],
                'kingdom_id' => $row['kingdom_id'],
                'phylum_id' => $row['phylum_id'],
                'class_id' => $row['class_id'],
                'order_id' => $row['order_id'],
                'kingdom_status' => $row['kingdom_status'],
                'phylum_status' => $row['phylum_status'],
                'class_status' => $row['class_status'],
                'order_status' => $row['order_status']
            );
        }
        return $temp;
    }

    protected function _getWebsites($id)
    {
        $select = $this->select()->setIntegrityCheck(false);
        $select->from(
            $this,
            array('uri.resource_identifier')
        )->joinLeft(
            array('utsd' => 'uri_to_source_database'),
            '_source_database_details.id = utsd.source_database_id',
            array()
        )->joinLeft(
            array('uri'),
            'utsd.uri_id = uri.id',
            array()
        )->where('_source_database_details.id = ?'
        )
        ->bind(array($id));
        $rows = $this->fetchAll($select);
        $rows = $this->fetchAll($select);
        $temp = array();
        foreach($rows as $row)
        {
            $temp[] = $row['resource_identifier'];
        }
        return implode(';',$temp);
    }

    protected function _getImageFromName($imageName)
    {
        return '/images/databases/' .
            $this->_getImagenameFromName($imageName) . '.png';
    }

    protected function _getThumbFromName($imageName)
    {
        return '/images/databases/' .
            $this->_getImagenameFromName($imageName) . '.gif';
    }

    protected function _getUrlFromId ($id)
    {
        return '/details/database/id/' . $id;
    }

    protected function _getImagenameFromName($imageName)
    {
        return str_replace(' ', '_', $imageName);
    }

    protected function _decorate(array $row)
    {
        $row['image'] = $this->_getImageFromName($row['short_name']);
        $row['thumb'] = $this->_getThumbFromName($row['short_name']);
        $row['url'] = $this->_getUrlFromId($row['id']);
        $row['accepted_extinct_species_names'] = $row['number_of_extinct_species'];
        $row['accepted_extinct_infraspecies_names'] = $row['number_of_extinct_infraspecific_taxon'];
        $row['accepted_species_names'] = $row['number_of_species'] -
            $row['accepted_extinct_species_names'];
        $row['accepted_infraspecies_names'] = $row['number_of_infraspecific_taxon'] -
            $row['number_of_extinct_infraspecific_taxon'];
        $row['common_names'] = $row['number_of_common_names'];
        $row['synonyms'] = $row['number_of_synonyms'];
        $row['total_names'] = $row['total_number'];
        $row['total_extant_names'] = $row['total_names'] - $row['number_of_extinct_species'] -
            $row['number_of_infraspecific_taxon'];
        $row['taxonomic_coverage'] = $row['taxonomic_coverage'];
        $row['database_full_name'] = $row['full_name'];
        $row['database_name'] = $row['short_name'];
        $row['is_new'] = $row['is_new'];
        $row['authors_editors'] = $row['authors_editors'];
        $row['taxa'] = $row['english_name'];
        $row['organization'] = $row['organization'];
        $row['web_site'] = $this->_getWebsites($row['id']);
        return $row;
    }
}