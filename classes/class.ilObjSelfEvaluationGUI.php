<?php
require_once __DIR__ . '/../vendor/autoload.php';

require_once('class.ilSelfEvaluationPlugin.php');
require_once(dirname(__FILE__) . '/Scale/class.ilSelfEvaluationScaleFormGUI.php');
require_once(dirname(__FILE__) . '/Identity/class.ilSelfEvaluationIdentity.php');

/**
 * Class ilObjSelfEvaluationGUI
 *
 * @ilCtrl_isCalledBy ilObjSelfEvaluationGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, , ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationPresentationGUI, ilSelfEvaluationQuestionGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationDatasetGUI, ilSelfEvaluationFeedbackGUI
 * @ilCtrl_Calls      ilObjSelfEvaluationGUI: ilSelfEvaluationMetaQuestionGUI, ilExportGUI
 */
class ilObjSelfEvaluationGUI extends ilObjectPluginGUI
{

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

    /**
     * @var ilObjSelfEvaluation
     */
    public $object;

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    public function displayIdentifier()
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        if ($_GET['uid']) {
            $id = new ilSelfEvaluationIdentity($_GET['uid']);
            if ($id->getType() == ilSelfEvaluationIdentity::TYPE_EXTERNAL && $this->object->isIdentitySelection()) {
                global $ilToolbar;
                $ilToolbar->addText('<b>' . $this->txt('your_uid') . ' ' . $id->getIdentifier() . '</b>');
            }
        }
    }

    public function initHeader()
    {
        $this->setTitleAndDescription();
        $this->displayIdentifier();
        $this->tpl->addCss($this->getPlugin()->getStyleSheetLocation('css/content.css'));
        $this->tpl->addCss($this->getPlugin()->getStyleSheetLocation('css/print.css'), 'print');

        $is_in_survey = $this->ctrl->getCmd() == "showContent" || $this->ctrl->getCmd() == "show" || $this->ctrl->getNextClass($this) == "ilselfevaluationpresentationgui";
        $is_not_logged_in = $this->user->getLogin() == "anonymous";

        if ($is_in_survey && $is_not_logged_in) {
            $this->tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/anonymous.css");
        } else {
            $this->setLocator();
        }
        $this->tpl->addJavaScript($this->getPlugin()->getDirectory() . '/templates/scripts.js');
        $this->setTabs();
    }

    public function executeCommand():bool
    {
        if (!$this->getCreationMode()) {
            if ($this->access->checkAccess('read', '', $_GET['ref_id'])) {
                $this->nav_history->addItem($_GET['ref_id'], $this->ctrl->getLinkTarget($this, $this->getStandardCmd()),
                    $this->getType(), '');
            }

            $cmd = $this->ctrl->getCmd();
            $next_class = $this->ctrl->getNextClass($this);

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
                    $this->tabs->setTabActive('perm_settings');
                    $this->ctrl->forwardCommand($perm_gui);
                    break;
                case 'ilinfoscreengui':
                    include_once('Services/InfoScreen/classes/class.ilInfoScreenGUI.php');
                    $gui = new ilInfoScreenGUI($this);
                    $this->tabs->setTabActive('info_short');
                    $this->ctrl->forwardCommand($gui);
                    break;
                case 'ilselfevaluationlistblocksgui':
                    require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationListBlocksGUI.php');
                    $gui = new ilSelfEvaluationListBlocksGUI($this,$this->tpl,$this->ctrl,$this->toolbar,$this->access,$this->getPlugin());
                    $this->tabs->setTabActive('administration');
                    $this->ctrl->forwardCommand($gui);
                    break;
                case 'ilselfevaluationquestionblockgui':
                    require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationQuestionBlockGUI.php');
                    $block_gui = new ilSelfEvaluationQuestionBlockGUI($this->object->getId(),$this->object->getRefId(),$this->tpl,$this->ctrl,$this->access,$this->getPlugin());
                    $this->tabs->setTabActive('administration');
                    $this->ctrl->forwardCommand($block_gui);
                    break;
                case 'ilselfevaluationmetablockgui':
                    require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationMetaBlockGUI.php');
                    $block_gui = new ilSelfEvaluationMetaBlockGUI($this->object->getId(),$this->object->getRefId(),$this->tpl,$this->ctrl,$this->access,$this->getPlugin());
                    $this->tabs->setTabActive('administration');
                    $this->ctrl->forwardCommand($block_gui);
                    break;
                case 'ilselfevaluationquestiongui':
                    require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationMetaBlock.php');
                    require_once(dirname(__FILE__) . '/Question/class.ilSelfEvaluationMetaQuestionGUI.php');
                    $block = new ilSelfEvaluationMetaBlock((int) $_GET['block_id']);
                    $container_gui = new ilSelfEvaluationQuestionGUI($block->getMetaContainer(),
                        $block->getTitle(), $this->getPlugin(), $this->object->getRefId());
                    $this->ctrl->forwardCommand($container_gui);
                    break;
                case 'ilselfevaluationmetaquestiongui':
                    require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationMetaBlock.php');
                    require_once(dirname(__FILE__) . '/Question/class.ilSelfEvaluationMetaQuestionGUI.php');
                    $block = new ilSelfEvaluationMetaBlock((int) $_GET['block_id']);
                    $container_gui = new ilSelfEvaluationMetaQuestionGUI($block->getMetaContainer(),
                        $block->getTitle(), $this->getPlugin(), $this->object->getRefId());
                    $this->ctrl->forwardCommand($container_gui);
                    break;
                case 'ilselfevaluationfeedbackgui':
                    require_once(dirname(__FILE__) . '/Feedback/class.ilSelfEvaluationFeedbackGUI.php');
                    $gui = new ilSelfEvaluationFeedbackGUI($this,$this->tpl,$this->ctrl,$this->toolbar,$this->access,$this->getPlugin());
                    $this->tabs->setTabActive('administration');
                    $this->ctrl->forwardCommand($gui);
                    break;
                case 'ilexportgui':
                    // only if plugin supports it?
                    $this->tabs->setTabActive("export");
                    include_once './Services/Export/classes/class.ilExportGUI.php';
                    $exp = new ilExportGUI($this);
                    $exp->addFormat('xml');
                    $this->ctrl->forwardCommand($exp);
                    break;
                case 'ilselfevaluationpresentationgui':
                    require_once(dirname(__FILE__) . '/Presentation/class.ilSelfEvaluationPresentationGUI.php');
                    $this->tabs_gui->setTabActive('content');
                    $gui = new ilSelfEvaluationPresentationGUI($this,$this->tpl,$this->ctrl,$this->toolbar);
                    $this->ctrl->forwardCommand($gui);
                    break;
                case 'ilselfevaluationdatasetgui':
                    require_once(dirname(__FILE__) . '/Dataset/class.ilSelfEvaluationDatasetGUI.php');
                    $this->tabs_gui->setTabActive('all_results');
                    $gui = new ilSelfEvaluationDatasetGUI($this,$this->tpl,$this->ctrl,$this->toolbar,$this->access,$this->getPlugin(),$this->user->getId());
                    $this->ctrl->forwardCommand($gui);
                case '':
                default:
                    switch ($cmd) {
                        default:
                            $this->performCommand($cmd);
                            break;
                    }
                    break;
            }
            if ($this->tpl->hide === false OR $this->tpl->hide === null) {
                $this->tpl->printToStdout();
            }

            return true;
        } else {
            return parent::executeCommand();
        }
    }

    /**
     * @return string
     */
    final function getType()
    {
        return 'xsev';
    }

    /**
     * @param $cmd
     */
    function performCommand($cmd)
    {
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
            case 'infoScreen':
                $this->checkPermission('visible');
                $this->ctrl->setCmd("showSummary");
                $this->ctrl->setCmdClass("ilinfoscreengui");
                $this->infoScreen();
                break;
        }
    }

    /**
     * @return string
     */
    function getAfterCreationCmd()
    {
        return 'editProperties';
    }

    /**
     * @return string
     */
    function getStandardCmd()
    {
        return 'showContent';
    }

    function setTabs()
    {
        global $DIC;

        if ($DIC->access()->checkAccess('read', '', $this->object->getRefId())) {
            $this->tabs->addTab('content', $this->txt('content'), $this->ctrl->getLinkTarget($this, 'showContent'));
        }
        $this->addInfoTab();
        if ($DIC->access()->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs->addTab('properties', $this->txt('properties'),
                $this->ctrl->getLinkTarget($this, 'editProperties'));
            $this->tabs->addTab('administration', $this->txt('administration'),
                $this->ctrl->getLinkTargetByClass('ilSelfEvaluationListBlocksGUI', 'showContent'));
        }
        if ($this->object->getAllowShowResults() && !$DIC->user()->isAnonymous()) {
            $this->tabs->addTab('all_results', $this->txt('show_results'),
                $this->ctrl->getLinkTargetByClass('ilSelfEvaluationDatasetGUI', 'index'));
        }

        // Not yet supported for 5.3
        //$this->addExportTab();
        // write
        if ($this->access->checkAccess('write', "", $this->object->getRefId())) {
            $this->tabs->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass("ilexportgui", ''),
                'export',
                'ilexportgui'
            );
        }

        $this->addPermissionTab();
    }

    function editProperties()
    {
        if ($this->object->hasDatasets()) {
            ilUtil::sendInfo($this->txt('scale_cannot_be_edited'));
        }
        $this->tabs->activateTab('properties');
        $this->initPropertiesForm();
        $this->getPropertiesValues();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function initPropertiesForm()
    {
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
        // online
        $cb = new ilCheckboxInputGUI($this->txt('identity_selection'), 'identity_selection');
        $cb->setInfo($this->txt('identity_selection_info'));
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
        $te->setInfo($this->txt('intro_info'));
        $this->form->addItem($te);
        // outro
        $te = new ilTextInputGUI($this->txt('outro_title'), 'outro_title');
        $te->setInfo($this->txt('outro_title_info'));
        $this->form->addItem($te);

        $te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('outro'), 'outro');
        $te->setInfo($this->txt('outro_info'));
        $this->form->addItem($te);
        // identity selection info text for anonymous users
        $te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('identity_selection_text'),
            'identity_selection_info');
        // $te->setRTESupport($this->object->getId(), $this->object->getType(), '', NULL, FALSE, '3.4.7');
        $te->setInfo($this->txt('identity_selection_text_info'));
        $this->form->addItem($te);

        //////////////////////////////
        /////////Block Section////////
        //////////////////////////////
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('block_section'));
        $this->form->addItem($section);

        //Ordering of Questions in Blocks
        $radio_options = new ilRadioGroupInputGUI($this->txt(self::FIELD_ORDER_TYPE),
            self::FIELD_ORDER_TYPE);

        $option_random = new ilRadioOption($this->txt(self::FIELD_ORDER_FULLY_RANDOM),
            self::FIELD_ORDER_FULLY_RANDOM);
        $option_random->setInfo($this->txt("block_option_random_info"));

        $nr_input = new ilNumberInputGUI($this->txt("sort_random_nr_items_block"),
            'sort_random_nr_items_block');
        $option_random->addSubItem($nr_input);

        $te = new ilTinyMceTextAreaInputGUI($this->object, $this->txt('block_option_random_desc'),
            'block_option_random_desc');
        $te->setInfo($this->txt('block_option_random_desc_info'));
        $option_random->addSubItem($te);

        $option_block = new ilRadioOption($this->txt(self::FIELD_ORDER_BLOCK),
            self::FIELD_ORDER_BLOCK);
        $option_block->setInfo($this->txt("block_option_block_info"));

        $cb = new ilCheckboxInputGUI($this->txt(self::FIELD_ORDER_BLOCK_RANDOM),
            self::FIELD_ORDER_BLOCK_RANDOM);
        $option_block->addSubItem($cb);

        $radio_options->addOption($option_random);
        $radio_options->addOption($option_block);
        $radio_options->setRequired(true);
        $this->form->addItem($radio_options);

        // DisplayType
        $se = new ilSelectInputGUI($this->txt('display_type'), 'display_type');
        $se->setInfo($this->txt("display_type_info"));
        $opt = array(
            ilObjSelfEvaluation::DISPLAY_TYPE_SINGLE_PAGE => $this->txt('single_page'),
            ilObjSelfEvaluation::DISPLAY_TYPE_MULTIPLE_PAGES => $this->txt('multiple_pages'),
        );
        $se->setOptions($opt);
        $this->form->addItem($se);

        // Show question block titles during evaluation
        $cb = new ilCheckboxInputGUI($this->txt('show_block_titles_sev'), 'show_block_titles_sev');
        $this->form->addItem($cb);

        // Show question block descriptions during evaluation
        $cb = new ilCheckboxInputGUI($this->txt('show_block_desc_sev'), 'show_block_desc_sev');
        $this->form->addItem($cb);

        //////////////////////////////
        /////////Feedback Section/////
        //////////////////////////////
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('feedback_section'));
        $this->form->addItem($section);

        // Show Feedbacks overview graphics
        $cb = new ilCheckboxInputGUI($this->txt('show_fbs_overview'), 'show_fbs_overview');
        $cb->setValue(1);

        $sub_cb_1 = new ilCheckboxInputGUI($this->txt('show_bar'), 'show_fbs_overview_bar');
        $sub_cb_1->setValue(1);

        $sub_sub_cb_1 = new ilCheckboxInputGUI($this->txt('overview_bar_show_label_as_percentage'),
            'overview_bar_show_label_as_percentage');
        $sub_sub_cb_1->setValue(1);
        $sub_cb_1->addSubItem($sub_sub_cb_1);

        $sub_cb_2 = new ilCheckboxInputGUI($this->txt('show_spider'), 'show_fbs_overview_spider');
        $sub_cb_2->setValue(1);

        $sub_cb_3 = new ilCheckboxInputGUI($this->txt('show_left_right'),
            'show_fbs_overview_left_right');
        $sub_cb_3->setValue(1);

        $cb->addSubItem($sub_cb_1);
        $cb->addSubItem($sub_cb_2);
        $cb->addSubItem($sub_cb_3);

        // Show Feedbacks overview text
        $sub_cb_4 = new ilCheckboxInputGUI($this->txt('show_fbs_overview_text'),
            'show_fbs_overview_text');
        $sub_cb_4->setInfo($this->txt('show_fbs_overview_text_info'));
        $sub_cb_4->setValue(1);
        $cb->addSubItem($sub_cb_4);

        // Show Feedbacks overview statistics
        $sub_cb_5 = new ilCheckboxInputGUI($this->txt('show_fbs_overview_statistics'),
            'show_fbs_overview_statistics');
        $sub_cb_5->setInfo($this->txt('show_fbs_overview_statistics_info'));
        $sub_cb_5->setValue(1);
        $cb->addSubItem($sub_cb_5);

        $this->form->addItem($cb);

        // Show question block titles during feedback
        $cb = new ilCheckboxInputGUI($this->txt('show_block_titles_fb'), 'show_block_titles_fb');
        $this->form->addItem($cb);

        // Show question block descriptions during feedback
        $cb = new ilCheckboxInputGUI($this->txt('show_block_desc_fb'), 'show_block_desc_fb');
        $this->form->addItem($cb);
        //
        $cb = new ilCheckboxInputGUI($this->txt('show_fbs'), 'show_fbs');
        $cb->setValue(1);
        $this->form->addItem($cb);
        //
        $cb = new ilCheckboxInputGUI($this->txt('show_fbs_charts'), 'show_fbs_charts');
        $cb->setValue(1);

        $sub_cb_1 = new ilCheckboxInputGUI($this->txt('show_bar'), 'show_fbs_chart_bar');
        $sub_cb_1->setValue(1);

        $sub_cb_2 = new ilCheckboxInputGUI($this->txt('show_spider'), 'show_fbs_chart_spider');
        $sub_cb_2->setValue(1);

        $sub_cb_3 = new ilCheckboxInputGUI($this->txt('show_left_right'),
            'show_fbs_chart_left_right');
        $sub_cb_3->setValue(1);

        $cb->addSubItem($sub_cb_1);
        $cb->addSubItem($sub_cb_2);
        $cb->addSubItem($sub_cb_3);

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

    function getPropertiesValues()
    {
        $aform = new ilSelfEvaluationScaleFormGUI($this->object->getId(), $this->object->hasDatasets());
        $values = $aform->fillForm();
        $values['title'] = $this->object->getTitle();
        $values['desc'] = $this->object->getDescription();
        $values['online'] = $this->object->getOnline();
        $values['identity_selection'] = $this->object->isIdentitySelection();

        $values['intro'] = $this->object->getIntro();

        if ($this->object->getSortType() == self::ORDER_QUESTIONS_FULLY_RANDOM) {
            $values[self::FIELD_ORDER_TYPE] = self::FIELD_ORDER_FULLY_RANDOM;
        } else {
            $values[self::FIELD_ORDER_TYPE] = self::FIELD_ORDER_BLOCK;
            if ($this->object->getSortType() == self::ORDER_QUESTIONS_BLOCK_RANDOM) {
                $values[self::FIELD_ORDER_BLOCK_RANDOM] = 1;
            }

        }
        $values['sort_random_nr_items_block'] = $this->object->getSortRandomNrItemBlock();
        $values['block_option_random_desc'] = $this->object->getBlockOptionRandomDesc();
        $values['outro_title'] = $this->object->getOutroTitle();
        $values['outro'] = $this->object->getOutro();
        $values['identity_selection_info'] = $this->object->getIdentitySelectionInfoText();
        $values['display_type'] = $this->object->getDisplayType();
        $values['display_type'] = $this->object->getDisplayType();
        $values['show_fbs_overview'] = $this->object->getShowFeedbacksOverview();
        $values['show_fbs_overview_text'] = $this->object->isShowFbsOverviewText();
        $values['show_fbs_overview_statistics'] = $this->object->isShowFbsOverviewStatistics();

        $values['show_fbs'] = $this->object->getShowFeedbacks();
        $values['show_fbs_charts'] = $this->object->getShowFeedbacksCharts();
        $values['show_block_titles_sev'] = $this->object->getShowBlockTitlesDuringEvaluation();
        $values['show_block_desc_sev'] = $this->object->getShowBlockDescriptionsDuringEvaluation();
        $values['show_block_titles_fb'] = $this->object->getShowBlockTitlesDuringFeedback();
        $values['show_block_desc_fb'] = $this->object->getShowBlockDescriptionsDuringFeedback();

        $values['show_fbs_overview_bar'] = $this->object->isShowFbsOverviewBar();
        $values['overview_bar_show_label_as_percentage'] = $this->object->isOverviewBarShowLabelAsPercentage();
        $values['show_fbs_overview_spider'] = $this->object->isShowFbsOverviewSpider();
        $values['show_fbs_overview_left_right'] = $this->object->isShowFbsOverviewLeftRight();

        $values['show_fbs_chart_bar'] = $this->object->isShowFbsChartBar();
        $values['show_fbs_chart_spider'] = $this->object->isShowFbsChartSpider();
        $values['show_fbs_chart_left_right'] = $this->object->isShowFbsChartLeftRight();

        $this->form->setValuesByArray($values);
    }

    public function updateProperties()
    {
        $this->initPropertiesForm();
        $this->form->setValuesByPost();
        if ($this->form->checkInput()) {
            // Append
            $aform = new ilSelfEvaluationScaleFormGUI($this->object->getId(), $this->object->hasDatasets());
            $aform->updateObject();

            $this->object->setTitle($this->form->getInput('title'));
            $this->object->setDescription($this->form->getInput('desc'));
            $this->object->setOnline($this->form->getInput('online'));
            $this->object->setIdentitySelection($this->form->getInput('identity_selection'));
            $this->object->setIntro($this->form->getInput('intro'));
            $this->object->setOutroTitle($this->form->getInput('outro_title'));
            $this->object->setOutro($this->form->getInput('outro'));
            $this->object->setIdentitySelectionInfoText($this->form->getInput('identity_selection_info'));

            if ($this->form->getInput(self::FIELD_ORDER_TYPE) == self::FIELD_ORDER_FULLY_RANDOM) {
                $this->object->setSortType(self::ORDER_QUESTIONS_FULLY_RANDOM);
            } elseif ($this->form->getInput(self::FIELD_ORDER_BLOCK_RANDOM)) {
                $this->object->setSortType(self::ORDER_QUESTIONS_BLOCK_RANDOM);
            } else {
                $this->object->setSortType(self::ORDER_QUESTIONS_STATIC);
            }

            $this->object->setSortRandomNrItemBlock($this->form->getInput('sort_random_nr_items_block'));
            $this->object->setBlockOptionRandomDesc($this->form->getInput('block_option_random_desc'));
            $this->object->setShowBlockTitlesDuringEvaluation($this->form->getInput('show_block_titles_sev'));
            $this->object->setShowBlockDescriptionsDuringEvaluation($this->form->getInput('show_block_desc_sev'));

            $this->object->setDisplayType($this->form->getInput('display_type'));

            $this->object->setShowFeedbacksOverview($this->form->getInput('show_fbs_overview'));
            $this->object->setShowFbsOverviewText($this->form->getInput('show_fbs_overview_text'));
            $this->object->setShowFbsOverviewStatistics($this->form->getInput('show_fbs_overview_statistics'));

            $this->object->setShowFeedbacks($this->form->getInput('show_fbs'));
            $this->object->setShowFeedbacksCharts($this->form->getInput('show_fbs_charts'));
            $this->object->setShowBlockTitlesDuringFeedback($this->form->getInput('show_block_titles_fb'));
            $this->object->setShowBlockDescriptionsDuringFeedback($this->form->getInput('show_block_desc_fb'));

            $this->object->setShowFbsOverviewBar($this->form->getInput('show_fbs_overview_bar'));
            $this->object->setOverviewBarShowLabelAsPercentage($this->form->getInput('overview_bar_show_label_as_percentage'));
            $this->object->setShowFbsOverviewSpider($this->form->getInput('show_fbs_overview_spider'));
            $this->object->setShowFbsOverviewLeftRight($this->form->getInput('show_fbs_overview_left_right'));

            $this->object->setShowFbsChartBar($this->form->getInput('show_fbs_chart_bar'));
            $this->object->setShowFbsChartSpider($this->form->getInput('show_fbs_chart_spider'));
            $this->object->setShowFbsChartLeftRight($this->form->getInput('show_fbs_chart_left_right'));

            $this->object->update();
            ilUtil::sendSuccess($this->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'editProperties');
        }
        $this->tabs->activateTab('properties');
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHtml());
    }


    //
    // Show content
    //
    function showContent()
    {
        global $DIC;
        if ($DIC->user()->isAnonymous()) {
            $this->ctrl->redirectByClass('ilSelfEvaluationIdentityGUI', 'show');
        } else {
            $id = ilSelfEvaluationIdentity::_getInstanceForObjIdAndIdentifier($this->object->getId(),
                $DIC->user()->getId());
            if (!$id) {
                $id = ilSelfEvaluationIdentity::_getNewInstanceForObjIdAndUserId($this->object->getId(),
                    $DIC->user()->getId());
            }
            $this->ctrl->setParameterByClass('ilSelfEvaluationPresentationGUI', 'uid', $id->getId());
            $this->ctrl->redirectByClass('ilSelfEvaluationPresentationGUI', 'startScreen');
        }
    }
}
