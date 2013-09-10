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

require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
require_once('class.ilSelfEvaluationPlugin.php');
require_once(dirname(__FILE__) . '/Scale/class.ilSelfEvaluationScaleFormGUI.php');
require_once(dirname(__FILE__) . '/Identity/class.ilSelfEvaluationIdentity.php');


/**
 * User Interface class for example repository object.
 *
 * @author            Alex Killing <alex.killing@gmx.de>
 * @author            fabian Schmid <Fabian.schmid@ilub.unibe.ch>
 *
 * $Id$
 *
 * Integration into control structure:
 * - The GUI class is called by ilRepositoryGUI
 * - GUI classes used by this class are ilPermissionGUI (provides the rbac
 *   screens) and ilInfoScreenGUI (handles the info screen).
 *
 * @ilCtrl_isCalledBy ilObjSelfEvaluationGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, , ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationBlockGUI, ilSelfEvaluationPresentationGUI, ilSelfEvaluationQuestionGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationDatasetGUI, ilSelfEvaluationFeedbackGUI
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
			$this->ctrl->saveParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $_GET['uid']);
			$this->ctrl->saveParameterByClass('ilSelfEvaluationDatasetGUI', 'uid', $_GET['uid']);
			$this->ctrl->saveParameterByClass('ilSelfEvaluationFeedbackGUI', 'uid', $_GET['uid']);
			$cmd = $this->ctrl->getCmd();
			$next_class = $this->ctrl->getNextClass($this);
			if (self::DEBUG) {
				var_dump(array( 'next_class' => $next_class, 'cmd' => $cmd ));
			}
			$this->tpl->getStandardTemplate();
			$this->setTitleAndDescription();
			$this->tpl->setTitleIcon($this->pl->getDirectory() . '/templates/images/icon_xsev_b.png');
			$this->tpl->addCss($this->pl->getStyleSheetLocation('content.css'));
			$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/scripts.js');
			$this->setTabs();
			switch ($next_class) {
				case 'ilpermissiongui':
					include_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
					$perm_gui = new ilPermissionGUI($this);
					$this->tabs_gui->setTabActive('perm_settings');
					$ret = $this->ctrl->forwardCommand($perm_gui);
					break;
				case '':
					if (! in_array($cmd, get_class_methods($this))) {
						$this->performCommand($this->getStandardCmd());
						if (self::DEBUG) {
							ilUtil::sendInfo('COMMAND NOT FOUND! Redirecting to standard class in ilObjSelfEvaluationGUI executeCommand()');
						}
						break;
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
		if (self::DEBUG) {
			$this->pl->updateLanguages();
		}
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
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->object->setAllowShowResults(true);
		}
		if ($ilAccess->checkAccess('read', '', $this->object->getRefId())) {
			$this->tabs_gui->addTab('content', $this->txt('content'), $this->ctrl->getLinkTarget($this, 'showContent'));
		}
		$this->addInfoTab();
		if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->tabs_gui->addTab('properties', $this->txt('properties'), $this->ctrl->getLinkTarget($this, 'editProperties'));
			$this->tabs_gui->addTab('administration', $this->txt('administration'), $this->ctrl->getLinkTargetByClass('ilSelfEvaluationBlockGUI', 'showContent'));
		}
		if (($this->object->getAllowShowResults())
			AND $this->object->hasDatasets()
		) {
			$this->tabs_gui->addTab('results', $this->txt('show_results'), $this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'listObjects'));
		}
		$this->addPermissionTab();
	}


	function editProperties() {
		if ($this->object->hasDatasets()) {
			ilUtil::sendInfo($this->pl->txt('scale_cannot_be_edited'));
		}
		$this->tabs_gui->activateTab('properties');
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$this->tpl->setContent($this->form->getHTML());
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
		$aform = new ilSelfEvaluationScaleFormGUI($this->object->getId(), $this->object->hasDatasets());
		$this->form = $aform->appendToForm($this->form);
	}


	function getPropertiesValues() {
		$aform = new ilSelfEvaluationScaleFormGUI($this->object->getId(), $this->object->hasDatasets());
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
			$aform = new ilSelfEvaluationScaleFormGUI($this->object->getId(), $this->object->hasDatasets());
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
		global $ilUser;
		if (self::_isAnonymous($ilUser->getId())) {
			// redirect to identity generator
		} else {
			$id = ilSelfEvaluationIdentity::_getInstanceForObjId($this->object->getId(), $ilUser->getId());
			$this->ctrl->setParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $id->getId());
			$this->ctrl->redirectByClass('ilSelfEvaluationPresentationGUI', 'startScreen');
		}
	}


	//
	// Helper
	//
	public static function _isAnonymous($user_id) {
		foreach (ilObjUser::_getUsersForRole(ANONYMOUS_ROLE_ID) as $u) {
			if ($u['usr_id'] == $user_id) {
				return true;
			}
		}

		return false;
	}
}

?>
