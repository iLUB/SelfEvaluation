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

require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlockTableRow.php');
/**
 * Class ilSelfEvaluationBlockTableRow
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationQuestionBlockTableRow extends ilSelfEvaluationBlockTableRow {

	/**
	 * @param ilSelfEvaluationQuestionBlock $block
	 */
	public function __construct(ilSelfEvaluationQuestionBlock $block) {
		parent::__construct($block);

		$questions = ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId());
		$this->setQuestionCount(count($questions));
		$question_action = $this->getQuestionAction();
		$this->setQuestionsLink($question_action->getLink());
		$this->addAction($question_action);

		$feedbacks = ilSelfEvaluationFeedback::_getAllInstancesForParentId($block->getId());
		$this->setFeedbackCount(count($feedbacks));
		$feedback_action = $this->getFeedbackAction();
		$this->setFeedbackLink($feedback_action->getLink());
		$this->addAction($feedback_action);

		if (ilSelfEvaluationFeedback::_isComplete($block->getId())) {
			$img_path = ilUtil::getImagePath('icon_ok.png');
		} else {
			$img_path = ilUtil::getImagePath('icon_not_ok.png');
		}
		$this->setStatusImg($img_path);

		$this->setAbbreviation($block->getAbbreviation());
	}


	protected function saveCtrlParameters() {
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionBlockGUI',    'block_id', $this->getBlockId());
		$this->ctrl->setParameterByClass('ilSelfEvaluationQuestionGUI',         'block_id', $this->getBlockId());
		$this->ctrl->setParameterByClass('ilSelfEvaluationFeedbackGUI',         'block_id', $this->getBlockId());
	}


	/**
	 * @return ilSelfEvaluationTableAction
	 */
	protected function getQuestionAction() {
		$title = $this->pl->txt('edit_questions');
		$link = $this->ctrl->getLinkTargetByClass('ilSelfEvaluationQuestionGUI', 'showContent');
		$cmd = 'edit_questions';

		return new ilSelfEvaluationTableAction($title, $cmd, $link);
	}


	/**
	 * @return ilSelfEvaluationTableAction
	 */
	protected function getFeedbackAction() {
		$title = $this->pl->txt('edit_feedback');
		$link = $this->ctrl->getLinkTargetByClass('ilSelfEvaluationFeedbackGUI', 'listObjects');
		$cmd = 'listObjects';

		return new ilSelfEvaluationTableAction($title, $cmd, $link);
	}
}