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

require_once(dirname(__FILE__) . '/class.ilSelfEvaluationTableAction.php');
/**
 * Class ilSelfEvaluationBlockTableRow
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationBlockTableRow {


	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilSelfEvaluationPlugin
	 */
	protected $pl;
	/**
	 * @var int
	 */
	protected $block_id;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var string
	 */
	protected $abbreviation;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var int
	 */
	protected $question_count;
	/**
	 * @var int
	 */
	protected $feedback_count;
	/**
	 * @var string
	 */
	protected $status_img;
	/**
	 * @var string
	 */
	protected $block_edit_link;
	/**
	 * @var string
	 */
	protected $questions_link;
	/**
	 * @var string
	 */
	protected $feedback_link;
	/**
	 * @var int
	 */
	protected $position_id;
	/**
	 * @var ilSelfEvaluationTableAction[]
	 */
	protected $actions;
	/**
	 * @var string
	 */
	protected $block_gui_class;


	public function __construct(ilSelfEvaluationBlock $block) {
		global $ilCtrl;
		$this->ctrl = $ilCtrl;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->block_gui_class = get_class($block) . 'GUI';

		$this->setBlockId($block->getId());
		$this->setPositionId($block->getPositionId());
		$this->setTitle($block->getTitle());
		$this->setDescription($block->getDescription());

		// actions
		$this->saveCtrlParameters();
		$edit_action = $this->getEditAction();
		$this->setBlockEditLink($edit_action->getLink());
		$this->addAction($edit_action);

		$delete_action = $this->getDeleteAction();
		$this->addAction($delete_action);
	}


	/**
	 * @return array
	 */
	public function toArray() {
		$arr = array();
		$arr['block_id'] = $this->getBlockId();
		$arr['position_id'] = $this->getPositionId();
		$arr['title'] = $this->getTitle();
		$arr['description'] = $this->getDescription();
		$arr['abbreviation'] = $this->getAbbreviation();
		$arr['question_count'] = is_numeric($this->getQuestionCount()) ? $this->getQuestionCount() : 0;
		$arr['feedback_count'] = $this->getFeedbackCount();
		$arr['status_img'] = $this->getStatusImg();
		$arr['edit_link'] = $this->getBlockEditLink();
		$arr['questions_link'] = $this->getQuestionsLink();
		$arr['feedback_link'] = $this->getFeedbackLink();

		$arr['actions'] = serialize($this->getActions());

		return $arr;
	}


	/**
	 * @param string $abbreviation
	 */
	public function setAbbreviation($abbreviation) {
		$this->abbreviation = $abbreviation;
	}


	/**
	 * @return string
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}


	/**
	 * @param string $block_edit_link
	 */
	public function setBlockEditLink($block_edit_link) {
		$this->block_edit_link = $block_edit_link;
	}


	/**
	 * @return string
	 */
	public function getBlockEditLink() {
		return $this->block_edit_link;
	}


	/**
	 * @param int $block_id
	 */
	public function setBlockId($block_id) {
		$this->block_id = $block_id;
	}


	/**
	 * @return int
	 */
	public function getBlockId() {
		return $this->block_id;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param int $feedback_count
	 */
	public function setFeedbackCount($feedback_count) {
		$this->feedback_count = $feedback_count;
	}


	/**
	 * @return int
	 */
	public function getFeedbackCount() {
		return $this->feedback_count;
	}


	/**
	 * @param string $feedback_link
	 */
	public function setFeedbackLink($feedback_link) {
		$this->feedback_link = $feedback_link;
	}


	/**
	 * @return string
	 */
	public function getFeedbackLink() {
		return $this->feedback_link;
	}


	/**
	 * @param int $position_id
	 */
	public function setPositionId($position_id) {
		$this->position_id = $position_id;
	}


	/**
	 * @return int
	 */
	public function getPositionId() {
		return $this->position_id;
	}


	/**
	 * @param int $question_count
	 */
	public function setQuestionCount($question_count) {
		$this->question_count = $question_count;
	}


	/**
	 * @return int
	 */
	public function getQuestionCount() {
		return $this->question_count;
	}


	/**
	 * @param string $questions_link
	 */
	public function setQuestionsLink($questions_link) {
		$this->questions_link = $questions_link;
	}


	/**
	 * @return string
	 */
	public function getQuestionsLink() {
		return $this->questions_link;
	}


	/**
	 * @param string $status_img
	 */
	public function setStatusImg($status_img) {
		$this->status_img = $status_img;
	}


	/**
	 * @return string
	 */
	public function getStatusImg() {
		return $this->status_img;
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
	 * @param \ilSelfEvaluationTableAction[] $actions
	 */
	public function setActions($actions) {
		$this->actions = $actions;
	}


	/**
	 * @return \ilSelfEvaluationTableAction[]
	 */
	public function getActions() {
		return $this->actions;
	}


	/**
	 * @param \ilSelfEvaluationTableAction $action
	 */
	public function addAction($action) {
		$this->actions[] = $action;
	}


	protected function saveCtrlParameters() {
		$this->ctrl->setParameterByClass('ilSelfEvaluationBlockGUI', 'block_id', $this->getBlockId());
	}


	/**
	 * @return ilSelfEvaluationTableAction
	 */
	protected function getEditAction() {
		$title = $this->pl->txt('edit_block');
		$link = $this->ctrl->getLinkTargetByClass($this->block_gui_class, 'editBlock');
		$cmd = 'edit_block';
        $position = 3;
		return new ilSelfEvaluationTableAction($title, $cmd, $link,$position);
	}


	/**
	 * @return ilSelfEvaluationTableAction
	 */
	protected function getDeleteAction() {
		$title = $this->pl->txt('delete_block');
		$link = $this->ctrl->getLinkTargetByClass($this->block_gui_class, 'deleteBlock');
		$cmd = 'delete_block';
        $position = 4;

		return new ilSelfEvaluationTableAction($title, $cmd, $link,$position);
	}
}