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
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function importXmlRepresentation($entity, $id, $xml, $mapping)
	{
		$obj_self_eval = new ilObjSelfEvaluation();
		$obj_self_eval->fromXML($xml);
	}
}