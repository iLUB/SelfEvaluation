<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2014 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once('class.csvExportColumns.php');
require_once('class.csvExportRow.php');
/**
 * Class csvExportTable
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExportTable {
    /**
     * @var csvExportRow[]
     */
    protected $rows = null;

    /**
     * @var csvExportColumns
     */
    protected $columns = null;

    /**
     * @var csvExportColumn
     */
    protected $sort_column;
    /**
     * @param csvExportRow[] $rows
     */
    function __construct($rows = array())
    {
        $this->rows = $rows;
        $this->setRows($rows);
    }

    /**
     * @param csvExportRow[] $rows
     */
    protected function addColumnsFromRows($rows){
        $this->columns = new csvExportColumns();

        if($rows){
            foreach($rows as $row){
                $this->addColumnsFromRow($row);
            }
        }
    }

    /**
     * @param csvExportRow $row
     */
    protected function addColumnsFromRow(csvExportRow $row){
        $this->getColumns()->addColumns($row->getColumns());
    }

    /**
     * @param csvExportRow[] $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        $this->columns = null;

        $this->addColumnsFromRows($rows, true);
    }

    /**
     * @return csvExportRow[]|null
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param csvExportRow $row
     * @param bool $containsNewColumns
     */
    public function addRow(csvExportRow $row, $containsNewColumns = true){
        $this->rows[] = $row;
        if($containsNewColumns || ($this->getColumns()->isEmpty())){
            $this->addColumnsFromRow($row);
        }
    }

    /**
     * @param string $table_name
     */
    public function addDBTable($table_name){
        $this->addDBCustom("SELECT * FROM ".$table_name);
    }

    public function addDBCustom($query){
        global $ilDB;
        /**
         * @var ilDB $ilDB
         */


        $set = $ilDB->query($query);

        while ($record = $ilDB->fetchAssoc($set)) {
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($record);
            $this->addRow($row);
        }
    }

    /**
     * @param \csvExportColumns $columns
     */
    protected function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return \csvExportColumns
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return \csvExportColumn[]
     */
    protected  function getColumnsArray()
    {
        return $this->columns->getColumns();
    }

    /**
     * @param csvExportColumn $column
     */
    public function addColumn(csvExportColumn $column)
    {
        $this->getColumns()->addColumn($column);
    }

    /**
     * @param $columns
     */
    public function addColumnsFromArray($columns){
        $this->getColumns()->addColumnsFromArray($columns);
    }

    /**
     * @param array $rows_of_values
     */
    public function addValuesFromArray($rows_of_values = array()){
        $first = true;
        foreach($rows_of_values as $row_of_values){
            $row = new csvExportRow();
            $row->addValuesFromArray($this->getColumns()->getColumnNamesAsArray(),$row_of_values);
            $this->addRow($row,$first);
            $first = false;
        }
    }

    public function addColumnsAndValuesFromArrays($columns,$rows_of_values ){
        $first = true;
        foreach($rows_of_values as $row_of_values){
            $row = new csvExportRow();
            $row->addValuesFromArray($columns,$row_of_values);
            $this->addRow($row,$first);
            $first = false;
        };
    }

    /**
     * @return array
     */
    public function getRowsValuesAsArray(){
        $values = array();
        foreach($this->getRows() as $row){
            $values[] = $row->getValuesAsArray();
        }
        return $values;
    }

    /**
     * @param $id
     * @param $posititon
     */
    public function setPositionOfColumn($id, $posititon){
        $this->getColumns()->getColumnById($id)->setPosition($posititon);
    }

    /**
     * @return array
     */
    public function getTableAsArray(){
        $values = array();
        $this->getColumns()->sortColumns();
        $this->sortRows();
        $values[0] = $this->getColumns()->getColumnNamesAsArray();
        foreach($this->getRowsValuesAsArray() as $row_id => $row_array){
            $values[1+$row_id] = array();
            foreach($this->getColumnsArray() as $column){
                if(!array_key_exists($column->getColumnId(),$row_array)){
                    $values[1+$row_id][] = null;
                }
                else{
                    $values[1+$row_id][] = $row_array[$column->getColumnId()];
                }

            }
        }
        return $values;
    }

    /**
     *
     */
    public function sortRows(){
        if($this->getSortColumn() && $this->getColumns()->columnExists($this->getSortColumn())){
            $sort_column = $this->getSortColumn();
            uasort($this->rows, function (csvExportRow $row_a, csvExportRow $row_b) use ($sort_column)
            {
                if(is_string($row_a->getValue($sort_column))){
                    return strcmp($row_a->getValue($sort_column), $row_b->getValue($sort_column));
                }
                else{
                    return $row_a->getValue($sort_column) > $row_b->getValue($sort_column);
                }
            });
        }
    }

    /**
     * @param $sort_column
     */
    public function setSortColumn($sort_column)
    {
        $this->sort_column = new csvExportColumn($sort_column);
    }

    /**
     * @return csvExportColumn
     */
    public function getSortColumn()
    {
        return $this->sort_column;
    }


    public function count(){
        return count($this->getRows());
    }

}