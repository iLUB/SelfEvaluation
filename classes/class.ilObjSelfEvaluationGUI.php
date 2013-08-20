<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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


require_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');


/**
 * User Interface class for example repository object.
 *
 * @author            Alex Killing <alex.killing@gmx.de>
 * @author            fabian Schmid <fabian.schmid@ilub.unibe.ch>
 *
 * $Id$
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * @ilCtrl_isCalledBy ilObjSelfEvaluationGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
 *
 */
class ilObjSelfEvaluationGUI extends ilObjectPluginGUI {
	protected function afterConstructor() {
		global $tpl, $ilCtrl;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
	}


	/**
	 * @return string
	 */
	final function getType() {
		return 'xsev';
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'editProperties':
			case 'updateProperties':
				$this->checkPermission('write');
				$this->$cmd();
				break;
			case 'showContent':
				$this->checkPermission('read');
				$this->$cmd();
				break;
		}
	}


	/**
	 * @return string
	 */
	function getAfterCreationCmd() {
		return 'editProperties';
	}


	/**
	 * @return string
	 */
	function getStandardCmd() {
		return 'showContent';
	}


	function setTabs() {
		global $ilAccess;
		if ($ilAccess->checkAccess('read', '', $this->object->getRefId())) {
			$this->tabs_gui->addTab('content', $this->txt('content'), $this->ctrl->getLinkTarget($this, 'showContent'));
		}
		$this->addInfoTab();
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->tabs_gui->addTab('properties', $this->txt('properties'), $this->ctrl->getLinkTarget($this, 'editProperties'));
		}
		$this->addPermissionTab();
	}


	function editProperties() {
		global $tpl, $ilTabs;
		$ilTabs->activateTab('properties');
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$tpl->setContent($this->form->getHTML());
	}


	public function initPropertiesForm() {
		global $ilCtrl;
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		// title
		$ti = new ilTextInputGUI($this->txt('title'), 'title');
		$ti->setRequired(true);
		$this->form->addItem($ti);
		// description
		$ta = new ilTextAreaInputGUI($this->txt('description'), 'desc');
		$this->form->addItem($ta);
		// online
		$cb = new ilCheckboxInputGUI($this->lng->txt('online'), 'online');
		$this->form->addItem($cb);
		// option 1
		$ti = new ilTextInputGUI($this->txt('option_one'), 'op1');
		$ti->setMaxLength(10);
		$ti->setSize(10);
		$this->form->addItem($ti);
		// option 2
		$ti = new ilTextInputGUI($this->txt('option_two'), 'op2');
		$ti->setMaxLength(10);
		$ti->setSize(10);
		$this->form->addItem($ti);
		$this->form->addCommandButton('updateProperties', $this->txt('save'));
		$this->form->setTitle($this->txt('edit_properties'));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}


	/**
	 * Get values for edit properties form
	 */
	function getPropertiesValues() {
		$values['title'] = $this->object->getTitle();
		$values['desc'] = $this->object->getDescription();
		$values['online'] = $this->object->getOnline();
//		$values['op1'] = $this->object->getOptionOne();
//		$values['op2'] = $this->object->getOptionTwo();
		$this->form->setValuesByArray($values);
	}


	/**
	 * Update properties
	 */
	public function updateProperties() {
		global $tpl, $lng, $ilCtrl;
		$this->initPropertiesForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setDescription($this->form->getInput('desc'));
			$this->object->setOptionOne($this->form->getInput('op1'));
			$this->object->setOptionTwo($this->form->getInput('op2'));
			$this->object->setOnline($this->form->getInput('online'));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt('msg_obj_modified'), true);
			$ilCtrl->redirect($this, 'editProperties');
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}


	//
	// Show content
	//
	function showContent() {
		global $tpl, $ilTabs;
		$ilTabs->activateTab('content');
		$tpl->setContent('Hello World.');
	}
}

?>
