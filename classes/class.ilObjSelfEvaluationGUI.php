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
 * Class ilObjSelfEvaluationGUI
 *
 * @author            Alex Killing <alex.killing@gmx.de>
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 *
 * $Id$
 *
 * @ilCtrl_isCalledBy ilObjSelfEvaluationGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, , ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationBlockGUI, ilSelfEvaluationPresentationGUI, ilSelfEvaluationQuestionGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationDatasetGUI, ilSelfEvaluationFeedbackGUI
 *
 */
class ilObjSelfEvaluationGUI extends ilObjectPluginGUI {

	const DEV = false;
	const DEBUG = false;
	const RELOAD = false;
	protected static $disabled_buttons = array(
		'charmap',
		'undo',
		'redo',
		'justifyleft',
		'justifycenter',
		'justifyright',
		'justifyfull',
		'anchor',
		'fullscreen',
		'cut',
		'copy',
		'paste',
		'pastetext',
		'pasteword',
		'formatselect',
		'imgupload',
		'ilimgupload'
	);
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
	 * @var ilNavigationHistory
	 */
	protected $history;
	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	/**
	 * @var ilTabsGUI
	 */
	public $tabs_gui; // Dirty type hinting fix for nasty implicit declaration by upstream code


	public function displayIdentifier() {
		/**
		 * @var $ilToolbar ilToolbarGUI
		 */
		if ($_GET['uid']) {
			$id = new ilSelfEvaluationIdentity($_GET['uid']);
			if ($id->getType() == ilSelfEvaluationIdentity::TYPE_EXTERNAL) {
				global $ilToolbar;
				$ilToolbar->addText('<b>' . $this->pl->txt('your_uid') . ' ' . $id->getIdentifier() . '</b>');
			}
		}
	}


	public function initHeader() {
		$this->setLocator();
		$this->tpl->getStandardTemplate();
		$this->setTitleAndDescription();
		$this->displayIdentifier();
		$this->tpl->setTitleIcon($this->pl->getDirectory() . '/templates/images/icon_xsev_b.png');
		$this->tpl->addCss($this->pl->getStyleSheetLocation('css/content.css'));
		$this->tpl->addCss($this->pl->getStyleSheetLocation('css/print.css'), 'print');
		$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/scripts.js');
		$this->setTabs();
	}


	/**
	 * @return bool|void
	 */
	public function executeCommand() {
		if (! $this->getCreationMode()) {
			if ($this->access->checkAccess('read', '', $_GET['ref_id'])) {
				$this->history->addItem($_GET['ref_id'], $this->ctrl->getLinkTarget($this, $this->getStandardCmd()), $this->getType(), '');
			}
            
			$cmd = $this->ctrl->getCmd();
			$next_class = $this->ctrl->getNextClass($this);
			if (self::RELOAD OR $_GET['rl'] == 'true') {
				$this->pl->updateLanguages();
			}
			$this->ctrl->saveParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $_GET['uid']);
			$this->ctrl->saveParameterByClass('ilSelfEvaluationDatasetGUI', 'uid', $_GET['uid']);
			$this->ctrl->saveParameterByClass('ilSelfEvaluationFeedbackGUI', 'uid', $_GET['uid']);
			$this->initHeader();
			switch ($next_class) {
				case 'ilcommonactiondispatchergui':
					include_once 'Services/Object/classes/class.ilCommonActionDispatcherGUI.php';
					$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
					$this->ctrl->forwardCommand($gui);
					break;
				case 'ilpermissiongui':
					include_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
					$perm_gui = new ilPermissionGUI($this);
					$this->tabs_gui->setTabActive('perm_settings');
					$this->ctrl->forwardCommand($perm_gui);
					break;
                case 'ilinfoscreengui':
                    $this->tabs_gui->setTabActive('info_short');
                    require_once($this->ctrl->lookupClassPath($next_class));
                    $gui = new $next_class($this);
                    $this->ctrl->forwardCommand($gui);
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
			if ($this->tpl->hide === false OR $this->tpl->hide === NULL) {
				$this->tpl->show();
			}

			return true;
		} else {
			return parent::executeCommand();
		}
	}


	protected function afterConstructor() {
		global $tpl, $ilCtrl, $ilAccess, $ilNavigationHistory;
		/**
		 * @var $tpl                 ilTemplate
		 * @var $ilCtrl              ilCtrl
		 * @var $ilAccess            ilAccessHandler
		 * @var $ilNavigationHistory ilNavigationHistory
		 */
		$this->tpl = $tpl;
		$this->history = $ilNavigationHistory;
		$this->access = $ilAccess;
		$this->ctrl = $ilCtrl;
		$this->pl = new ilSelfEvaluationPlugin();
		if (self::DEBUG OR $_GET['rl'] == 'true') {
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
		/** @var ilAccessHandler $ilAccess */
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
			if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
				$this->tabs_gui->addTab('all_results', $this->txt('show_all_results'), $this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'index'));
			}
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
		require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Form/class.ilTinyMceTextAreaInputGUI.php');
		$te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('intro'), 'intro');
		$te->disableButtons(self::$disabled_buttons);
		$this->form->addItem($te);
		// outro
		$te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('outro'), 'outro');
		$te->disableButtons(self::$disabled_buttons);
		$this->form->addItem($te);
		// Sorting
		$se = new ilSelectInputGUI($this->pl->txt('sort_type'), 'sort_type');
		$opt = array(
			ilObjSelfEvaluation::SORT_MANUALLY => $this->pl->txt('sort_manually'),
			ilObjSelfEvaluation::SORT_SHUFFLE => $this->pl->txt('sort_shuffle'),
		);
		$se->setOptions($opt);
		$this->form->addItem($se);
		// DisplayType
		$se = new ilSelectInputGUI($this->pl->txt('display_type'), 'display_type');
		$opt = array(
			ilObjSelfEvaluation::DISPLAY_TYPE_SINGLE_PAGE => $this->pl->txt('single_page'),
			ilObjSelfEvaluation::DISPLAY_TYPE_MULTIPLE_PAGES => $this->pl->txt('multiple_pages'),
			//			ilObjSelfEvaluation::DISPLAY_TYPE_ALL_QUESTIONS_SHUFFLED => $this->pl->txt('all_questions_shuffled'),
		);
		$se->setOptions($opt);
		$this->form->addItem($se);
		// Show question block titles during evaluation
		$cb = new ilCheckboxInputGUI($this->pl->txt('show_block_titles_sev'), 'show_block_titles_sev');
		$this->form->addItem($cb);
		// Show question block titles during feedback
		$cb = new ilCheckboxInputGUI($this->pl->txt('show_block_titles_fb'), 'show_block_titles_fb');
		$this->form->addItem($cb);
		// Show Feedbacks
		$cb = new ilCheckboxInputGUI($this->pl->txt('show_fbs_overview'), 'show_fbs_overview');
		$cb->setValue(1);
		$this->form->addItem($cb);
		//
		$cb = new ilCheckboxInputGUI($this->pl->txt('show_fbs'), 'show_fbs');
		$cb->setValue(1);
		//
		$cb_a = new ilCheckboxInputGUI($this->pl->txt('show_fbs_charts'), 'show_fbs_charts');
		$cb_a->setValue(1);
		$cb->addSubItem($cb_a);
		$this->form->addItem($cb);
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
		$values['sort_type'] = $this->object->getSortType();
		$values['display_type'] = $this->object->getDisplayType();
		$values['display_type'] = $this->object->getDisplayType();
		$values['show_fbs_overview'] = $this->object->getShowFeedbacksOverview();
		$values['show_fbs'] = $this->object->getShowFeedbacks();
		$values['show_fbs_charts'] = $this->object->getShowFeedbacksCharts();
		$values['show_block_titles_sev'] = $this->object->getShowBlockTitlesDuringEvaluation();
		$values['show_block_titles_fb'] = $this->object->getShowBlockTitlesDuringFeedback();
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
			$this->object->setSortType($this->form->getInput('sort_type'));
			$this->object->setDisplayType($this->form->getInput('display_type'));
			$this->object->setShowFeedbacksOverview($this->form->getInput('show_fbs_overview'));
			$this->object->setShowFeedbacks($this->form->getInput('show_fbs'));
			$this->object->setShowFeedbacksCharts($this->form->getInput('show_fbs_charts'));
			$this->object->setShowBlockTitlesDuringEvaluation($this->form->getInput('show_block_titles_sev'));
			$this->object->setShowBlockTitlesDuringFeedback($this->form->getInput('show_block_titles_fb'));
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
			$this->ctrl->redirectByClass('ilSelfEvaluationIdentityGUI', 'show');
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
