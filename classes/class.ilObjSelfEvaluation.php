<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once('./Services/Repository/classes/class.ilObjectPlugin.php');
require_once(dirname(__FILE__).'/Block/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__).'/Question/class.ilSelfEvaluationQuestion.php');
require_once(dirname(__FILE__).'/Scale/class.ilSelfEvaluationScale.php');
require_once(dirname(__FILE__).'/Scale/class.ilSelfEvaluationScaleUnit.php');
require_once(dirname(__FILE__).'/Dataset/class.ilSelfEvaluationDataset.php');
require_once(dirname(__FILE__).'/Dataset/class.ilSelfEvaluationData.php');
require_once(dirname(__FILE__).'/Identity/class.ilSelfEvaluationIdentity.php');

/**
 * Application class for SelfEvaluation repository object.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 *
 * $Id$
 */
class ilObjSelfEvaluation extends ilObjectPlugin {

	const TABLE_NAME = 'rep_robj_xsev_data';
	const TYPE_GROUP = 1;
	const SORT_MANUALLY = 1;
	const SORT_SHUFFLE = 2;
	const DISPLAY_TYPE_SINGLE = 1;
	const DISPLAY_TYPE_MULTIPLE = 2;
	/**
	 * @var bool
	 */
	protected $online = false;
	/**
	 * @var int
	 */
	protected $evaluation_type = self::TYPE_GROUP;
	/**
	 * @var int
	 */
	protected $sort_type = self::SORT_MANUALLY;
	/**
	 * @var int
	 */
	protected $display_type = self::DISPLAY_TYPE_SINGLE;
	/**
	 * @var string
	 */
	protected $intro = '';
	/**
	 * @var string
	 */
	protected $outro = '';


	/**
	 * @param int $a_ref_id
	 */
	function __construct($a_ref_id = 0) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		parent::__construct($a_ref_id);
		$this->db = $ilDB;
	}


	final function initType() {
		$this->setType('xsev');
	}


	/**
	 * @return array
	 */
	public function getArrayForDb() {
		return array(
			'id' => array(
				'integer',
				$this->getId()
			),
			'is_online' => array(
				'integer',
				$this->getOnline()
			),
			'evaluation_type' => array(
				'integer',
				$this->getEvaluationType()
			),
			'sort_type' => array(
				'integer',
				$this->getSortType()
			),
			'display_type' => array(
				'integer',
				$this->getDisplayType()
			),
			'intro' => array(
				'text',
				$this->getIntro()
			),
			'outro' => array(
				'text',
				$this->getOutro()
			),
		);
	}


	function doCreate() {
		$this->db->insert(self::TABLE_NAME, $this->getArrayForDb());
	}


	function doRead() {
		$set = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
		. $this->db->quote($this->getId(), 'integer'));
		while ($rec = $this->db->fetchObject($set)) {
			$this->setOnline($rec->is_online);
			$this->setEvaluationType($rec->evaluation_type);
			$this->setSortType($rec->sort_type);
			$this->setDisplayType($rec->display_type);
			$this->setIntro($rec->intro);
			$this->setOutro($rec->outro);
		}
	}


	function doUpdate() {
		$this->db->update(self::TABLE_NAME, $this->getArrayForDb(), array(
			'id' => array(
				'integer',
				$this->getId()
			),
		));
	}


	public function doDelete() {
		$scale = ilSelfEvaluationScale::_getInstanceByRefId($this->getId());
		foreach (ilSelfEvaluationScaleUnit::_getAllInstancesByParentId($scale->getId()) as $u) {
			$u->delete();
		}
		$scale->delete();
		foreach (ilSelfEvaluationIdentity::_getAllInstancesByForForObjId($this->getId()) as $id) {
			foreach (ilSelfEvaluationDataset::_getAllInstancesByIdentifierId($id->getId()) as $ds) {
				foreach (ilSelfEvaluationData::_getAllInstancesByDatasetId($ds->getId()) as $d) {
					$d->delete();
				}
				$ds->delete();
			}
			$id->delete();
		}
		foreach (ilSelfEvaluationBlock::_getAllInstancesByParentId($this->getId()) as $block) {
			foreach (ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId()) as $qst) {
				$qst->delete();
			}
			$block->delete();
		}
		$this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . ' id = '
		. $this->db->quote($this->getId(), 'integer'));

		return true;
	}


	/**
	 * @param                     $a_target_id
	 * @param                     $a_copy_id
	 * @param ilObjSelfEvaluation $new_obj
	 */
	function doClone($a_target_id, $a_copy_id, ilObjSelfEvaluation $new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->setEvaluationType($this->getEvaluationType());
		$new_obj->setSortType($this->getSortType());
		$new_obj->setDisplayType($this->getDisplayType());
		$new_obj->setIntro($this->getIntro());
		$new_obj->setOutro($this->getOutro());
		$new_obj->update();
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		return ($this->getOnline() AND $this->hasBlocks()) ? true : false;
	}


	/**
	 * @return bool
	 */
	public function hasDatasets() {
		foreach (ilSelfEvaluationIdentity::_getAllInstancesByForForObjId($this->getId()) as $id) {
			foreach (ilSelfEvaluationDataset::_getAllInstancesByIdentifierId($id->getId()) as $ds) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @return bool
	 */
	public function hasBLocks() {
		foreach (ilSelfEvaluationBlock::_getAllInstancesByParentId($this->getId()) as $block) {
			foreach (ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId()) as $qst) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @param string $intro
	 */
	public function setIntro($intro) {
		$this->intro = $intro;
	}


	/**
	 * @return string
	 */
	public function getIntro() {
		return $this->intro;
	}


	/**
	 * @param boolean $online
	 */
	public function setOnline($online) {
		$this->online = $online;
	}


	/**
	 * @return boolean
	 */
	public function getOnline() {
		return $this->online;
	}


	/**
	 * @param string $outro
	 */
	public function setOutro($outro) {
		$this->outro = $outro;
	}


	/**
	 * @return string
	 */
	public function getOutro() {
		return $this->outro;
	}


	/**
	 * @param int $sort_type
	 */
	public function setSortType($sort_type) {
		$this->sort_type = $sort_type;
	}


	/**
	 * @return int
	 */
	public function getSortType() {
		return $this->sort_type;
	}


	/**
	 * @param int $evaluation_type
	 */
	public function setEvaluationType($evaluation_type) {
		$this->evaluation_type = $evaluation_type;
	}


	/**
	 * @return int
	 */
	public function getEvaluationType() {
		return $this->evaluation_type;
	}


	/**
	 * @param int $display_type
	 */
	public function setDisplayType($display_type) {
		$this->display_type = $display_type;
	}


	/**
	 * @return int
	 */
	public function getDisplayType() {
		return $this->display_type;
	}
}

?>
