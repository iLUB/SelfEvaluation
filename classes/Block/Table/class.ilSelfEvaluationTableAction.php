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
 * Class ilSelfEvaluationTableAction
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationTableAction {

	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $cmd;
	/**
	 * @var string
	 */
	protected $link;

    /**
     * @var int
     */
    protected $position = 0;


    /**
     * @param $title
     * @param $cmd
     * @param $link
     * @param int $position
     */
    public function __construct($title, $cmd, $link, $position = 0) {
		$this->title = $title;
		$this->cmd = $cmd;
		$this->link = $link;
        $this->setPosition($position);
	}


	/**
	 * @return string
	 */
	public function getCmd() {
		return $this->cmd;
	}


	/**
	 * @return string
	 */
	public function getLink() {
		return $this->link;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
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