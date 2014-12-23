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
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlockGUI.php');

/**
 * Class ilSelfEvaluationMetaBlockGUI
 *
 * @ilCtrl_isCalledBy ilSelfEvaluationMetaBlockGUI: ilObjSelfEvaluationGUI
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaBlockGUI extends ilSelfEvaluationBlockGUI {

	/**
	 * @var ilSelfEvaluationMetaBlock
	 */
	protected $object;


	/**
	 * @param ilPropertyFormGUI $parent_form
	 * @param bool              $first
	 *
	 * @return ilPropertyFormGUI
	 */
	public function getBlockForm(ilPropertyFormGUI $parent_form = NULL, $first = true) {
		$form = parent::getBlockForm($parent_form, $first);

		require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationMetaQuestionGUI.php');
		$question_gui = new ilSelfEvaluationMetaQuestionGUI($this->object->getMetaContainer(),
			$this->object->getTitle(), $this->parent->getPluginObject(), $this->parent->object->getRefId());

		return $question_gui->getQuestionForm($form);
	}
}