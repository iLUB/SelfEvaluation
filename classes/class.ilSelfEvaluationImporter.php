<?php
require_once __DIR__ . '/../vendor/autoload.php';

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
        $ref_id = false;
        foreach ($mapping->getMappingsOfEntity('Services/Container', 'objs') as $old => $new) {
            if (ilObject::_lookupType($new) == "xsev" && $id == $old) {
                $ref_id = end(ilObject::_getAllReferences($new));
            }
        }

        $obj_self_eval = new ilObjSelfEvaluation($ref_id);
        $obj_self_eval->fromXML($xml);
        return $obj_self_eval->getId();
    }
}