<?php
namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilSubEnabledFormPropertyGUI;
use ilRepositoryObjectPlugin;
use ilTemplate;
use ilUtil;

class MatrixHeaderGUI extends ilSubEnabledFormPropertyGUI
{

    /**
     * @var string
     */
    protected $html = '';
    /**
     * @var array
     */
    protected $scale = [];
    /**
     * @var string
     */
    protected $block_info = '';
    /**
     * @var ilRepositoryObjectPlugin
     */
    protected $plugin;

    public function __construct(ilRepositoryObjectPlugin $plugin, string $a_title = '',string $a_postvar = '')
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType('matrix_header');

        $this->plugin = $plugin;
    }

    public function getHtml() : string
    {
        $tpl = $this->plugin->getTemplate('default/Matrix/tpl.matrix_header.html');

        $width = floor(100 / count($this->getScale()));
        $even = false;
        foreach ($this->getScale() as $title) {
            if ($title == '' || $title == ' ') {
                $title = '&nbsp;';
            }
            $title = str_replace('  ', '&nbsp;', $title);

            $tpl->setCurrentBlock('item');
            $tpl->setVariable('NAME', $title);
            $tpl->setVariable('STYLE', $width . '%');
            $tpl->setVariable('CLASS', $even ? "ilUnitEven" : "ilUnitOdd");
            $even = !$even;
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    public function setValueByArray(array $a_values)
    {
        foreach ($this->getSubItems() as $item) {
            /**
             * @var SliderInputGUI $item
             */
            $item->setValueByArray($a_values);
        }
    }

    public function insert(ilTemplate $a_tpl)
    {
        $a_tpl->setCurrentBlock('prop_custom');
        $a_tpl->setVariable('CUSTOM_CONTENT', $this->getHtml());
        $a_tpl->parseCurrentBlock();
    }

    public function checkInput() : bool
    {
        if (!is_array($_POST[$this->getPostVar()])) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == '') {
                $this->setAlert($this->plugin->txt('msg_input_is_required'));

                return false;
            }
        }

        return $this->checkSubItemsInput();

    }

    /**
     * @param mixed $parentform
     */
    public function setParentform($parentform)
    {
        $this->parentform = $parentform;
    }

    public function getParentform()
    {
        return $this->parentform;
    }

    public function setParentgui($parentgui)
    {
        $this->parentgui = $parentgui;
    }

    public function getParentgui()
    {
        return $this->parentgui;
    }

    public function setPostvar($postvar)
    {
        $this->postvar = $postvar;
    }

    public function getPostvar()
    {
        return $this->postvar;
    }

    public function setScale(array $scale)
    {
        $this->scale = $scale;
    }

    public function getScale() : array
    {
        return $this->scale;
    }

    public function setBlockInfo(string $block_info)
    {
        $this->setTitle($block_info);
        $this->block_info = $block_info;
    }

    public function getBlockInfo() : string
    {
        return $this->block_info;
    }

    public function getRequired() : bool
    {
        return false;
    }
}
