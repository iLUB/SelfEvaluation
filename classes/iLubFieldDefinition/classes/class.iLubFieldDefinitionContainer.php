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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinition.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinitionFactory.php');

/**
 * Class iLubFieldDefinitionContainer
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinitionContainer {

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var iLubFieldDefinition[]
	 */
	protected $field_definitions = array();
	/**
	 * @var iLubFieldDefinitionFactory
	 */
	protected $factory;
	/**
	 * @var ilDB
	 */
	protected $db;


	/**
	 * Constructor
	 *
	 * @param iLubFieldDefinitionFactory $factory
	 * @param int                        $id
	 */
	public function __construct(iLubFieldDefinitionFactory $factory, $id = 0) {
		global $ilDB;

		$this->db = $ilDB;
		$this->factory = $factory;
		$this->setId($id);

		if ($this->getId() > 0) {
			$this->read();
		}
	}


	/**
	 * Creates the table setup for the field definitions. Should be called in dbupdate.sql
	 */
	public function initDB() {
		$field = $this->factory->createILubFieldDefinition();
		$field->initDB();
	}


	/**
	 * Read DB entries
	 */
	protected function read() {
		$field = $this->factory->createILubFieldDefinition();
		$stmt = $this->db->prepare('SELECT * FROM ' . $field->getTableName() . ' WHERE container_id = ? ORDER BY position ASC;',
			array('integer'));
		$this->db->execute($stmt, array($this->getId()));

		while ($row = $this->db->fetchObject($stmt)) {
			$field = $this->factory->createILubFieldDefinition();
			$field->setId($row->field_id);
			$field->setContainerId($this->getId());
			$field->setName($row->field_name);
			$field->setShortTitle($row->short_title);
			$field->setTypeId($row->field_type);
			$field->setValues(unserialize($row->field_values));
			$field->enableRequired($row->field_required);
			$field->setPosition($row->position);

			$this->field_definitions[] = $field;
		}
	}


	/**
	 * @param \iLubFieldDefinition[] $field_definitions
	 */
	public function setFieldDefinitions($field_definitions) {
		$this->field_definitions = $field_definitions;
	}


	/**
	 * @return \iLubFieldDefinition[]
	 */
	public function getFieldDefinitions() {
		return $this->field_definitions;
	}


	/**
	 * @param iLubFieldDefinition $field_definition
	 */
	public function addFieldDefinition(iLubFieldDefinition $field_definition) {
		$this->field_definitions[] = $field_definition;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @return \iLubFieldDefinitionFactory
	 */
	public function getFactory() {
		return $this->factory;
	}
}