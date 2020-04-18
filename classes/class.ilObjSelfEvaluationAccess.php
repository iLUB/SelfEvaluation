<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once('./Services/Repository/classes/class.ilObjectPluginAccess.php');
require_once('class.ilObjSelfEvaluation.php');

/**
 * Access/Condition checking for SelfEvaluation object
 * Please do not create instances of large application classes (like ilObjSelfEvaluation)
 * Write small methods within this class to determin the status.
 * @author        Alex Killing <alex.killing@gmx.de>
 * @author        fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @author        Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version       $Id$
 */
class ilObjSelfEvaluationAccess extends ilObjectPluginAccess
{

    /**
     * @param string $a_cmd
     * @param string $a_permission
     * @param int    $a_ref_id
     * @param int    $a_obj_id
     * @param string $a_user_id
     * @return bool
     */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = '')
    {
        if ($a_user_id == '') {
            $a_user_id = $this->user->getId();
        }

        switch ($a_permission) {
            case 'read':
            case 'visible':
                $object = new ilObjSelfEvaluation($a_ref_id);
                if (!$object->getOnline($a_obj_id)
                    AND !$this->access->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)
                ) {
                    return false;
                }
                break;
        }

        return true;
    }
}
