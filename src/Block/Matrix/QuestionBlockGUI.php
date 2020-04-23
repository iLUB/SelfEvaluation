<?php
namespace ilub\plugin\SelfEvaluation\Block\Matrix;

use ilub\plugin\SelfEvaluation\Block\BlockGUI;
use ilTextInputGUI;

class QuestionBlockGUI extends BlockGUI
{

    /**
     * @var QuestionBlock
     */
    protected $object;

    public function initForm(string $mode = 'create')
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

    protected function getObjectValuesAsArray() : array
    {
        $values = ['abbreviation' => $this->object->getAbbreviation()];

        return array_merge(parent::getObjectValuesAsArray(), $values);
    }
}

