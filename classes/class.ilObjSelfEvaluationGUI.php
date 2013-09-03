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

require_once('Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
require_once('class.ilSelfEvaluationPlugin.php');
require_once('class.ilSelfEvaluationScaleFormGUI.php');


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
 * @ilCtrl_isCalledBy ilObjSelfEvaluationGUI: ilRepositoryGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilSelfEvaluationBlockGUI, ilCommonActionDispatcherGUI
 *
 */
class ilObjSelfEvaluationGUI extends ilObjectPluginGUI {

	const DEBUG = false;
	/**
	 * @var ilObjSelfEvaluation
	 */
	public $object;
	/**
	 * @var ilSelfEvaluationPlugin
	 */
	protected $pl;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	/**
	 * @return bool|void
	 */
	public function executeCommand() {
		if (! $this->getCreationMode()) {
			$cmd = $this->ctrl->getCmd();
			$next_class = $this->ctrl->getNextClass($this);
			$this->tpl->getStandardTemplate();
			$this->setTitleAndDescription();
			$this->tpl->setTitleIcon($this->pl->getDirectory() . '/templates/images/icon_xsev_b.png');
			$this->tpl->addCss($this->pl->getStyleSheetLocation("content.css"));
			$this->setTabs();
			switch ($next_class) {
				case '':
					if (! in_array($cmd, get_class_methods($this))) {
						$this->{$this->getStandardCmd()}();
						if (DEBUG) {
							ilUtil::sendInfo("COMMAND NOT FOUND! Redirecting to standard class in ilObjSelfEvaluationGUI executeCommand()");
						}

						return true;
					}
					switch ($cmd) {
						default:
							$this->performCommand($cmd);
							break;
					}
					break;
				default:
					require_once($this->ctrl->lookupClassPath($next_class));
					$gui = new $next_class($this);
					$this->ctrl->forwardCommand($gui);
					break;
			}
			$this->tpl->show();

			return true;
		} else {
			parent::executeCommand();
		}
	}


	protected function afterConstructor() {
		global $tpl, $ilCtrl;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->pl->updateLanguages();
		//		$this->pl->update();
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
			$this->tabs_gui->addTab('administration', $this->txt('administration'), $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'showContent'));
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
		$this->form = new ilPropertyFormGUI();
		// title
		$ti = new ilTextInputGUI($this->txt('title'), 'title');
		$ti->setRequired(true);
		$this->form->addItem($ti);
		// description
		$ta = new ilTextAreaInputGUI($this->txt('description'), 'desc');
		$this->form->addItem($ta);
		// online
		$cb = new ilCheckboxInputGUI($this->txt('online'), 'online');
		$this->form->addItem($cb);
		// intro
		$te = new ilTextAreaInputGUI($this->txt('intro'), 'intro');
		$te->setUseRte(true);
		$this->form->addItem($te);
		// outro
		$te = new ilTextAreaInputGUI($this->txt('outro'), 'outro');
		$te->setUseRte(true);
		$this->form->addItem($te);
		// Buttons
		$this->form->addCommandButton('updateProperties', $this->txt('save'));
		$this->form->setTitle($this->txt('edit_properties'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		// Append
		$aform = new ilSelfEvaluationScaleFormGUI($this->object->getId());
		$this->form = $aform->appendToForm($this->form);
	}


	function getPropertiesValues() {
		$aform = new ilSelfEvaluationScaleFormGUI($this->object->getId());
		$values = $aform->fillForm();
		$values['title'] = $this->object->getTitle();
		$values['desc'] = $this->object->getDescription();
		$values['online'] = $this->object->getOnline();
		$values['intro'] = $this->object->getIntro();
		$values['outro'] = $this->object->getOutro();
		$this->form->setValuesByArray($values);
	}


	public function updateProperties() {
		$this->initPropertiesForm();
		$this->form->setValuesByPost();
		if ($this->form->checkInput()) {
			// Append
			$aform = new ilSelfEvaluationScaleFormGUI($this->object->getId());
			$aform->updateObject();
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setDescription($this->form->getInput('desc'));
			$this->object->setOnline($this->form->getInput('online'));
			$this->object->setIntro($this->form->getInput('intro'));
			$this->object->setOutro($this->form->getInput('outro'));
			$this->object->update();
			ilUtil::sendSuccess($this->txt('msg_obj_modified'), true);
			$this->ctrl->redirect($this, 'editProperties');
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHtml());
	}


	//
	// Show content
	//
	function showContent() {
		$this->tabs_gui->activateTab('content');
		$content = $this->pl->getTemplate('tpl.content.html');
		$content->setVariable('INTRO_HEADER', $this->txt('intro_header'));
		$content->setVariable('INTRO_BODY', $this->object->getIntro());
		if ($this->object->isActive()) {
			$content->setCurrentBlock('button');
			$content->setVariable('START_BUTTON', $this->txt('start_button'));
			$content->setVariable('START_HREF', $this->ctrl->getLinkTargetByClass('ilSelfEvaluationPresentationGUI'));
			$content->parseCurrentBlock();
		}
		$this->tpl->setContent($content->get());
	}
}

?>
