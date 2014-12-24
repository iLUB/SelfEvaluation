<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
/**
 * GUI-Class ilSelfEvaluationPresentationGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id:
 *
 */
class ilSelfEvaluationPresentationFormGUI extends ilPropertyFormGUI{

    /**
     * @var array
     */
    protected $copy_of_buttons = array();

    /**
     * Add Command button
     *
     * @param	string	Command
     * @param	string	Text
     */
    function addCommandButton($a_cmd, $a_text)
    {

        $this->copy_of_buttons[] = array("cmd" => $a_cmd, "text" => $a_text);
        parent::addCommandButton($a_cmd, $a_text);
    }

    /**
     * Remove all command buttons
     */
    function clearCommandButtons()
    {
        $this->copy_of_buttons = array();
        parent::clearCommandButtons();
    }

    /**
     * Get Content.
     */
    function getContent()
    {
        global $lng, $tpl, $ilUser;

        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initEvent();
        ilYuiUtil::initDom();
        ilYuiUtil::initAnimation();

        $tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
        $tpl->addJavaScript("Services/Form/js/Form.js");
        $tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/bootstrap.css");
        $tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/templates/css/grid.css");

        $this->tpl = new ilTemplate("tpl.presentation_form.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/");

        // check if form has not title and first item is a section header
        // -> use section header for title and remove section header
        // -> command buttons are presented on top
        $items = $this->getItems();
        $fi = $items[0];
        if ($this->getMode() == "std" &&
            $this->getTitle() == "" &&
            is_object($fi) && $fi->getType() == "section_header"
        )
        {
            $this->setTitle($fi->getTitle());
            unset($items[0]);
        }


        // title icon
        if ($this->getTitleIcon() != "" && @is_file($this->getTitleIcon()))
        {
            $this->tpl->setCurrentBlock("title_icon");
            $this->tpl->setVariable("IMG_ICON", $this->getTitleIcon());
            $this->tpl->parseCurrentBlock();
        }

        // title
        if ($this->getTitle() != "")
        {
            // commands on top
            if (count($this->copy_of_buttons) > 0 && $this->getShowTopButtons())
            {
                // command buttons
                foreach($this->copy_of_buttons as $button)
                {
                    $this->tpl->setCurrentBlock("cmd2");
                    $this->tpl->setVariable("CMD", $button["cmd"]);
                    $this->tpl->setVariable("CMD_TXT", $button["text"]);
                    $this->tpl->parseCurrentBlock();
                }
                $this->tpl->setCurrentBlock("commands2");
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("header");
            $this->tpl->setVariable("TXT_TITLE", $this->getTitle());
            $this->tpl->setVariable("LABEL", $this->getTopAnchor());
            $this->tpl->setVariable("TXT_DESCRIPTION", $this->getDescription());
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->touchBlock("item");

        // properties
        $this->required_text = false;
        foreach($this->getItems() as $item)
        {
            if ($item->getType() != "hidden")
            {
                $this->insertItem($item);
            }
        }

        // required
        if ($this->required_text && $this->getMode() == "std")
        {
            $this->tpl->setCurrentBlock("required_text");
            $this->tpl->setVariable("TXT_REQUIRED", $lng->txt("required_field"));
            $this->tpl->parseCurrentBlock();
        }

        /** command buttons**/
        foreach($this->copy_of_buttons as $button)
        {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD", $button["cmd"]);
            $this->tpl->setVariable("CMD_TXT", $button["text"]);
            $this->tpl->parseCurrentBlock();
        }

        // try to keep uploads even if checking input fails
        if($this->getMultipart())
        {
            $hash = $_POST["ilfilehash"];
            if(!$hash)
            {
                $hash = md5(uniqid(mt_rand(), true));
            }
            $fhash = new ilHiddenInputGUI("ilfilehash");
            $fhash->setValue($hash);
            $this->addItem($fhash);
        }

        // hidden properties
        $hidden_fields = false;
        foreach($this->getItems() as $item)
        {
            if ($item->getType() == "hidden")
            {
                $item->insert($this->tpl);
                $hidden_fields = true;
            }
        }

        if ($this->required_text ||  $hidden_fields)
        {
            $this->tpl->setCurrentBlock("commands");
            $this->tpl->parseCurrentBlock();
        }

        return $this->tpl->get();
    }
}

?>