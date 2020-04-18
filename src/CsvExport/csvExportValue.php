<?php
namespace ilub\plugin\SelfEvaluation\CsvExport;


/**
 * Class csvExportValue
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExportValue
{
    /**
     * @var csvExportColumn
     */
    protected $column = null;
    /**
     * @var string
     */
    protected $value = "";

    /**
     * @param string $column_name
     * @param string $value
     */
    function __construct($column_name, $value)
    {
        $this->column = new csvExportColumn($column_name);
        $this->value = $value;
    }

    /**
     * @param csvExportColumn $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * @return csvExportColumn
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}