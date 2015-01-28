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
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationPresentationGUI, ilSelfEvaluationQuestionGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationDatasetGUI, ilSelfEvaluationFeedbackGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationMetaQuestionGUI
 */
class ilObjSelfEvaluationGUI extends ilObjectPluginGUI {

	const DEV = false;
	const DEBUG = false;
	const RELOAD = false; // set to true or use the GET parameter rl=true to reload the plugin languages

    const ORDER_QUESTIONS_STATIC = 1;
    const ORDER_QUESTIONS_BLOCK_RANDOM = 2;
    const ORDER_QUESTIONS_FULLY_RANDOM = 3;

    const FIELD_ORDER_TYPE = 'block_presentation_type';
    const FIELD_ORDER_FULLY_RANDOM = 'block_option_random';
    const FIELD_ORDER_BLOCK = 'block_option_block';
    const FIELD_ORDER_BLOCK_RANDOM = 'shuffle_in_blocks';


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
				$ilToolbar->addText('<b>' . $this->getPluginObject()->txt('your_uid') . ' ' . $id->getIdentifier() . '</b>');
			}
		}
	}


	public function initHeader() {
        global $ilUser;
        /**
         * @var $ilUser ilObjUser
         */

		$this->tpl->getStandardTemplate();
		$this->setTitleAndDescription();
		$this->displayIdentifier();
		$this->tpl->setTitleIcon($this->getPluginObject()->getDirectory() . '/templates/images/icon_xsev_b.png');
		$this->tpl->addCss($this->getPluginObject()->getStyleSheetLocation('css/content.css'));
		$this->tpl->addCss($this->getPluginObject()->getStyleSheetLocation('css/print.css'), 'print');

        $is_in_survey = $this->ctrl->getCmd() == "showContent" || $this->ctrl->getCmd() == "show" || $this->ctrl->getNextClass($this)=="ilselfevaluationpresentationgui";
        $is_not_logged_in = $ilUser->login == "anonymous";

        if($is_in_survey && $is_not_logged_in){
            $this->tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/anonymous.css");
        }
        else{
            $this->setLocator();
        }
		$this->tpl->addJavaScript($this->getPluginObject()->getDirectory() . '/templates/scripts.js');
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
				$this->getPluginObject()->updateLanguages();
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
	                include_once('Services/InfoScreen/classes/class.ilInfoScreenGUI.php');
	                $gui = new ilInfoScreenGUI($this);
	                $this->tabs_gui->setTabActive('info_short');
	                $this->ctrl->forwardCommand($gui);
                    break;
				case 'ilselfevaluationlistblocksgui':
					require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationListBlocksGUI.php');
					$gui = new ilSelfEvaluationListBlocksGUI($this);
					$this->tabs_gui->setTabActive('administration');
					$this->ctrl->forwardCommand($gui);
					break;
				case 'ilselfevaluationquestionblockgui':
					require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationQuestionBlockGUI.php');
					$block = new ilSelfEvaluationQuestionBlock((int)$_GET['block_id']);
					$block->setParentId($this->object->getId());
					$block_gui = new ilSelfEvaluationQuestionBlockGUI($this, $block);
					$this->ctrl->forwardCommand($block_gui);
					break;
				case 'ilselfevaluationmetablockgui':
					require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationMetaBlockGUI.php');
					require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationMetaBlock.php');
					$block = new ilSelfEvaluationMetaBlock((int)$_GET['block_id']);
					$block->setParentId($this->object->getId());
					$block_gui = new ilSelfEvaluationMetaBlockGUI($this, $block);
					$this->ctrl->forwardCommand($block_gui);
					break;
				case 'ilselfevaluationmetaquestiongui':
					require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationMetaBlock.php');
					require_once(dirname(__FILE__) . '/Question/class.ilSelfEvaluationMetaQuestionGUI.php');
					$block = new ilSelfEvaluationMetaBlock((int)$_GET['block_id']);
					$container_gui = new ilSelfEvaluationMetaQuestionGUI($block->getMetaContainer(),
						$block->getTitle(), $this->getPluginObject(), $this->object->getRefId());
					$this->ctrl->forwardCommand($container_gui);
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
		if (self::DEBUG OR $_GET['rl'] == 'true') {
			$this->getPluginObject()->updateLanguages();
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
			$this->tabs_gui->addTab('administration', $this->txt('administration'), $this->ctrl->getLinkTargetByClass('ilSelfEvaluationListBlocksGUI', 'showContent'));
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


    /**
     * Init creation froms
     *
     * this will create the default creation forms: new, import, clone
     *
     * @param	string	$a_new_type
     * @return	array
     */
    protected function initCreationForms($a_new_type)
    {
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm($a_new_type),
        );

        return $forms;
    }

	function editProperties() {
		if ($this->object->hasDatasets()) {
			ilUtil::sendInfo($this->getPluginObject()->txt('scale_cannot_be_edited'));
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

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('help_text_section'));
        $this->form->addItem($section);

        //////////////////////////////
        /////////Text Section////////
        //////////////////////////////
		// intro
		require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Form/class.ilTinyMceTextAreaInputGUI.php');
		$te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('intro'), 'intro');
		$te->disableButtons(self::$disabled_buttons);
        $te->setInfo($this->txt('intro_info'));
		$this->form->addItem($te);
		// outro
		$te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('outro'), 'outro');
		$te->disableButtons(self::$disabled_buttons);
        $te->setInfo($this->txt('outro_info'));
		$this->form->addItem($te);
		// identity selection info text for anonymous users
		$te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('identity_selection'), 'identity_selection_info');
		$te->setInfo($this->txt('identity_selection_info'));
		$te->disableButtons(self::$disabled_buttons);
		$this->form->addItem($te);

        //////////////////////////////
        /////////Block Section////////
        //////////////////////////////
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('block_section'));
        $this->form->addItem($section);

        //Ordering of Questions in Blocks
        $radio_options = new ilRadioGroupInputGUI($this->getPluginObject()->txt(self::FIELD_ORDER_TYPE),self::FIELD_ORDER_TYPE);

        $option_random = new ilRadioOption($this->getPluginObject()->txt(self::FIELD_ORDER_FULLY_RANDOM),self::FIELD_ORDER_FULLY_RANDOM);
        $option_random->setInfo($this->getPluginObject()->txt("block_option_random_info"));

        $nr_input = new ilNumberInputGUI($this->getPluginObject()->txt("sort_random_nr_items_block"),'sort_random_nr_items_block');
        $option_random->addSubItem($nr_input);

        $option_block = new ilRadioOption($this->getPluginObject()->txt(self::FIELD_ORDER_BLOCK),self::FIELD_ORDER_BLOCK);
        $option_block->setInfo($this->getPluginObject()->txt("block_option_block_info"));

        $cb = new ilCheckboxInputGUI($this->getPluginObject()->txt(self::FIELD_ORDER_BLOCK_RANDOM), self::FIELD_ORDER_BLOCK_RANDOM);
        $option_block->addSubItem($cb);

        $radio_options->addOption($option_random);
        $radio_options->addOption($option_block);
        $radio_options->setRequired(true);
        $this->form->addItem($radio_options);

        // DisplayType
        $se = new ilSelectInputGUI($this->getPluginObject()->txt('display_type'), 'display_type');
        $se->setInfo($this->getPluginObject()->txt("display_type_info"));
        $opt = array(
            ilObjSelfEvaluation::DISPLAY_TYPE_SINGLE_PAGE => $this->getPluginObject()->txt('single_page'),
            ilObjSelfEvaluation::DISPLAY_TYPE_MULTIPLE_PAGES => $this->getPluginObject()->txt('multiple_pages'),
        );
        $se->setOptions($opt);
        $this->form->addItem($se);

        // Show question block titles during evaluation
        $cb = new ilCheckboxInputGUI($this->getPluginObject()->txt('show_block_titles_sev'), 'show_block_titles_sev');
        $this->form->addItem($cb);

        // Show question block descriptions during evaluation
        $cb = new ilCheckboxInputGUI($this->getPluginObject()->txt('show_block_desc_sev'), 'show_block_desc_sev');
        $this->form->addItem($cb);

        //////////////////////////////
        /////////Feedback Section/////
        //////////////////////////////
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('feedback_section'));
        $this->form->addItem($section);

        // Show Feedbacks overview graphics
        $cb = new ilCheckboxInputGUI($this->getPluginObject()->txt('show_fbs_overview'), 'show_fbs_overview');
        $cb->setValue(1);
        $this->form->addItem($cb);
		// Show question block titles during feedback
		$cb = new ilCheckboxInputGUI($this->getPluginObject()->txt('show_block_titles_fb'), 'show_block_titles_fb');
		$this->form->addItem($cb);
		// Show question block descriptions during feedback
		$cb = new ilCheckboxInputGUI($this->getPluginObject()->txt('show_block_desc_fb'), 'show_block_desc_fb');
		$this->form->addItem($cb);
		//
		$cb = new ilCheckboxInputGUI($this->getPluginObject()->txt('show_fbs'), 'show_fbs');
		$cb->setValue(1);
        $this->form->addItem($cb);
		//
        $cb = new ilCheckboxInputGUI($this->getPluginObject()->txt('show_fbs_charts'), 'show_fbs_charts');
        $cb->setValue(1);
        $this->form->addItem($cb);


        //////////////////////////////
        /////////Scale Section////////
        //////////////////////////////
        // Append
        $aform = new ilSelfEvaluationScaleFormGUI($this->object->getId(), $this->object->hasDatasets());
        $this->form = $aform->appendToForm($this->form);

		// Buttons
		$this->form->addCommandButton('updateProperties', $this->txt('save'));
		$this->form->setTitle($this->txt('edit_properties'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	}


	function getPropertiesValues() {
		$aform = new ilSelfEvaluationScaleFormGUI($this->object->getId(), $this->object->hasDatasets());
		$values = $aform->fillForm();
		$values['title'] = $this->object->getTitle();
		$values['desc'] = $this->object->getDescription();
		$values['online'] = $this->object->getOnline();
		$values['intro'] = $this->object->getIntro();

        if($this->object->getSortType() == self::ORDER_QUESTIONS_FULLY_RANDOM){
            $values[self::FIELD_ORDER_TYPE] =self::FIELD_ORDER_FULLY_RANDOM;
        }
        else{
            $values[self::FIELD_ORDER_TYPE] =self::FIELD_ORDER_BLOCK;
            if($this->object->getSortType() == self::ORDER_QUESTIONS_BLOCK_RANDOM){
                $values[self::FIELD_ORDER_BLOCK_RANDOM] = 1;
            }

        }
        $values['sort_random_nr_items_block'] = $this->object->getSortRandomNrItemBlock();
		$values['outro'] = $this->object->getOutro();
		$values['identity_selection_info'] = $this->object->getIdentitySelectionInfoText();
		$values['display_type'] = $this->object->getDisplayType();
		$values['display_type'] = $this->object->getDisplayType();
		$values['show_fbs_overview'] = $this->object->getShowFeedbacksOverview();
		$values['show_fbs'] = $this->object->getShowFeedbacks();
		$values['show_fbs_charts'] = $this->object->getShowFeedbacksCharts();
		$values['show_block_titles_sev'] = $this->object->getShowBlockTitlesDuringEvaluation();
		$values['show_block_desc_sev'] = $this->object->getShowBlockDescriptionsDuringEvaluation();
		$values['show_block_titles_fb'] = $this->object->getShowBlockTitlesDuringFeedback();
		$values['show_block_desc_fb'] = $this->object->getShowBlockTitlesDuringFeedback();
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
			$this->object->setIdentitySelectionInfoText($this->form->getInput('identity_selection_info'));

            if($this->form->getInput(self::FIELD_ORDER_TYPE) == self::FIELD_ORDER_FULLY_RANDOM ){
                $this->object->setSortType(self::ORDER_QUESTIONS_FULLY_RANDOM);
            }
			elseif($this->form->getInput(self::FIELD_ORDER_BLOCK_RANDOM)){
                $this->object->setSortType(self::ORDER_QUESTIONS_BLOCK_RANDOM);
            }
            else{
                $this->object->setSortType(self::ORDER_QUESTIONS_STATIC);
            }

            $this->object->setSortRandomNrItemBlock($this->form->getInput('sort_random_nr_items_block'));
            $this->object->setShowBlockTitlesDuringEvaluation($this->form->getInput('show_block_titles_sev'));
            $this->object->setShowBlockDescriptionsDuringEvaluation($this->form->getInput('show_block_desc_sev'));

			$this->object->setDisplayType($this->form->getInput('display_type'));
			$this->object->setShowFeedbacksOverview($this->form->getInput('show_fbs_overview'));
			$this->object->setShowFeedbacks($this->form->getInput('show_fbs'));
			$this->object->setShowFeedbacksCharts($this->form->getInput('show_fbs_charts'));
			$this->object->setShowBlockTitlesDuringFeedback($this->form->getInput('show_block_titles_fb'));
			$this->object->setShowBlockDescriptionsDuringFeedback($this->form->getInput('show_block_desc_fb'));
			$this->object->update();
			ilUtil::sendSuccess($this->txt('msg_obj_modified'), true);
			$this->ctrl->redirect($this, 'editProperties');
		}
        $this->tabs_gui->activateTab('properties');
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


	//
	// Make important but unfortunately as 'final' declared methods available
	//

	/**
	 * Get plugin object
	 *
	 * @return ilSelfEvaluationPlugin plugin object
	 */
	public function getPluginObject() {
		return $this->plugin;
	}


	/**
	 * @param string $permission
	 * @param string $cmd
	 *
	 * @return bool
	 */
	public function permissionCheck($permission, $cmd = '') {
		return $this->checkPermission($permission, $cmd);
	}
}

?>
