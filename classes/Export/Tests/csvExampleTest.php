<?php
require_once('../class.csvExport.php');

/*
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExampleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $columns = array("column1","column2","column3");
    /**
     * @var array
     */
    protected $rows_values = array(array("ce1r1c1","e1r1c2","e1r1c3"),array("ae1r2c1","e1r2c2","e1r2c3"),array("be1r3c1","e1r3c2","e1r3c3"));
    /**
     * @var array
     */
    protected $rows_paired = array(array("column1"=>"e2r1c1","column2"=>"e2r1c2","column3"=>"e2r1c3"),array("column1"=>"e2r2c1","column3"=>"e2r2c3"),array("column3"=>"e2r3c3"),array("columnX"=>"e2r4cX","column1"=>"e2r4c1"));

    protected $csvExport= null;

    public function csvExampleTest()
    {
        $this->csvExport = new csvExport();
    }

    public function testInitTable()
    {
        $this->assertEquals($this->csvExport->getTable()->getColumns()->count(), 0);
        $this->assertEquals($this->csvExport->getTable()->getColumns()->count(), 0);
    }

    /**
     * @depends testInitTable
     */
    public function testAddFromArray()
    {
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);
        $this->assertEquals($this->csvExport->getTable()->getColumns()->count(), 3);
        $this->assertEquals($this->csvExport->getTable()->getColumns()->count(), 3);
        $this->assertEquals($this->csvExport->getTable()->getColumns()->getColumnNamesAsArray(), Array ('column1' => 'column1','column2' => 'column2','column3' => 'column3'));
        $expected_table = Array(
            0=>Array ("column1" => "column1","column2"=>"column2","column3"=> "column3"),
            1=>Array (0 => "ce1r1c1",1=>"e1r1c2",2=>"e1r1c3"),
            2=>Array (0 => "ae1r2c1",1=>"e1r2c2",2=>"e1r2c3"),
            3=>Array (0 => "be1r3c1",1=>"e1r3c2",2=>"e1r3c3"));
        $this->assertEquals($this->csvExport->getTable()->getTableAsArray(), $expected_table);

    }

    /**
     * @depends testInitTable
     */
    public function testPositioningOfColumns()
    {
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);
        $this->csvExport->getTable()->setPositionOfColumn('column1',3);
        $this->csvExport->getTable()->setPositionOfColumn('column2',1);
        $this->csvExport->getTable()->setPositionOfColumn('column3',2);

        $expectd_table = Array(
            0=>Array ("column2" => "column2","column3"=>"column3","column1"=> "column1"),
            1=>Array (0 => "e1r1c2",1=>"e1r1c3",2=>"ce1r1c1"),
            2=>Array (0 => "e1r2c2",1=>"e1r2c3",2=>"ae1r2c1"),
            3=>Array (0 => "e1r3c2",1=>"e1r3c3",2=>"be1r3c1"));
        $this->assertEquals($this->csvExport->getTable()->getTableAsArray(), $expectd_table);
        return $this->csvExport->getTable();

    }

    /**
     * @depends testInitTable
     */
    public function testOrderingOfRows()
    {
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);
        $this->csvExport->getTable()->setSortColumn("column1");

        $expected_table = Array(
            0=>Array ("column1" => "column1","column2"=>"column2","column3"=> "column3"),
            1=>Array (0 => "ae1r2c1",1=>"e1r2c2",2=>"e1r2c3"),
            2=>Array (0 => "be1r3c1",1=>"e1r3c2",2=>"e1r3c3"),
            3=>Array (0 => "ce1r1c1",1=>"e1r1c2",2=>"e1r1c3"));
        $this->assertEquals($this->csvExport->getTable()->getTableAsArray(), $expected_table);
        return $this->csvExport->getTable();

    }

    /**
     * @depends testInitTable
     */
    public function testAddFromPairedArray(){
        foreach($this->rows_paired as $row_paired){
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($row_paired);
            $this->csvExport->getTable()->addRow($row);
        }
        $expected_table = Array(
            0=>Array ("column1" => "column1","column2"=>"column2","column3"=>"column3","columnX"=> "columnX"),
            1=>Array (0 => "e2r1c1",1=>"e2r1c2",2=>"e2r1c3",3=>null),
            2=>Array (0 => "e2r2c1",1 => null, 2=>"e2r2c3",3 => null),
            3=>Array (0 => null,1=>null,2=>"e2r3c3",3=>null),
            4=>Array (0 => "e2r4c1",1=>null,2=>null,3=>"e2r4cX"),
        );
        $this->assertEquals($this->csvExport->getTable()->getTableAsArray(), $expected_table);

    }

    /**
     * @depends testInitTable
     */
    public function testJoinTable()
    {
        foreach($this->rows_paired as $row_paired){
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($row_paired);
            $this->csvExport->getTable()->addRow($row);
        }
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);
        $expected_table = Array(
            0=>Array ("column1" => "column1","column2"=>"column2","column3"=>"column3","columnX"=> "columnX"),
            1=>Array (0 => "e2r1c1",1=>"e2r1c2",2=>"e2r1c3",3=>null),
            2=>Array (0 => "e2r2c1",1 => null, 2=>"e2r2c3",3 => null),
            3=>Array (0 => null,1=>null,2=>"e2r3c3",3=>null),
            4=>Array (0 => "e2r4c1",1=>null,2=>null,3=>"e2r4cX"),
            5=>Array (0 => "ce1r1c1",1=>"e1r1c2",2=>"e1r1c3",3=>null),
            6=>Array (0 => "ae1r2c1",1=>"e1r2c2",2=>"e1r2c3",3=>null),
            7=>Array (0 => "be1r3c1",1=>"e1r3c2",2=>"e1r3c3",3=>null)
        );
        $this->assertEquals($this->csvExport->getTable()->getTableAsArray(), $expected_table);

    }

    /**
     * @depends testInitTable
     */
    public function testJoinTableReversed()
    {
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);

        foreach($this->rows_paired as $row_paired){
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($row_paired);
            $this->csvExport->getTable()->addRow($row);
        }
        $expected_table = Array(
            0=>Array ("column1" => "column1","column2"=>"column2","column3"=>"column3","columnX"=> "columnX"),
            1=>Array (0 => "ce1r1c1",1=>"e1r1c2",2=>"e1r1c3",3=>null),
            2=>Array (0 => "ae1r2c1",1=>"e1r2c2",2=>"e1r2c3",3=>null),
            3=>Array (0 => "be1r3c1",1=>"e1r3c2",2=>"e1r3c3",3=>null),
            4=>Array (0 => "e2r1c1",1=>"e2r1c2",2=>"e2r1c3",3=>null),
            5=>Array (0 => "e2r2c1",1 => null, 2=>"e2r2c3",3 => null),
            6=>Array (0 => null,1=>null,2=>"e2r3c3",3=>null),
            7=>Array (0 => "e2r4c1",1=>null,2=>null,3=>"e2r4cX"),
        );
        $this->assertEquals($this->csvExport->getTable()->getTableAsArray(), $expected_table);
    }

    /**
     * @depends testInitTable
     */
    public function testJoinSortOrderTable()
    {
        $this->csvExport->getTable()->addColumnsAndValuesFromArrays($this->columns,$this->rows_values);

        foreach($this->rows_paired as $row_paired){
            $row = new csvExportRow();
            $row->addValuesFromPairedArray($row_paired);
            $this->csvExport->getTable()->addRow($row);
        }

        $this->csvExport->getTable()->setSortColumn("column2");
        $this->csvExport->getTable()->setPositionOfColumn("column2",-1);

        $expected_table = Array(
            0=>Array ("column2"=>"column2","column1" => "column1","column3"=>"column3","columnX"=> "columnX"),
            1=>Array (0=>null,1 => "e2r2c1", 2=>"e2r2c3",3 => null),
            2=>Array (0=>null, 1 => null,2=>"e2r3c3",3=>null),
            3=>Array (0=>null,1 => "e2r4c1",2=>null,3=>"e2r4cX"),
            4=>Array (0=>"e1r1c2",1 => "ce1r1c1",2=>"e1r1c3",3=>null),
            5=>Array (0=>"e1r2c2",1 => "ae1r2c1",2=>"e1r2c3",3=>null),
            6=>Array (0=>"e1r3c2",1 => "be1r3c1",2=>"e1r3c3",3=>null),
            7=>Array (0=>"e2r1c2",1 => "e2r1c1",2=>"e2r1c3",3=>null),

        );
        $this->assertEquals($this->csvExport->getTable()->getTableAsArray(), $expected_table);

    }
}
?>

