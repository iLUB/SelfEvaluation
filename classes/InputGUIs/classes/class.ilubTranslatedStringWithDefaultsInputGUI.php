<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2014 ILIAS open source, University of Cologne            |
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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilubTranslatedStringInputGUI.php');

/**
 * Class ilubTranslatedStringWithDefaultsInputGUI
 * An text input GUI with fields for multiple languages and the option to load a default message by clicking on a link.
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilubTranslatedStringWithDefaultsInputGUI extends ilubTranslatedStringInputGUI
{

    /**
     * @var xbgTranslatedString
     */
    protected $standard_translated_string;
    /**
     * @var string
     */
    protected $load_defaults_title;

    /**
     * @param string               $title               the title of the input item
     * @param string               $postvar             the post variable name
     * @param string               $load_defaults_title the text for the link, which loads the default text when clicked
     * @param ilubTranslatedString $default_text        the default texts
     */
    public function __construct($title, $postvar, $load_defaults_title, ilubTranslatedString $default_text)
    {
        parent::__construct($title, $postvar);
        $this->setLoadDefaultsTitle($load_defaults_title);
        $this->setDefaultTranslatedString($default_text);
    }

    /**
     * @param string $load_defaults_title the text for the link, which loads the default text when clicked
     */
    public function setLoadDefaultsTitle($load_defaults_title)
    {
        $this->load_defaults_title = $load_defaults_title;
    }

    /**
     * @return string the text for the link, which loads the default text when clicked
     */
    public function getLoadDefaultsTitle()
    {
        return $this->load_defaults_title;
    }

    /**
     * @param \ilubTranslatedString $string the default texts
     */
    public function setDefaultTranslatedString($string)
    {
        $this->standard_translated_string = $string;
    }

    /**
     * @return \ilubTranslatedString the default texts
     */
    public function getDefaultTranslatedString()
    {
        return $this->standard_translated_string;
    }

    /**
     * Insert property html
     * @param ilTemplate $a_tpl
     */
    public function insert(&$a_tpl)
    {
        parent::insert($a_tpl);
        iljQueryUtil::initjQuery();

        $translations = $this->getDefaultTranslatedString()->getTranslations();
        foreach ($this->sub_items as $item) {
            /** @var ilTextInputGUI|ilTextAreaInputGUI $item */
            $title_tpl = new ilTemplate('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/tpl.ilub_translated_string_with_default_text_input.html',
                true, true);
            $title_tpl->setVariable('TITLE', $item->getTitle());
            $title_tpl->setVariable('LINK_ID', 'default_text_' . $item->getPostVar());
            $title_tpl->setVariable('LINK_TEXT', $this->getLoadDefaultsTitle());
            $title_tpl->setVariable('POSTVAR', $item->getPostVar());
            $lng_key = $this->getLngKey($item->getPostVar());
            if (array_key_exists($lng_key, $translations)) {
                $title_tpl->setVariable('DEFAULT_TEXT', $translations[$lng_key]);
            }
            $item->setTitle($title_tpl->get());
        }
    }

    /**
     * @param string $postvar
     * @return string
     */
    protected function getLngKey($postvar)
    {
        return substr($postvar, -2);
    }
}