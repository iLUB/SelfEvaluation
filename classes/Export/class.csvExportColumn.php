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

/**
 * Class csvExportColumn
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class csvExportColumn {
    /**
     * @var string
     */
    protected $column_id = "";

    /**
     * @var string
     */
    protected $column_txt = "";

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @param $column_id
     * @param string $column_txt
     * @param int $position
     */
    function __construct($column_id, $column_txt = "", $position = 0){
        $this->setColumnId($column_id);
        $this->setColumnTxt($column_txt);
        $this->setPosition($position);
    }

    /**
     * @param string $column_id
     */
    public function setColumnId($column_id)
    {
        $this->column_id = $column_id;
    }

    /**
     * @return string
     */
    public function getColumnId()
    {
        return $this->column_id;
    }

    /**
     * @param string $column_txt
     */
    public function setColumnTxt($column_txt)
    {
        $this->column_txt = $column_txt;
    }

    /**
     * @return string
     */
    public function getColumnTxt()
    {
        if($this->column_txt == ""){
            return $this->getColumnId();
        }
        else{
            return $this->column_txt;
        }
    }



    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }


}