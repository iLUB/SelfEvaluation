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
require_once('class.csvExportValue.php');
require_once('class.csvExportColumns.php');
require_once(dirname(__FILE__) . '/Exceptions/class.csvExportException.php');

/**
 * Class csvExportRow
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExportRow {
    /**
     * @var csvExportValue[]
     */
    protected $values = array();

    /**
     * @var csvExportColumns
     */
    protected $columns = null;

    /**
     * @param csvExportValue[] $values
     */
    function __construct($values = array())
    {
        $this->columns = new csvExportColumns();
        $this->values = $values;
        foreach($values as $value){
            $this->addValue($value);
        }
    }

    /**
     * @param csvExportValue[] $values
     */
    public function setValues($values)
    {
        unset($this->values);
        $this->getColumns()->reset();
        foreach($values as $value){
            $this->addValue($value);
        }
    }

    /**
     * @return csvExportValue[]|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param csvExportColumn $column
     * @return csvExportValue
     */
    public function getValue(csvExportColumn $column)
    {
        return $this->values[$column->getColumnId()];
    }

    /**
     * @param csvExportValue $value
     * @throws csvExportException
     */
    public function addValue(csvExportValue $value){
        if($this->getColumns()->columnExists($value->getColumn())){
            throw new csvExportException(csvExportException::COLUMN_DOES_ALREADY_EXISTS_IN_ROW);
        }
        $this->columns->addColumn($value->getColumn());
        $this->values[$value->getColumn()->getColumnId()] = $value;
    }

    public function getColumns(){
        return $this->columns;
    }

    /**
     * @param $values
     */
    public function addValuesFromArray($column_names, $values){
        foreach($values as $value){
            $this->addValue(new csvExportValue(array_shift($column_names),$value));
        }
    }
    /**
     * @param $values
     */
    public function addValuesFromPairedArray($values){
        foreach($values as $column_name => $value){
            $this->addValue(new csvExportValue($column_name,$value));
        }
    }

    public function getValuesAsArray(){
        $values = array();
        foreach($this->getValues() as $value){
            $values[$value->getColumn()->getColumnId()] = $value->getValue();
        }
        return $values;
    }

}