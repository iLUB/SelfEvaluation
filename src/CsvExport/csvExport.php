<?php
namespace ilub\plugin\SelfEvaluation\CsvExport;

class csvExport
{

    /**
     * @var csvExportTable
     */
    protected $table;

    /**
     * @param csvExportTable $table
     */
    function __construct($table = null)
    {
        if ($table) {
            $this->table = $table;
        } else {
            $this->table = new csvExportTable();
        }
    }

    public function getCsvExport($delimiter = ";", $enclosure = '"')
    {
        // output headers so that the file is downloaded rather than displayed
        header('Content-Encoding: UTF-8');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        foreach ($this->getTable()->getTableAsArray() as $row) {
            $utf8_row = [];
            foreach ($row as $entry) {
                $utf8_row[] = $this->convertExcelUtf8($entry);
            }
            fputcsv($output, $utf8_row, $delimiter, $enclosure);
        }
    }

    /**
     * @param $string
     * @return mixed
     */
    protected function convertExcelUtf8($string)
    {
        $string = str_replace("Ä", mb_convert_encoding("Ä", 'UTF-16LE', 'UTF-8'), $string);
        $string = str_replace("Ü", mb_convert_encoding("Ü", 'UTF-16LE', 'UTF-8'), $string);
        $string = str_replace("Ö", mb_convert_encoding("Ö", 'UTF-16LE', 'UTF-8'), $string);
        $string = str_replace("ä", mb_convert_encoding("ä", 'UTF-16LE', 'UTF-8'), $string);
        $string = str_replace("ü", mb_convert_encoding("ü", 'UTF-16LE', 'UTF-8'), $string);
        $string = str_replace("ö", mb_convert_encoding("ö", 'UTF-16LE', 'UTF-8'), $string);
        return $string;
    }

    /**
     * @param csvExportTable $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return csvExportTable
     */
    public function getTable()
    {
        return $this->table;
    }
}