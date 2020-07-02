<?php
require_once __DIR__ . '/../vendor/autoload.php';

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
                if (!$object->isOnline()
                    AND !$this->access->checkAccessOfUser($a_user_id, 'write', '', $a_ref_id)
                ) {
                    return false;
                }
                break;
        }

        return true;
    }
}
