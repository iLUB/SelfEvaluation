<?php
namespace ilub\plugin\SelfEvaluation\Player;

use ilPropertyFormGUI;
use ilub\plugin\SelfEvaluation\UIHelper\KnobGUI;

class PlayerFormContainer extends ilPropertyFormGUI
{

    /**
     * @var array
     */
    protected $copy_of_buttons = [];

    /**
     * @var KnobGUI
     */
    protected $knob = null;

    /**
     * @var int
     */
    protected $question_field_size = 6;

    /**
     * Add Command button
     * @param    string    Command
     * @param    string    Text
     */
    public function addCommandButton($a_cmd, $a_text, $a_id = '')
    {

        $this->copy_of_buttons[] = ["cmd" => $a_cmd, "text" => $a_text];
        parent::addCommandButton($a_cmd, $a_text);
    }

    /**
     * Remove all command buttons
     */
    function clearCommandButtons()
    {
        $this->copy_of_buttons = [];
        parent::clearCommandButtons();
    }

    public function addKnob($page, $last_page)
    {
        $this->knob = new KnobGUI($this->tpl,$plugin);
        $this->knob->setValue($page);
        $this->knob->setMax($last_page);

    }

    /**
     * Get Content.
     */
    public function getContent()
    {
        global $lng, $tpl;

        $tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
        $tpl->addJavaScript("Services/Form/js/Form.js");
        $tpl->addJavascript("./Services/UIComponent/Tooltip/js/ilTooltip.js");
        $tpl->addOnLoadCode('il.Tooltip.init();', 3);

        $this->tpl = new ilTemplate("tpl.presentation_form.html", true, true,
            "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/");

        // properties
        $this->required_text = false;
        foreach ($this->getItems() as $item) {

            if ($item->getType() == "section_header" && $this->knob) {
                $this->tpl->setVariable("PROGRESS_KNOB", $this->knob->getHtml());
                //$this->tpl->setVariable("PROP_CLASS","block-header");
            } else {
                //$this->tpl->setVariable("PROP_CLASS","block-question");
            }
            if ($item->getType() != "hidden") {
                $this->tpl->setVariable("RATING_SIZE", 12 - $this->getQuestionFieldSize());
                $this->tpl->setVariable("QUESTION_SIZE", $this->getQuestionFieldSize());
                $this->tpl->setVariable("RATING_SIZE_COMMAND", 12 - $this->getQuestionFieldSize());
                $this->tpl->setVariable("QUESTION_SIZE_COMMAND", $this->getQuestionFieldSize());
                $this->tpl->setVariable("TYPE", $item->getType());

                $this->insertItem($item);
            }
        }

        // required
        if ($this->required_text && $this->getMode() == "std") {
            $this->tpl->setCurrentBlock("required_text");
            $this->tpl->setVariable("TXT_REQUIRED", $lng->txt("required_field"));
            $this->tpl->parseCurrentBlock();
        }

        /** command buttons**/
        foreach ($this->copy_of_buttons as $button) {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD", $button["cmd"]);
            $this->tpl->setVariable("CMD_TXT", $button["text"]);
            $this->tpl->parseCurrentBlock();
        }

        // hidden properties
        $hidden_fields = false;
        foreach ($this->getItems() as $item) {
            if ($item->getType() == "hidden") {
                $item->insert($this->tpl);
                $hidden_fields = true;
            }
        }

        if ($this->required_text || $hidden_fields) {
            $this->tpl->setCurrentBlock("commands");
            $this->tpl->parseCurrentBlock();
        }

        return $this->tpl->get();
    }

    /**
     * @param int $question_field_size
     */
    public function setQuestionFieldSize($question_field_size)
    {
        $this->question_field_size = $question_field_size;
    }

    /**
     * @return int
     */
    public function getQuestionFieldSize()
    {
        return $this->question_field_size;
    }

}

