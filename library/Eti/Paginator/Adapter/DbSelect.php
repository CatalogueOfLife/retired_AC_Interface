<?php
/**
 * Annual Checklist Interface
 *
 * Class Eti_Paginator_Adapter_DbSelect
 * Extends the Zend_Paginator_Adapter_DbSelect to allow the addition of custom
 * counting fields to the paginator count query
 *
 * @category    Eti
 * @package     Eti_Paginator
 * @subpackage  Adapter
 *
 */
class Eti_Paginator_Adapter_DbSelect extends Zend_Paginator_Adapter_DbSelect
{
    protected $_countColumns = array();
    
/**
     * Sets the total row count, either directly or through a supplied
     * query.  Without setting this, {@link getPages()} selects the count
     * as a subquery (SELECT COUNT ... FROM (SELECT ...)).  While this
     * yields an accurate count even with queries containing clauses like
     * LIMIT, it can be slow in some circumstances.  For example, in MySQL,
     * subqueries are generally slow when using the InnoDB storage engine.
     * Users are therefore encouraged to profile their queries to find
     * the solution that best meets their needs.
     *
     * @param  Zend_Db_Select|integer $totalRowCount Total row count integer
     *                                               or query
     * @return Zend_Paginator_Adapter_DbSelect $this
     * @throws Zend_Paginator_Exception
     */
    public function setRowCount($rowCount)
    {
        if ($rowCount instanceof Zend_Db_Select) {
            $columns = $rowCount->getPart(Zend_Db_Select::COLUMNS);

            $rowCountColumn =
                $this->_select->getAdapter()->foldCase(self::ROW_COUNT_COLUMN);
                
            $rowCountColumExists = false;
            
            foreach($columns as $col) {
                $countColumnPart = $col[1];
                if ($countColumnPart instanceof Zend_Db_Expr) {
                    $countColumnPart = $countColumnPart->__toString();
                }
                $countColumnAlias = $col[2];
                if(false !== strpos($countColumnPart, $rowCountColumn) ||
                    $countColumnAlias == $rowCountColumn) {
                    $rowCountColumExists = true;
                    break;
                }
            }
            
            if ($rowCountColumExists == false) {
                /**
                 * @see Zend_Paginator_Exception
                 */
                require_once 'Zend/Paginator/Exception.php';

                throw new Zend_Paginator_Exception('Row count column not found');
            }

            $result = $rowCount->query(Zend_Db::FETCH_ASSOC)->fetch();
            
            foreach($result as $colName => $colValue) {
                $this->_countColumns[$colName] = $colValue;
            }
            $this->_rowCount = $this->getCountColumn($rowCountColumn);
            
        } else if (is_integer($rowCount)) {
            $this->_rowCount = $rowCount;
        } else {
            /**
             * @see Zend_Paginator_Exception
             */
            require_once 'Zend/Paginator/Exception.php';

            throw new Zend_Paginator_Exception('Invalid row count');
        }

        return $this;
    }
    
    /**
     * It returns the result of the counting of the given column name
     * If the value does not exist, it returns 0
     *
     * @param string $columnId
     * @return int
     */
    public function getCountColumn($columnName)
    {
        return isset($this->_countColumns[$columnName]) ?
            $this->_countColumns[$columnName] : 0;
    }
}