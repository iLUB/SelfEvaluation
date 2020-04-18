<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once './Services/Export/classes/class.ilXmlImporter.php';
include_once './Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/class.ilObjSelfEvaluation.php';

/***
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 */
class ilSelfEvaluationImporter extends ilXmlImporter
{
    /**
     * Import xml representation
     * @param string          $entity
     * @param string          $id
     * @param string          $xml
     * @param ilImportMapping $mapping
     * @return    string    $ref_id
     */
    public function importXmlRepresentation($entity, $id, $xml, $mapping)
    {
        global $DIC;

        $ref_id = false;
        foreach ($mapping->getMappingsOfEntity('Services/Container', 'objs') as $old => $new) {
            if (ilObject::_lookupType($new) == "xsev" && $id == $old) {
                $ref_id = end(ilObject::_getAllReferences($new));
            }
        }

        $obj_self_eval = new ilObjSelfEvaluation($ref_id);
        $obj_self_eval->fromXML($entity, $id, $xml, $mapping);
        return $obj_self_eval->getId();
    }
}