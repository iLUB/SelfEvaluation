<?php
require_once __DIR__ . '/../vendor/autoload.php';

class ilSelfEvaluationExporter extends ilXmlExporter
{

    public function init()
    {

    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        if ($type = ilObject::_lookupType($a_id) != "xsev") {
            throw new Exception("Wrong type " . $type . " for selfevaluation export.");
        }

        $ref_id = end(ilObject::_getAllReferences($a_id));

        $obj_self_eval = new ilObjSelfEvaluation($ref_id);
        $dom = dom_import_simplexml($obj_self_eval->toXML());
        $xml_string = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
        return $xml_string;
    }

    public function getValidSchemaVersions($a_entity)
    {
        return [
            "5.3.0" => [
                "uses_dataset" => false,
                "min" => "5.3.0",
                "max" => ""
            ]
        ];
    }
}

