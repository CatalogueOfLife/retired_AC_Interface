<?php
/**
 * Annual Checklist Interface
 *
 * Class Eti_Db_Select
 * Extends the Zend_Db_Select to fix a bug in the _join function when
 * modifying an instance
 *
 * @category    Eti
 * @package     Eti_Db
 *
 */
class Eti_Db_Select extends Zend_Db_Select
{
    /**
     * Populate the {@link $_parts} 'join' key
     *
     * Does the dirty work of populating the join key.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  null|string $type Type of join; inner, left, and null are
     * currently supported
     * @param  array|string|Zend_Db_Expr $name Table name
     * @param  string $cond Join on this condition
     * @param  array|string $cols The columns to select from the joined table
     * @param  string $schema The database name to specify, if any.
     * @return Zend_Db_Select This Zend_Db_Select object
     * @throws Zend_Db_Select_Exception
     */
    protected function _join($type, $name, $cond, $cols, $schema = null)
    {
        // Fixes the variable not ser error
        $lastFromCorrelationName = null;
        
        if (!in_array($type, self::$_joinTypes) && $type != self::FROM) {
            /**
             * @see Zend_Db_Select_Exception
             */
            require_once 'Zend/Db/Select/Exception.php';
            throw new Zend_Db_Select_Exception(
                "Invalid join type '$type'"
            );
        }

        if (count($this->_parts[self::UNION])) {
            require_once 'Zend/Db/Select/Exception.php';
            throw new Zend_Db_Select_Exception(
                "Invalid use of table with " . self::SQL_UNION
            );
        }

        if (empty($name)) {
            $correlationName = $tableName = '';
        } else if (is_array($name)) {
            // Must be array($correlationName => $tableName) or
            // array($ident, ...)
            foreach ($name as $_correlationName => $_tableName) {
                if (is_string($_correlationName)) {
                    // We assume the key is the correlation name and value is
                    // the table name
                    $tableName = $_tableName;
                    $correlationName = $_correlationName;
                } else {
                    // We assume just an array of identifiers, with no
                    // correlation name
                    $tableName = $_tableName;
                    $correlationName = $this->_uniqueCorrelation($tableName);
                }
                break;
            }
        } else if ($name instanceof Zend_Db_Expr ||
            $name instanceof Zend_Db_Select) {
            $tableName = $name;
            $correlationName = $this->_uniqueCorrelation('t');
        } else if (preg_match('/^(.+)\s+AS\s+(.+)$/i', $name, $m)) {
            $tableName = $m[1];
            $correlationName = $m[2];
        } else {
            $tableName = $name;
            $correlationName = $this->_uniqueCorrelation($tableName);
        }

        // Schema from table name overrides schema argument
        if (!is_object($tableName) && false !== strpos($tableName, '.')) {
            list($schema, $tableName) = explode('.', $tableName);
        }

        if (!empty($correlationName)) {
            if (array_key_exists($correlationName, $this->_parts[self::FROM])) {
                /**
                 * @see Zend_Db_Select_Exception
                 */
                require_once 'Zend/Db/Select/Exception.php';
                throw new Zend_Db_Select_Exception(
                    "You cannot define a correlation name '$correlationName' " .
                    "more than once"
                );
            }
            
            $lastFromCorrelationName = null;
            if ($type == self::FROM) {
                // append this from after the last from joinType
                $tmpFromParts = $this->_parts[self::FROM];
                $this->_parts[self::FROM] = array();
                // move all the froms onto the stack
                while ($tmpFromParts) {
                    $currentCorrelationName = key($tmpFromParts);
                    if ($tmpFromParts[$currentCorrelationName]['joinType'] !=
                        self::FROM) {
                        break;
                    }
                    $lastFromCorrelationName = $currentCorrelationName;
                    $this->_parts[self::FROM][$currentCorrelationName] =
                        array_shift($tmpFromParts);
                }
            } else {
                $tmpFromParts = array();
            }
            $this->_parts[self::FROM][$correlationName] = array(
                'joinType'      => $type,
                'schema'        => $schema,
                'tableName'     => $tableName,
                'joinCondition' => $cond
                );
            while ($tmpFromParts) {
                $currentCorrelationName = key($tmpFromParts);
                $this->_parts[self::FROM][$currentCorrelationName] =
                    array_shift($tmpFromParts);
            }
        }

        // add to the columns from this joined table
        if ($type == self::FROM && $lastFromCorrelationName == null) {
            $lastFromCorrelationName = true;
        }
        $this->_tableCols($correlationName, $cols, $lastFromCorrelationName);

        return $this;
    }
}