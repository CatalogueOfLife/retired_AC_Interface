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

            $countColumnPart = $columns[0][1];

            if ($countColumnPart instanceof Zend_Db_Expr) {
                $countColumnPart = $countColumnPart->__toString();
            }

            $rowCountColumn =
                $this->_select->getAdapter()->foldCase(self::ROW_COUNT_COLUMN);

            // The select query can contain only one column, which should be
            // the row count column
            if (false === strpos($countColumnPart, $rowCountColumn)) {
                /**
                 * @see Zend_Paginator_Exception
                 */
                require_once 'Zend/Paginator/Exception.php';

                throw new Zend_Paginator_Exception('Row count column not found');
            }

            $result = $rowCount->query(Zend_Db::FETCH_ASSOC)->fetchAll();

            $numRows = count($result);
            for($i = 0, $this->_rowCount = 0; $i < $numRows; $i++) {
                $this->_rowCount += $result[$i][$rowCountColumn];
                $this->_countColumns[$i] = $result[$i][$rowCountColumn];
            }
            
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
     * It returns the result of the counting of the field in the given index
     * If the field does not exist, it returns 0
     *
     * @param string $columnId
     * @return int
     */
    public function getCountColumn($columnId)
    {
        return isset($this->_countColumns[$columnId]) ?
            $this->_countColumns[$columnId] : 0;
    }
}