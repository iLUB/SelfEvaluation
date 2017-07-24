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
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Export/class.csvExport.php');
/**
 * Class csvExample
 *
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExample {
    /**
     * @var array
     */
    protected $columns = array("column1","column2","column3");
    /**
     * @var array
     */
    protected $rows_values = array(array("e1r1c1","e1r1c2","e1r1c3"),array("e1r2c1","e1r2c2","e1r2c3"),array("e1r3c1","e1r3c2","e1r3c3"));
    /**
     * @var array
     */
    protected $rows_paired = array(array("column1"=>"e2r1c1","column2"=>"e2r1c2","column3"=>"e2r1c3"),array("column1"=>"e2c1","column3"=>"e2r2c3"),array("column3"=>"e2r3c3"),array("columnX"=>"e2r4cX","column1"=>"e2r4c1"));


    /**
     * If csvExport is part of a bigger class. Maybe its better to outfactor specific csv export in a module and
     * make an own csvExport clas extending from csvExport
     * @var csvExportTable
     */
    protected $csvExport= null;

    function __construct(){
        $this->csvExport = new csvExport();
    }
    /**
     * First example with $rows_values and $columns, rows must contain all values
     */
    public function example1(){
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);
        $this->csvExport->getCsvExport();
    }

    /**
     * second example with $rows_paired, missing values allowed
     */
    public function example2(){
        foreach($this->rows_paired as $row_paired){
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($row_paired);
            $this->csvExport->getTable()->addRow($row);
        }
        $this->csvExport->getCsvExport();

    }

    /**
     * Join the two previous example into one csv table and add one row with two values
     */
    public function example3(){
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);

        foreach($this->rows_paired as $row_paired){
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($row_paired);
            $this->csvExport->getTable()->addRow($row);
        }

        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);
        $this->csvExport->getTable()->addRow(new csvExportRow(
            array(new csvExportValue("column1","e3r1c1"),new csvExportValue("columnY","e3r1cY") )));
        $this->csvExport->getCsvExport();

    }

     /**
     * Reorder positions of Columns
     */
    public function example4(){
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);

        foreach($this->rows_paired as $row_paired){
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($row_paired);
            $this->csvExport->getTable()->addRow($row);
        }

        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);
        $this->csvExport->getTable()->addRow(new csvExportRow(
            array(new csvExportValue("column1","e3r1c1"),new csvExportValue("columnY","e3r1cY") )));

        $this->csvExport->getTable()->setPositionOfColumn("columnY",-1);
        $this->csvExport->getTable()->setPositionOfColumn("column2",1);
        $this->csvExport->getTable()->setPositionOfColumn("column1",3);
        $this->csvExport->getTable()->setPositionOfColumn("column3",2);
        $this->csvExport->getCsvExport();

    }

    /**
     * Export Tablefrom DB
     */
    public function example5(){
        $this->csvExport->getTable()->addDBTable("il_plugin");
        $this->csvExport->getTable()->setPositionOfColumn("plugin_id",-1);
        $this->csvExport->getCsvExport();
    }
}