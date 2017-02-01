<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
 * Fixed version of ilFormSectionHeaderGUI (info shown in output)
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 * @ingroup    ServicesForm
 */
class ilFormSectionHeaderGUIFixed extends ilFormSectionHeaderGUI
{
    /**
     * Insert property html
     *
     */
    function insert(ilTemplate &$a_tpl = null)
    {
        $a_tpl->setCurrentBlock("header");
        $a_tpl->setVariable("TXT_TITLE", $this->getTitle());
        $a_tpl->setVariable("TXT_DESCRIPTION",$this->getInfo());
        if (isset($this->section_anchor)){
            $a_tpl->setVariable('LABEL', $this->section_anchor);
        }

        $a_tpl->parseCurrentBlock();
    }
}
