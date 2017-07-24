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
class ilSelectColorInputOption {

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var string
	 */
	protected $color;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var array
	 */
	protected $style_properties = array();


	/**
	 * @param int    $id        color id
	 * @param string $color     color code
	 * @param string $title     name of the color
	 */
	public function __construct($id, $color, $title = '') {
		$this->setId($id);
		$this->setColor($color);
		$this->setTitle($title);
	}


	/**
	 * @param string $color
	 */
	public function setColor($color) {
		if ($color == 'transparent') {
			$this->color = $color;
		} else {
			require_once('Services/Form/classes/class.ilColorPickerInputGUI.php');
			$hex_code = '#' . ilColorPickerInputGUI::determineHexcode($color);
			$this->color = $hex_code;
		}
	}


	/**
	 * @return string
	 */
	public function getColor() {
		return $this->color;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param array $property
	 */
	public function setStyleProperties($property) {
		$this->style_properties = $property;
	}


	/**
	 * @param string $property
	 */
	public function addStyleProperty($property) {
		$this->style_properties[] = $property;
	}

	/**
	 * @return array
	 */
	public function getStyleProperties() {
		return $this->style_properties;
	}
}