<?php
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlockGUI.php');

/**
 * GUI-Class ilSelfEvaluationQuestionBlockGUI
 * @ilCtrl_isCalledBy ilSelfEvaluationQuestionBlockGUI: ilObjSelfEvaluationGUI
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author            Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version           $Id:
 */
class ilSelfEvaluationQuestionBlockGUI extends ilSelfEvaluationBlockGUI
{

    /**
     * @var ilSelfEvaluationQuestionBlock
     */
    protected $object;

    public function initForm($mode = 'create')
    {
        parent::initForm($mode);

        $te = new ilTextInputGUI($this->plugin->txt('abbreviation'), 'abbreviation');
        $te->setInfo($this->plugin->txt("block_abbreviation_info"));
        $te->setMaxLength(8);
        $this->form->addItem($te);
    }

    protected function setObjectValuesByPost()
    {
        parent::setObjectValuesByPost();
        $this->object->setAbbreviation($this->form->getInput('abbreviation'));
    }

    /**
     * @return array (postvar => value) is set to the form
     */
    protected function getObjectValuesAsArray()
    {
        $values = ['abbreviation' => $this->object->getAbbreviation()];

        return array_merge(parent::getObjectValuesAsArray(), $values);
    }
}

