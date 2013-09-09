<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once(dirname(__FILE__) . '/../class.ilObjSelfEvaluationGUI.php');
require_once('class.ilSelfEvaluationFeedback.php');
require_once('class.ilSelfEvaluationFeedbackTableGUI.php');
require_once(dirname(__FILE__) . '/../Form/class.ilSliderInputGUI.php');
/**
 * GUI-Class ilSelfEvaluationFeedbackGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 *
 * @ilCtrl_Calls      ilSelfEvaluationFeedbackGUI:
 * @ilCtrl_IsCalledBy ilSelfEvaluationFeedbackGUI:
 */
class ilSelfEvaluationFeedbackGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;


	function __construct(ilObjSelfEvaluationGUI $parent) {
		global $tpl, $ilCtrl, $ilToolbar;
		/**
		 * @var $tpl       ilTemplate
		 * @var $ilCtrl    ilCtrl
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent = $parent;
		$this->toolbar = $ilToolbar;
		$this->tabs_gui = $this->parent->tabs_gui;
		$this->pl = new ilSelfEvaluationPlugin();
		$this->block = new ilSelfEvaluationBlock($_GET['block_id']);
		if ($_GET['feedback_id']) {
			$this->object = new ilSelfEvaluationFeedback($_GET['feedback_id']);
		} else {
			$this->object = ilSelfEvaluationFeedback::_getNewInstanceByParentId($this->block->getId());
		}
		// jQuery
		$this->tpl->addCss($this->pl->getDirectory()
		. '/templates/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css');
		//		$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/jquery-ui-1.10.3.custom/js/jquery-1.9.1.js');
		$this->tpl->addJavaScript($this->pl->getDirectory()
		. '/templates/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js');
	}


	public function executeCommand() {
		$this->tabs_gui->setTabActive('administration');
		$this->ctrl->saveParameter($this, 'block_id');
		$this->ctrl->saveParameter($this, 'feedback_id');
		$this->ctrl->saveParameterByClass('ilSelfEvaluationBlockGUI', 'block_id');
		$cmd = ($this->ctrl->getCmd()) ? $this->ctrl->getCmd() : $this->getStandardCommand();
		switch ($cmd) {
			default:
				$this->performCommand($cmd);
				break;
		}

		return true;
	}


	/**
	 * @return string
	 */
	public function getStandardCommand() {
		return 'listObjects';
	}


	/**
	 * @param $cmd
	 */
	function performCommand($cmd) {
		switch ($cmd) {
			case 'listObjects':
			case 'addNew':
			case 'cancel':
			case 'createObject':
			case 'updateObject':
			case 'editFeedback':
			case 'checkNextValue':
				//				$this->checkPermission('read'); FSX
				$this->$cmd();
				break;
		}
	}


	public function cancel() {
		$this->ctrl->setParameter($this, 'feedback_id', '');
		$this->ctrl->redirect($this);
	}


	public function listObjects() {
		//$ov = $this->getOverview();
		$table = new ilSelfEvaluationFeedbackTableGUI($this, 'listObjects');
		$this->tpl->setContent($table->getHTML());
	}


	public function addNew() {
		$this->initForm();
		$this->object->setStartValue(ilSelfEvaluationFeedback::_getNextMinValueForParentId($this->block->getId()));
		$this->object->setEndValue(ilSelfEvaluationFeedback::_getNextMaxValueForParentId($this->block->getId(), $this->object->getStartValue()));
		$this->setValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function checkNextValue() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$ignore = ($_GET['feedback_id'] ? $_GET['feedback_id'] : 0);
		$next_min = ilSelfEvaluationFeedback::_getNextMinValueForParentId($_GET['block_id'], 0, $ignore);
		$next_max = ilSelfEvaluationFeedback::_getNextMaxValueForParentId($_GET['block_id'], $next_min, $ignore);
		$state = (($_GET['from'] < $next_min) OR ($_GET['to'] > $next_max)) ? false : true;
		echo json_encode(array(
			'check' => $state,
			'next_from' => $next_min,
			'next_to' => $next_max
		));
		exit;
	}


	public function initForm($mode = 'create') {
		$this->form = new  ilPropertyFormGUI();
		$this->form->setTitle($this->pl->txt($mode . '_feedback_form'));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->addCommandButton($mode . 'Object', $this->pl->txt($mode . '_feedback_button'));
		$this->form->addCommandButton('cancel', $this->pl->txt('cancel'));
		// Title
		$te = new ilTextInputGUI($this->pl->txt('title'), 'title');
		$te->setRequired(true);
		$this->form->addItem($te);
		// Description
		$te = new ilTextInputGUI($this->pl->txt('description'), 'description');
		$this->form->addItem($te);
		// StartValue
		/*$se = new ilSelectInputGUI($this->pl->txt('start_value'), 'start_value');
		for ($x = $max ? $max : 1; $x <= 100; $x ++) {
			$opt[$x] = $x . '%';
		}
		$se->setRequired(true);
		$se->setOptions($opt);
		if (! $max) {
			$se->setDisabled(true);
		} else {
			$se->setValue($max);
		}
		$this->form->addItem($se);
		// EndValue
		$se = new ilSelectInputGUI($this->pl->txt('end_value'), 'end_value');
		$se->setRequired(true);
		$se->setOptions($opt);
		$this->form->addItem($se);*/
		// Slider
		$sl = new ilSliderInputGUI($this->pl->txt('slider'), 'slider', 0, 100, $this->ctrl->getLinkTarget($this, 'checkNextValue'));
		$sl->setRequired(true);
		$this->form->addItem($sl);
		// Feedbacktext
		$te = new ilTextAreaInputGUI($this->pl->txt('feedback_text'), 'feedback_text');
		$te->setUseRte(true);
		$te->setRequired(true);
		$this->form->addItem($te);
	}


	public function createObject() {
		$this->initForm();
		if ($this->form->checkInput()) {
			$obj = ilSelfEvaluationFeedback::_getNewInstanceByParentId($this->block->getId());
			$obj->setTitle($this->form->getInput('title'));
			$obj->setDescription($this->form->getInput('description'));
			$slider = $this->form->getInput('slider');
			$obj->setStartValue($slider[0]);
			$obj->setEndValue($slider[1]);
			$obj->setFeedbackText($this->form->getInput('feedback_text'));
			$obj->create();
			ilUtil::sendSuccess($this->pl->txt('msg_feedback_created'));
			$this->cancel();
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function setValues() {
		$values['title'] = $this->object->getTitle();
		$values['description'] = $this->object->getDescription();
		$values['start_value'] = $this->object->getStartValue();
		$values['end_value'] = $this->object->getEndValue();
		$values['feedback_text'] = $this->object->getFeedbackText();
		$values['slider'] = array( $this->object->getStartValue(), $this->object->getEndValue() );
		$this->form->setValuesByArray($values);
	}


	public function editFeedback() {
		$this->initForm('update');
		$this->setValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function updateObject() {
		$this->initForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput('title'));
			$this->object->setDescription($this->form->getInput('description'));
			$slider = $this->form->getInput('slider');
			$this->object->setStartValue($slider[0]);
			$this->object->setEndValue($slider[1]);
			$this->object->setFeedbackText($this->form->getInput('feedback_text'));
			$this->object->update();
			ilUtil::sendSuccess($this->pl->txt('msg_feedback_created'));
			$this->cancel();
		}
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function getOverview() {
		$ov = $this->pl->getTemplate('default/tpl.feedback_overview.html');
		$last = 1;
		$feedbacks = ilSelfEvaluationFeedback::_getAllInstancesForParentId($this->block->getId());
		foreach ($feedbacks as $i => $fb) {
			$width = $fb->getEndValue() - $fb->getStartValue();
			$ov->setCurrentBlock('fb');
			$ov->setVariable('WIDTH', $width);
			$ov->setVariable('TITLE', $fb->getTitle());
			$this->ctrl->setParameter($this, 'feedback_id', $fb->getId());
			$ov->setVariable('HREF', $this->ctrl->getLinkTarget($this, 'editFeedback'));
			$ov->parseCurrentBlock();
			if ($last != $fb->getStartValue() OR count($feedbacks) == 1 OR ($i == count($feedbacks) - 1 AND
					$fb->getEndValue() != 100)
			) {
				if (count($feedbacks) == 1 OR ($i == count($feedbacks) - 1 AND
						$fb->getEndValue() != 100)
				) {
					$width = 100 - $fb->getEndValue();
				} else {
					$width = $fb->getStartValue() - $last;
				}
				$ov->setCurrentBlock('fb');
				$this->ctrl->setParameter($this, 'feedback_id', NULL);
				$this->ctrl->setParameter($this, 'start_value', $fb->getEndValue());
				$ov->setVariable('WIDTH', $width);
				$ov->setVariable('CSS', '_blank');
				$ov->setVariable('HREF', $this->ctrl->getLinkTarget($this, 'addNew'));
				$ov->parseCurrentBlock();
			}
			$last = $fb->getEndValue();
		}

		return $ov;
	}
}

?>