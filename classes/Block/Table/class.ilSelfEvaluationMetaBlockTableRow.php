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
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaBlockTableRow extends ilSelfEvaluationBlockTableRow {

	/**
	 * @param ilSelfEvaluationMetaBlock $block
	 */
	public function __construct(ilSelfEvaluationMetaBlock $block) {
		parent::__construct($block);

		$this->setQuestionCount(count($block->getMetaContainer()->getFieldDefinitions()));
		$question_action = $this->getQuestionAction();
		$this->setQuestionsLink($question_action->getLink());
		$this->addAction($question_action);

		$this->setFeedbackCount('-');
		$img_path = ilUtil::getImagePath('icon_ok.png');
		$this->setStatusImg($img_path);
	}


	protected function saveCtrlParameters() {
		$this->ctrl->setParameterByClass('ilSelfEvaluationMetaBlockGUI', 'block_id', $this->getBlockId());
		$this->ctrl->setParameterByClass('ilSelfEvaluationMetaQuestionGUI', 'block_id', $this->getBlockId());
	}


	/**
	 * @return ilSelfEvaluationTableAction
	 */
	protected function getQuestionAction() {
		$title = $this->pl->txt('edit_questions');
		require_once('Customizing/global/plugins/Libraries/iLubFieldDefinition/classes/class.iLubFieldDefinitionContainerGUI.php');
		$link = $this->ctrl->getLinkTargetByClass('ilSelfEvaluationMetaQuestionGUI', 'listFields');
		$cmd = 'edit_questions';

		return new ilSelfEvaluationTableAction($title, $cmd, $link);
	}
}