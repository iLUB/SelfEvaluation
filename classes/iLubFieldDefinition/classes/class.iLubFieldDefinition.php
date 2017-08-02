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

/**
 * Class iLubFieldDefinition
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinition {
	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var int
	 */
	protected $container_id;
	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $short_title;
	/**
	 * @var int
	 */
	protected $type_id;
	/**
	 * @var array
	 */
	protected $values;
	/**
	 * @var bool
	 */
	protected $required;
	/**
	 * @var int
	 */
	protected $position;
	/**
	 * @var string
	 */
	protected $table_name = '';


	/**
	 * Constructor
	 *
	 * @param string $table_name
	 * @param int    $container_id
	 * @param int    $id
	 */
	public function __construct($table_name, $container_id, $id = 0) {
		$this->setId($id);
		$this->setContainerId($container_id);
		$this->setTableName($table_name);

		if ($this->getId() > 0) {
			$this->read();
		}
	}


	public function initDB() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		if ($this->getTableName() != '' AND !$DIC->database()->tableExists($this->getTableName())) {
			$DIC->database()->createTable($this->getTableName(), $this->getDbFields());
			$DIC->database()->createSequence($this->getTableName());
			$DIC->database()->addPrimaryKey($this->getTableName(), array( 'field_id' ));
		}
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
	 * @param int $container_id
	 */
	public function setContainerId($container_id) {
		$this->container_id = $container_id;
	}


	/**
	 * @return int
	 */
	public function getContainerId() {
		return $this->container_id;
	}


	/**
	 * @return int
	 */
	public function getTypeId() {
		return $this->type_id;
	}


	/**
	 * @param int $type must be a field definition type
	 */
	public function setTypeId($type) {
		$this->type_id = $type;
	}


	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @param string $name the field name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getShortTitle()
	{
		return $this->short_title;
	}

	/**
	 * @param string $short_title
	 */
	public function setShortTitle($short_title)
	{
		$this->short_title = $short_title;
	}


	/**
	 * @return array
	 */
	public function getValues() {
		return $this->values ? $this->values : array();
	}


	/**
	 * @param array $values
	 */
	public function setValues($values) {
		$this->values = $values;
	}


	/**
	 * @return bool
	 */
	public function isRequired() {
		return (bool)$this->required;
	}


	/**
	 * @param bool $status
	 */
	public function enableRequired($status) {
		$this->required = (bool)$status;
	}


	/**
	 * @param int $position
	 */
	public function setPosition($position) {
		$this->position = (int)$position;
	}


	/**
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @return int
	 */
	protected function getNextPosition() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		$stmt = $DIC->database()->prepare('SELECT MAX(position) next_pos FROM ' . $this->getTableName() .
			' WHERE container_id = ?;', array('integer'));
		$DIC->database()->execute($stmt, array($this->getContainerId()));
		while ($rec = $DIC->database()->fetchObject($stmt)) {
			return $rec->next_pos + 1;
		}

		return 1;
	}


	/**
	 * Save
	 */
	public function save() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		$stmt = $DIC->database()->prepare('INSERT INTO ' . $this->getTableName() .
			' (field_id, container_id, field_name, short_title,field_type, field_values, field_required, position) ' .
			' VALUES(?, ?, ?, ?, ?, ?, ?, ?);',
			array('integer', 'integer', 'text', 'text','integer', 'text', 'integer', 'integer'));
		$this->setId($DIC->database()->nextId($this->getTableName()));
		$this->setPosition($this->getNextPosition());
		$DIC->database()->execute($stmt,
			array($this->getId(), $this->getContainerId(), $this->getName(),
					$this->getShortTitle(), $this->getTypeId(),
				serialize($this->getValues()), $this->isRequired(), $this->getPosition()));
	}


	/**
	 * Update a field
	 */
	public function update() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		$stmt = $DIC->database()->prepare('UPDATE ' . $this->getTableName() . ' SET ' .
			'container_id = ?, field_name = ?, short_title = ?,field_type = ?,
			field_values = ?, field_required = ?, position = ? ' .
			'WHERE field_id = ?',
			array('integer', 'text', 'integer', 'text', 'integer', 'integer', 'integer'));

		$DIC->database()->execute($stmt, array($this->getContainerId(), $this->getName(),
				$this->getShortTitle(), $this->getTypeId(),
			serialize($this->getValues()), $this->isRequired(), $this->getPosition(), $this->getId()));
	}


	/**
	 * Delete a field
	 */
	public function delete() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		$stmt = $DIC->database()->prepare('DELETE FROM ' . $this->getTableName() . ' WHERE field_id = ?;', array('integer'));
		$DIC->database()->execute($stmt, array($this->getId()));
	}


	/**
	 * Read DB entries
	 */
	protected function read() {
		global $DIC;
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		
		$stmt = $DIC->database()->prepare('SELECT * FROM ' . $this->getTableName() . ' WHERE field_id = ? ORDER BY position ASC;',
			array('integer'));
		$DIC->database()->execute($stmt, array($this->getId()));

		$row = $stmt->fetch(ilDBConstants::FETCHMODE_OBJECT);

		$this->setValuesFromRecord($row);
	}


	/**
	 * @param string $table_name
	 */
	public function setTableName($table_name) {
		$this->table_name = $table_name;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}


	/**
	 * @return array
	 */
	protected function getDbFields() {
		$fields = array(
				'field_id' => array(
						'type' => 'integer',
						'length' => 4,
						'notnull' => true
				),
				'container_id' => array(
						'type' => 'integer',
						'length' => 4,
						'notnull' => true
				),
				'field_name' => array(
						'type' => 'text',
						'length' => 255,
						'notnull' => false
				),
				'short_title' => array(
						'type' => 'text',
						'length' => 255,
						'notnull' => false
				),
				'field_type' => array(
						'type' => 'integer',
						'length' => 2,
						'notnull' => true
				),
				'field_values' => array(
						'type' => 'clob',
						'notnull' => false
				),
				'field_required' => array(
						'type' => 'integer',
						'length' => 1,
						'notnull' => true
				),
				'position' => array(
						'type' => 'integer',
						'length' => 4,
						'notnull' => true
				)
		);

		return $fields;
	}


	/**
	 * @param stdClass $rec
	 */
	protected function setValuesFromRecord($rec) {
		$this->setContainerId($rec->container_id);
		$this->setName($rec->field_name);
		$this->setShortTitle($rec->short_title);
		$this->setTypeId($rec->field_type);
		$this->setValues(unserialize($rec->field_values));
		$this->enableRequired($rec->field_required);
		$this->setPosition($rec->position);
	}
}