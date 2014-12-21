<?php
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');

/**
 * Class ilSelfEvaluationQuestionBlock
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationQuestionBlock extends ilSelfEvaluationBlock {

	/**
	 * @var ilSelfEvaluationScale
	 */
	protected $scale;
	/**
	 * @var string
	 */
	protected $abbreviation = '';


	public function read() {
		parent::read();
		$this->setScale(ilSelfEvaluationScale::_getInstanceByRefId($this->getParentId()));
	}


	/**
	 * @return array
	 */
	protected function getNonDbFields() {
		return array_merge(parent::getNonDbFields(), array('scale'));
	}


	/**
	 * @param \ilSelfEvaluationScale $scale
	 */
	public function setScale($scale) {
		$this->scale = $scale;
	}


	/**
	 * @return \ilSelfEvaluationScale
	 */
	public function getScale() {
		return $this->scale;
	}


	/**
	 * @param string $abbreviation
	 */
	public function setAbbreviation($abbreviation) {
		$this->abbreviation = $abbreviation;
	}


	/**
	 * @return string
	 */
	public function getAbbreviation() {
		return $this->abbreviation;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return 'rep_robj_xsev_block';
	}


	//
	// Static
	//
	/**
	 * @param int  $parent_id ilObjSelfEvaluation obj id
	 * @param bool $as_array
	 *
	 * @return ilSelfEvaluationQuestionBlock[]
	 */
	public static function _getAllInstancesByParentId($parent_id, $as_array = false) {
		global $ilDB;
		$return = array();
		$set = $ilDB->query('SELECT * FROM ' . self::getTableName() . ' ' . ' WHERE parent_id = '
			. $ilDB->quote($parent_id, 'integer') . ' ORDER BY position ASC');
		while ($rec = $ilDB->fetchObject($set)) {
			$scale = ilSelfEvaluationScale::_getInstanceByRefId($parent_id);

			if ($as_array) {
				$return[] = array(
					'id' => (int)$rec->id,
					'parent_id' => (int)$rec->parent_id,
					'title' => (string)$rec->title,
					'description' => (string)$rec->description,
					'abbreviation' => (string)$rec->abbreviation,
					'scale_id' => (int)$scale->getId(),
				);
			} else {
				$block = new ilSelfEvaluationQuestionBlock();
				$block->setId((int)$rec->id);
				$block->setParentId((int)$rec->parent_id);
				$block->setPosition((int)$rec->position);
				$block->setTitle((string)$rec->title);
				$block->setDescription((string)$rec->description);
				$block->setAbbreviation((string)$rec->abbrevation);

				$block->setScale($scale);
				$return[] = $block;
			}
		}

		return $return;
	}


	/**
	 * @return ilSelfEvaluationBlockTableRow
	 */
	public function getBlockTableRow() {
		require_once(dirname(__FILE__) . '/Table/class.ilSelfEvaluationQuestionBlockTableRow.php');
		$row = new ilSelfEvaluationQuestionBlockTableRow($this);

		return $row;
	}
}

?>