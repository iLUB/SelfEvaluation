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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/classes/class.ilSelectInputGUIwrapper.php');

/**
 * Class ilSelectInputGUIwrapper
 * Improve input validation check: allowed POST values should match one of the set options.
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelectInputGUIwithDefaultText extends ilSelectInputGUIwrapper
{

    const IGNORE_KEY = 'ilsel_dummy';

    /**
     * Prepends the options with an information text "select an item"
     * @param array $a_options
     */
    function setOptions($a_options)
    {
        global $lng;

        parent::setOptions(array(self::IGNORE_KEY => $lng->txt('select_one')) + $a_options);
    }

    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     * @return    boolean        Input ok, true/false
     */
    function checkInput()
    {
        global $lng;

        $ok = parent::checkInput();
        if ($ok AND $this->getRequired() AND $_POST[$this->getPostVar()] == self::IGNORE_KEY) {
            $this->setAlert($lng->txt('msg_input_is_required'));

            return false;
        }

        return $ok;
    }
}