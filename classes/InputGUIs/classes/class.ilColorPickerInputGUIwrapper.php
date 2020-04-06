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

/**
 * Class ilColorPickerInputGUIwrapper
 * This class extends the ilColorPickerInputGUI's JavaScript such that set color is already displayed by default.
 * Further, entering a hex code into the text input field updates the displayed color.
 * These changes are included in Ilias since version 4.4.5. This wrapper provides these changes to older versions of
 * Ilias
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 * @uses    ilColorPickerInputGUI
 */
class ilColorPickerInputGUIwrapper extends ilColorPickerInputGUI
{

    /**
     * @param ilTemplate $a_tpl
     * @return int|void
     */
    function insert(&$a_tpl)
    {
        parent::insert($a_tpl);
        $a_tpl->setCurrentBlock('prop_custom');

        $js_tpl = new ilTemplate('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/tpl.il_color_picker_wrapper.html',
            true, true);
        $js_tpl->setVariable('INIT_COLOR', '#' . $this->getHexcode());
        $js_tpl->setVariable('POST_VAR', $this->getPostVar());

        $a_tpl->setVariable('CUSTOM_CONTENT', $js_tpl->get());
        $a_tpl->parseCurrentBlock();
    }
}