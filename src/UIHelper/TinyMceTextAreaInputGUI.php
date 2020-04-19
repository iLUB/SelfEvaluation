<?php
namespace ilub\plugin\SelfEvaluation\UIHelper;

use ilTextAreaInputGUI;

class TinyMceTextAreaInputGUI extends ilTextAreaInputGUI
{

    public function __construct(int $obj_id, string $obj_type, $a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setUseRte(true);
        $this->setRTESupport( $obj_id,  $obj_type, '', null, false);
        $this->setRteTagSet('full');
        $this->disableButtons([
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
            'imgupload',
            'ilimgupload']);
    }
}