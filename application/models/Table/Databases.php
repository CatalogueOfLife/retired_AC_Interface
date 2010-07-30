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
    protected $_name = 'source_database';
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
    
    public function count()
    {
        if (is_null(self::$_numDatabases)) {
            $select = $this->select();
            $select->from($this, array('COUNT(1) AS total'));
            $rows = $this->fetchAll($select);
            self::$_numDatabases = $rows[0]->total;
        }
        return self::$_numDatabases;
    }
    
    public function countNew()
    {
        if (is_null(self::$_numDatabasesNew)) {
            $select = $this->select();
            $select->from(
                $this, array('COUNT(1) AS total')
            )->where('is_new');
            $rows = $this->fetchAll($select);
            self::$_numDatabasesNew = $rows[0]->total;
        }
        return self::$_numDatabasesNew;
    }
    
    public function countWithAcceptedNames()
    {
        if (is_null(self::$_numDatabasesWithAcceptedNames)) {
            $select = $this->select();
            $select->from(
                $this, array('COUNT(1) AS total')
            )->where('accepted_species_names > 0');
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
    
    protected function _taxonomicCoverage($id)
    {
        $select = $this->select()->setIntegrityCheck(false);
        $select->from(
            $this,
            array(
                'kingdom' => 'sne_k.name_element',
                'phylum' => 'sne_p.name_element',
                'class' => 'sne_c.name_element',
                'order' => 'sne_o.name_element',
                'kingdom_id' => 'tne_k.taxon_id',
                'phylum_id' => 'tne_p.taxon_id',
                'class_id' => 'tne_c.taxon_id',
                'order_id' => 'tne_o.taxon_id'
                )
        )->joinRight(
            array('t' => 'taxon'),
            'source_database.id = t.source_database_id AND t.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_GENUS,
            array()
        )->joinRight(
            array('tne_g' => 'taxon_name_element'),
            't.id = tne_g.taxon_id',
            array()
        )->joinRight(
            array('tne_f' => 'taxon_name_element'),
            'tne_g.parent_id = tne_f.taxon_id',
            array()
        )->joinLeft(
            array('tne_sf' => 'taxon_name_element'),
            'tne_f.parent_id = tne_sf.taxon_id',
            array()
        )
        ->joinLeft(
            array('t_sf' => 'taxon'),
            'tne_sf.taxon_id = t_sf.id AND t_sf.taxonomic_rank_id = ' .
            ACI_Model_Table_Taxa::RANK_SUPERFAMILY,
            array()
        )
        ->joinRight(
            array('tne_o' => 'taxon_name_element'),
            '(tne_f.parent_id = tne_o.taxon_id AND t_sf.id IS NULL) OR ' .
            '(tne_sf.parent_id = tne_o.taxon_id AND t_sf.id IS NOT NULL)',
            array()
        )->joinRight(
            array('sne_o' => 'scientific_name_element'),
            'tne_o.scientific_name_element_id = sne_o.id',
            array()
        )->joinRight(
            array('tne_c' => 'taxon_name_element'),
            'tne_o.parent_id = tne_c.taxon_id',
            array()
        )->joinRight(
            array('sne_c' => 'scientific_name_element'),
            'tne_c.scientific_name_element_id = sne_c.id',
            array()
        )->joinRight(
            array('tne_p' => 'taxon_name_element'),
            'tne_c.parent_id = tne_p.taxon_id',
            array()
        )->joinRight(
            array('sne_p' => 'scientific_name_element'),
            'tne_p.scientific_name_element_id = sne_p.id',
            array()
        )->joinRight(
            array('tne_k' => 'taxon_name_element'),
            'tne_p.parent_id = tne_k.taxon_id',
            array()
        )->joinRight(
            array('sne_k' => 'scientific_name_element'),
            'tne_k.scientific_name_element_id = sne_k.id',
            array()
        )->where('source_database.id = ?'
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
                'order_id' => $row['order_id']
            );
        }
        return $temp;
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
        $row['image'] = $this->_getImageFromName($row['abbreviated_name']);
        $row['thumb'] = $this->_getThumbFromName($row['abbreviated_name']);
        $row['url'] = $this->_getUrlFromId($row['id']);
        $row['accepted_species_names'] = $this->_countAcceptedSpecies($row['id']);
        $row['accepted_infraspecies_names'] = $this->_countAcceptedInfraSpecies($row['id']);
        $row['common_names'] = $this->_countCommonNames($row['id']);
        $row['species_synonyms'] = $this->_countSpeciesSynonyms($row['id']);
        $row['infraspecies_synonyms'] = $this->_countInfraSpeciesSynonyms($row['id']);
        $row['total_names'] = $row['accepted_species_names'] +
            $row['accepted_infraspecies_names'] + $row['common_names'] +
            $row['species_synonyms'] + $row['infraspecies_synonyms'];
        $row['taxonomic_coverage'] = $this->_taxonomicCoverage($row['id']);
        return $row;
    }
}