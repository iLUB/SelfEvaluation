<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
 * ilSelfEvaluationExporter
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilSelfEvaluationExporter extends ilXmlExporter
{

    public function init()
    {

    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {

    }

    public function getValidSchemaVersions($a_entity)
    {
        return array (
            "5.3.0" => array(
                "namespace" => "http://ilias.unibe.ch/Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation",
                "xsd_file" => "ilias_self_evaluation.xsd",
                "uses_dataset" => true,
                "min" => "5.3.0",
                "max" => "")
        );
    }

}
?>