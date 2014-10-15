<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
require_once('./Services/Form/classes/class.ilCustomInputGUI.php');
/**
 * This class represents a custom property in a property form.
 *
 * @author     Alex Killing <alex.killing@gmx.de>
 * @author     Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version    $Id$
 * @ingroup    ServicesForm
 */
class ilMatrixHeaderGUI extends ilCustomInputGUI {

	/**
	 * @var string
	 */
	protected $html = '';
	/**
	 * @var array
	 */
	protected $scale = array();
	/**
	 * @var string
	 */
	protected $block_info = '';


	/**
	 * @param string $a_title
	 * @param string $a_postvar
	 */
	public function __construct($a_title = '', $a_postvar = '') {
		parent::__construct($a_title, $a_postvar);
		$this->setType('marix_header');
	}


	/**
	 * @return string
	 */
	public function getHtml() {
		$pl = new ilSelfEvaluationPlugin();
		$tpl = $pl->getTemplate('default/Form/tpl.matrix_header.html');
		//		$tpl->setVariable('BLOCKINFO', $this->getBlockInfo());
		$width = floor(100 / count($this->getScale()));
		foreach ($this->getScale() as $title) {
		    if ($title == '' || $title == ' '){
                $title = '&nbsp;';
            }
            $title = str_replace('  ','&nbsp;',$title);
            
			$tpl->setCurrentBlock('item');
			$tpl->setVariable('NAME', $title);
			$tpl->setVariable('STYLE', $width . '%');
			$tpl->parseCurrentBlock();
		}

		return $tpl->get();
	}


	/**
	 * @param $a_values
	 */
	public function setValueByArray($a_values) {
		foreach ($this->getSubItems() as $item) {
			$item->setValueByArray($a_values);
		}
	}


	public function insert(&$a_tpl) {
		$a_tpl->setCurrentBlock('prop_custom');
		$a_tpl->setVariable('CUSTOM_CONTENT', $this->getHtml());
		$a_tpl->parseCurrentBlock();
	}


	/**
	 * @return bool
	 */
	public function checkInput() {
		global $lng;
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == '') {
			$this->setAlert($lng->txt('msg_input_is_required'));

			return false;
		}

		return $this->checkSubItemsInput();
	}


	/**
	 * @param mixed $parentform
	 */
	public function setParentform($parentform) {
		$this->parentform = $parentform;
	}


	/**
	 * @return mixed
	 */
	public function getParentform() {
		return $this->parentform;
	}


	/**
	 * @param mixed $parentgui
	 */
	public function setParentgui($parentgui) {
		$this->parentgui = $parentgui;
	}


	/**
	 * @return mixed
	 */
	public function getParentgui() {
		return $this->parentgui;
	}


	/**
	 * @param mixed $postvar
	 */
	public function setPostvar($postvar) {
		$this->postvar = $postvar;
	}


	/**
	 * @return mixed
	 */
	public function getPostvar() {
		return $this->postvar;
	}


	/**
	 * @param array $scale
	 */
	public function setScale($scale) {
		$this->scale = $scale;
	}


	/**
	 * @return array
	 */
	public function getScale() {
		return $this->scale;
	}


	/**
	 * @param string $block_info
	 */
	public function setBlockInfo($block_info) {
		$this->setTitle($block_info);
		$this->block_info = $block_info;
	}


	/**
	 * @return string
	 */
	public function getBlockInfo() {
		return $this->block_info;
	}
}
