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
 * Class ilSelfEvaluationBlockTableData
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationBlockTableData {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilSelfEvaluationPlugin
	 */
	protected $pl;


	public function __construct() {
		global $ilCtrl;
		$this->ctrl = $ilCtrl;
		$this->pl = new ilSelfEvaluationPlugin();
	}


	/**
	 * @param int  $self_eval_id
	 *
	 * @return array
	 */
	public function getTableDataForAllBlocks($self_eval_id) {
		$question_blocks = ilSelfEvaluationQuestionBlock::_getAllInstancesByParentId($self_eval_id, true);
		foreach ($question_blocks as $key => $block) {
			$this->saveCtrlParameters($block['id']);
			$edit = $this->getEditBlockAction();
			$delete = $this->getDeleteBlockAction();
			$question = $this->getQuestionAction();
			$feedback = $this->getFeedbackAction();
			$question_blocks[$key]['edit_link'] = $edit['link'];
			$question_blocks[$key]['delete_link'] =  $delete['link'];
			$question_blocks[$key]['question_link'] = $question['link'];
			$question_blocks[$key]['feedback_link'] = $feedback['link'];
			$question_blocks[$key]['actions'] = array($edit, $delete, $question, $feedback);
		}

		return $question_blocks;
	}


	/**
	 * @return array (title, link, value)
	 */
	protected function getEditBlockAction() {
		$action = array();
		$action['title'] = $this->pl->txt('edit_block');
		$action['link'] = ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionBlockGUI', 'editBlock'));
		$action['value'] = 'edit_block';

		return $action;
	}


	/**
	 * @return array array (title, link, value)
	 */
	protected function getDeleteBlockAction() {
		$action = array();
		$action['title'] = $this->pl->txt('delete_block');
		$action['link'] = ilOverlayRequestGUI::getLink($this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionBlockGUI', 'deleteBlock'));
		$action['value'] = 'delete_block';

		return $action;
	}


	/**
	 * @return array array (title, link, value)
	 */
	protected function getQuestionAction() {
		$action = array();
		$action['title'] = $this->pl->txt('edit_questions');
		$action['link'] = $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'showContent');
		$action['value'] = 'edit_questions';

		return $action;
	}


	/**
	 * @return array array (title, link, value)
	 */
	protected function getFeedbackAction() {
		$action = array();
		$action['title'] = $this->pl->txt('edit_feedback');
		$action['link'] = $this->ctrl->getLinkTargetByClass('ilSelfEvaluationFeedbackGUI');
		$action['value'] = 'edit_feedbacks';

		return $action;
	}


	/**
	 * @param int $id
	 */
	protected function saveCtrlParameters($id) {
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', $id);
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionBlockGUI', 'block_id', $id);
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI', 'block_id', $id);
		$this->ctrl->setParameterByClass('ilSelfEvaluationFeedbackGUI', 'block_id', $id);
	}
} 