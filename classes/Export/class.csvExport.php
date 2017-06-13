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
require_once('class.csvExportTable.php');
/**
 * Class csvExport
 *
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExport {

    /**
     * @var csvExportTable
     */
    protected $table;

    /**
     * @param csvExportTable $table
     */
    function __construct($table = null)
    {
        if($table){
            $this->table = $table;
        }
        else{
            $this->table = new csvExportTable();
        }
    }

    public function getCsvExport($delimiter = ";",$enclosure = '"') {
        // output headers so that the file is downloaded rather than displayed
       header('Content-Encoding: UTF-8');
       header('Content-Type: text/csv; charset=utf-8');
       header('Content-Disposition: attachment; filename=data.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        foreach($this->getTable()->getTableAsArray() as $row){
            $utf8_row = array();
            foreach($row as $entry){
                $utf8_row[] = $this->convertExcelUtf8($entry);
            }
            fputcsv($output, $utf8_row,$delimiter,$enclosure);
        }
    }

    /**
     * @param $string
     * @return mixed
     * Todo: Stupid fix, must be a better way to do this, so that special chars look properly in mac excel
     */
    protected function convertExcelUtf8($string){
        $string = str_replace("Ä",mb_convert_encoding("Ä", 'UTF-16LE', 'UTF-8'),$string);
        $string = str_replace("Ü",mb_convert_encoding("Ü", 'UTF-16LE', 'UTF-8'),$string);
        $string = str_replace("Ö",mb_convert_encoding("Ö", 'UTF-16LE', 'UTF-8'),$string);
        $string = str_replace("ä",mb_convert_encoding("ä", 'UTF-16LE', 'UTF-8'),$string);
        $string = str_replace("ü",mb_convert_encoding("ü", 'UTF-16LE', 'UTF-8'),$string);
        $string = str_replace("ö",mb_convert_encoding("ö", 'UTF-16LE', 'UTF-8'),$string);
        return $string;
    }

    /**
     * @param \csvExportTable $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return \csvExportTable
     */
    public function getTable()
    {
        return $this->table;
    }
}