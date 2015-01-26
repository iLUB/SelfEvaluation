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
require_once(dirname(__FILE__) . '/Table/class.ilSelfEvaluationBlockTableGUI.php');
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlockFactory.php');

/**
 * Class ilSelfEvaluationListBlocksGUI
 *
 * @ilCtrl_isCalledBy ilSelfEvaluationListBlocksGUI: ilObjSelfEvaluationGUI
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationListBlocksGUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilObjSelfEvaluationGUI
	 */
	protected $parent;


	/**
	 * @param ilObjSelfEvaluationGUI $parent
	 */
	public function __construct(ilObjSelfEvaluationGUI $parent) {
		global $ilCtrl;

		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
	}


	public function executeCommand() {
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
		$this->ctrl->saveParameter($this, 'block_id');
		$this->parent->tabs_gui->setTabActive('administration');
		switch ($cmd) {
			default:
				$this->performCommand($cmd);
				break;
		}

		return true;
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'showContent':
			case 'saveSorting':
			$this->parent->permissionCheck('write');
				$this->$cmd();
				break;
		}
	}


	/**
	 * @return string
	 */
	public function getStandardCommand() {
		return 'showContent';
	}


	public function showContent() {
		global $tpl;

		$tpl->addJavaScript($this->parent->getPluginObject()->getDirectory() . '/templates/js/sortable.js');
		$async = new ilOverlayRequestGUI();
//		$async->setAddNewLink($this->ctrl->getLinkTargetByClass('ilselfevaluationquestionblockgui', 'addBlock')); // TODO is this needed?
		$table = new ilSelfEvaluationBlockTableGUI($this->parent, 'showContent');

		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionBlockGUI', 'block_id', NULL);
		$question_block_link = ilOverlayRequestGUI::getLink(
			$this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionBlockGUI', 'addBlock'));
		$table->addHeaderCommand($question_block_link, $this->txt('add_new_question_block'));

		$this->ctrl->setParameterByClass('ilSelfEvaluationMetaBlockGUI', 'block_id', NULL);
		$meta_block_link = ilOverlayRequestGUI::getLink(
			$this->ctrl->getLinkTargetByClass('ilSelfEvaluationMetaBlockGUI', 'addBlock'));
		$table->addHeaderCommand($meta_block_link, $this->txt('add_new_meta_block'));

		$factory = new ilSelfEvaluationBlockFactory($this->getSelfEvalId());
		$blocks = $factory->getAllBlocks();

		$table_data = array();
		foreach ($blocks as $block) {
			$table_data[] = $block->getBlockTableRow()->toArray();
		}

		$table->setData($table_data);
		$tpl->setContent($async->getHTML() . $table->getHTML());
	}


	public function saveSorting() {
		$factory = new ilSelfEvaluationBlockFactory($this->getSelfEvalId());
		$blocks = $factory->getAllBlocks();
		$positions = $_POST['position'];
		foreach ($blocks as $block) {
			$position = (int)array_search($block->getPositionId(), $positions) + 1;
			if ($position) {
				$block->setPosition($position);
				$block->update();
			}
		}

		ilUtil::sendSuccess($this->txt('sorting_saved'), true);
		$this->ctrl->redirect($this, 'showContent');
	}


	/**
	 * @return int
	 */
	protected function getSelfEvalId() {
		return $this->parent->object->getId();
	}


	/**
	 * @param $lng_var
	 *
	 * @return string
	 */
	protected function txt($lng_var) {
		return $this->parent->getPluginObject()->txt($lng_var);
	}
}