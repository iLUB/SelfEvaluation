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
require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/Block/class.ilSelfEvaluationMetaBlock.php');
require_once(dirname(__FILE__) . '/Question/class.ilSelfEvaluationQuestion.php');
require_once(dirname(__FILE__) . '/Scale/class.ilSelfEvaluationScale.php');
require_once(dirname(__FILE__) . '/Scale/class.ilSelfEvaluationScaleUnit.php');
require_once(dirname(__FILE__) . '/Dataset/class.ilSelfEvaluationDataset.php');
require_once(dirname(__FILE__) . '/Dataset/class.ilSelfEvaluationData.php');
require_once(dirname(__FILE__) . '/Identity/class.ilSelfEvaluationIdentity.php');
require_once(dirname(__FILE__) . '/Feedback/class.ilSelfEvaluationFeedback.php');

/**
 * Application class for SelfEvaluation repository object.
 *
 * @author Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 *
 * $Id$
 */
class ilObjSelfEvaluation extends ilObjectPlugin {

	const TABLE_NAME = 'rep_robj_xsev_data';
	const TYPE_GROUP = 1;
	const SHUFFLE_OFF = 1;
	const SHUFFLE_IN_BLOCKS = 2;
	const SHUFFLE_ACROSS_BLOCKS = 3;

	const DISPLAY_TYPE_SINGLE_PAGE = 1; // Single Page
	const DISPLAY_TYPE_MULTIPLE_PAGES = 2; // Multiple Pages
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
	protected $sort_type = self::SHUFFLE_OFF;
	/**
	 * @var int
	 */
	protected $display_type = self::DISPLAY_TYPE_MULTIPLE_PAGES;
	/**
	 * @var string
	 */
	protected $intro = '';
	/**
	 * @var string
	 */
	protected $outro = '';
	/**
	 * @var string
	 */
	protected $identity_selection_info_text = '';
	/**
	 * @var bool
	 */
	protected $show_charts = true;
	/**
	 * @var bool
	 */
	protected $allow_multiple_datasets = true;
	/**
	 * @var bool
	 */
	protected $allow_dataset_editing = false;
	/**
	 * @var bool
	 */
	protected $allow_show_results = true;
	/**
	 * @var bool
	 */
	protected $show_feedbacks = true;
	/**
	 * @var bool
	 */
	protected $show_feedbacks_charts = true;
	/**
	 * @var bool
	 */
	protected $show_feedbacks_overview = true;
	/**
	 * @var bool
	 */
	protected $show_block_titles_during_evaluation = true;
	/**
	 * @var bool
	 */
	protected $show_block_descriptions_during_evaluation = true;
	/**
	 * @var bool
	 */
	protected $show_block_titles_during_feedback = true;
	/**
	 * @var bool
	 */
	protected $show_block_descriptions_during_feedback = true;

	/**
	 * @var bool
	 */
	protected $show_fbs_overview_bar = true;
	/**
	 * @var bool
	 */
	protected $show_fbs_overview_spider = true;
	/**
	 * @var bool
	 */
	protected $show_fbs_overview_left_right = true;
	/**
	 * @var bool
	 */
	protected $show_fbs_chart_bar = true;
	/**
	 * @var bool
	 */
	protected $show_fbs_chart_spider = true;
	/**
	 * @var bool
	 */
	protected $show_fbs_chart_left_right = true;

    /**
     * @var int
     */
    protected $sort_random_nr_item_block = 10;

    /**
	 * @param int $a_ref_id
	 */
	function __construct($a_ref_id = 0) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$this->db = $ilDB;
		parent::__construct($a_ref_id);
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
			'identity_selection_info' => array(
				'text',
				$this->getIdentitySelectionInfoText()
			),
			'show_fbs' => array(
				'integer',
				$this->getShowFeedbacks()
			),
			'show_fbs_charts' => array(
				'integer',
				$this->getShowFeedbacksCharts()
			),
			'show_fbs_overview' => array(
				'integer',
				$this->getShowFeedbacksOverview()
			),
			'show_block_titles_sev' => array(
				'integer',
				$this->getShowBlockTitlesDuringEvaluation()
			),
			'show_block_desc_sev' => array(
				'integer',
				$this->getShowBlockDescriptionsDuringEvaluation()
			),
			'show_block_titles_fb' => array(
				'integer',
				$this->getShowBlockTitlesDuringFeedback()
			),
			'show_block_desc_fb' => array(
				'integer',
				$this->getShowBlockDescriptionsDuringFeedback()
			),
            'sort_random_nr_items_block' => array(
                'integer',
                $this->getSortRandomNrItemBlock()
            ),
				'show_fbs_overview_bar' => array(
						'integer',
						$this->isShowFbsOverviewBar()
				),
				'show_fbs_overview_spider' => array(
						'integer',
						$this->isShowFbsOverviewSpider()
				),
				'show_fbs_overview_left_right' => array(
						'integer',
						$this->isShowFbsOverviewLeftRight()
				),
				'show_fbs_chart_bar' => array(
						'integer',
						$this->isShowFbsChartBar()
				),
				'show_fbs_chart_spider' => array(
						'integer',
						$this->isShowFbsChartSpider()
				),
				'show_fbs_chart_left_right' => array(
						'integer',
						$this->isShowFbsChartLeftRight()
				),
		);
	}


	function doCreate() {
		/** @var ilSelfEvaluationPlugin $plugin */
		$plugin = $this->plugin;
		$config = new ilSelfEvaluationConfig($plugin->getConfigTableName());
		$this->setIdentitySelectionInfoText($config->getValue('identity_selection'));
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
			$this->setIdentitySelectionInfoText($rec->identity_selection_info);
			$this->setShowFeedbacks($rec->show_fbs);
			$this->setShowFeedbacksCharts($rec->show_fbs_charts);
			$this->setShowFeedbacksOverview($rec->show_fbs_overview);
			$this->setShowBlockTitlesDuringEvaluation($rec->show_block_titles_sev);
			$this->setShowBlockDescriptionsDuringEvaluation($rec->show_block_desc_sev);
			$this->setShowBlockTitlesDuringFeedback($rec->show_block_titles_fb);
			$this->setShowBlockDescriptionsDuringFeedback($rec->show_block_desc_fb);
            $this->setSortRandomNrItemBlock($rec->sort_random_nr_items_block);
			$this->setShowFbsOverviewBar($rec->show_fbs_overview_bar);
			$this->setShowFbsOverviewSpider($rec->show_fbs_overview_spider);
			$this->setShowFbsOverviewLeftRight($rec->show_fbs_overview_left_right);
			$this->setShowFbsChartBar($rec->show_fbs_chart_bar);
			$this->setShowFbsChartSpider($rec->show_fbs_chart_spider);
			$this->setShowFbsChartLeftRight($rec->show_fbs_chart_left_right);

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
		foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($this->getId()) as $block) {
			foreach (ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId()) as $qst) {
				$qst->delete();
			}
			foreach (ilSelfEvaluationFeedback::_getAllInstancesForParentId($block->getId()) as $fb) {
				$fb->delete();
			}
			$block->delete();
		}
		foreach (ilSelfEvaluationMetaBlock::getAllInstancesByParentId($this->getId()) as $block) {
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
		$new_obj->setIdentitySelectionInfoText($this->getIdentitySelectionInfoText());
		$new_obj->setShowBlockTitlesDuringEvaluation($this->getShowBlockTitlesDuringEvaluation());
		$new_obj->setShowBlockDescriptionsDuringEvaluation($this->getShowBlockDescriptionsDuringEvaluation());
		$new_obj->setShowBlockTitlesDuringFeedback($this->getShowBlockTitlesDuringFeedback());
		$new_obj->setShowBlockDescriptionsDuringFeedback($this->getShowBlockDescriptionsDuringFeedback());
        $new_obj->setSortRandomNrItemBlock($this->getSortRandomNrItemBlock());
		$new_obj->setShowFbsOverviewBar($this->isShowFbsOverviewBar());
		$new_obj->setShowFbsOverviewSpider($this->isShowFbsOverviewSpider());
		$new_obj->setShowFbsOverviewLeftRight($this->isShowFbsOverviewLeftRight());
		$new_obj->setShowFbsChartBar($this->isShowFbsChartBar());
		$new_obj->setShowFbsChartSpider($this->isShowFbsChartSpider());
		$new_obj->setShowFbsChartLeftRight($this->isShowFbsChartLeftRight());
		// TODO clone meta blocks and questions?
		$new_obj->update();
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		return ((boolean)$this->getOnline() AND $this->hasBlocks() AND $this->areFeedbacksComplete() AND $this->hasScale()) ? true : false;
	}
    /**
     * @return bool
     */
    public function hasScale() {
        return count(ilSelfEvaluationScale::_getInstanceByRefId($this->getId())->getUnitsAsArray())==0 ? false: true;
    }

	/**
	 * @return bool
	 */
	public function hasDatasets() {
		foreach (ilSelfEvaluationIdentity::_getAllInstancesByForForObjId($this->getId()) as $id) {
			if (count(ilSelfEvaluationDataset::_getAllInstancesByIdentifierId($id->getId())) > 0) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Return if there are any blocks with at least one question
	 *
	 * @return bool
	 */
	public function hasBLocks() {
		foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($this->getId()) as $block) {
			if (count(ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId())) > 0) {
				return true;
			}
		}
		foreach (ilSelfEvaluationMetaBlock::getAllInstancesByParentId($this->getId()) as $block) {
			/** @var ilSelfEvaluationMetaBlock $block */
			if (count($block->getMetaContainer()->getFieldDefinitions()) > 0) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @return bool
	 */
	public function areFeedbacksComplete() {
		$return = true;
		foreach (ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($this->getId()) as $block) {
			$return = ilSelfEvaluationFeedback::_isComplete($block->getId()) ? $return : false;
		}

		return $return;
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
	 * @param string $info
	 */
	public function setIdentitySelectionInfoText($info) {
		$this->identity_selection_info_text = $info;
	}


	/**
	 * @return string
	 */
	public function getIdentitySelectionInfoText() {
		return $this->identity_selection_info_text;
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


	/**
	 * @param boolean $show_charts
	 */
	public function setShowCharts($show_charts) {
		$this->show_charts = $show_charts;
	}


	/**
	 * @return boolean
	 */
	public function getShowCharts() {
		return $this->show_charts;
	}


	/**
	 * @param boolean $allow_multiple_datasets
	 */
	public function setAllowMultipleDatasets($allow_multiple_datasets) {
		$this->allow_multiple_datasets = $allow_multiple_datasets;
	}


	/**
	 * @return boolean
	 */
	public function getAllowMultipleDatasets() {
		return $this->allow_multiple_datasets;
	}


	/**
	 * @param boolean $allow_dataset_editing
	 */
	public function setAllowDatasetEditing($allow_dataset_editing) {
		$this->allow_dataset_editing = $allow_dataset_editing;
	}


	/**
	 * @return boolean
	 */
	public function getAllowDatasetEditing() {
		return $this->allow_dataset_editing;
	}


	/**
	 * @param boolean $allow_show_results
	 */
	public function setAllowShowResults($allow_show_results) {
		$this->allow_show_results = $allow_show_results;
	}


	/**
	 * @return boolean
	 */
	public function getAllowShowResults() {
		return $this->allow_show_results;
	}


	/**
	 * @param boolean $show_feedbacks
	 */
	public function setShowFeedbacks($show_feedbacks) {
		$this->show_feedbacks = $show_feedbacks;
	}


	/**
	 * @return boolean
	 */
	public function getShowFeedbacks() {
		return $this->show_feedbacks;
	}


	/**
	 * @param boolean $show_feedbacks_charts
	 */
	public function setShowFeedbacksCharts($show_feedbacks_charts) {
		$this->show_feedbacks_charts = $show_feedbacks_charts;
	}


	/**
	 * @return boolean
	 */
	public function getShowFeedbacksCharts() {
		return $this->show_feedbacks_charts;
	}


	/**
	 * @param boolean $show_feedbacks_overview
	 */
	public function setShowFeedbacksOverview($show_feedbacks_overview) {
		$this->show_feedbacks_overview = $show_feedbacks_overview;
	}


	/**
	 * @return boolean
	 */
	public function getShowFeedbacksOverview() {
		return $this->show_feedbacks_overview;
	}


	/**
	 * @param boolean $show_block_titles_during_evaluation
	 */
	public function setShowBlockTitlesDuringEvaluation($show_block_titles_during_evaluation) {
		$this->show_block_titles_during_evaluation = $show_block_titles_during_evaluation;
	}


	/**
	 * @return boolean
	 */
	public function getShowBlockTitlesDuringEvaluation() {
		return $this->show_block_titles_during_evaluation;
	}


	/**
	 * @param boolean $show_block_descriptions_during_evaluation
	 */
	public function setShowBlockDescriptionsDuringEvaluation($show_block_descriptions_during_evaluation) {
		$this->show_block_descriptions_during_evaluation = $show_block_descriptions_during_evaluation;
	}


	/**
	 * @return boolean
	 */
	public function getShowBlockDescriptionsDuringEvaluation() {
		return $this->show_block_descriptions_during_evaluation;
	}


	/**
	 * @param boolean $show_block_titles_during_feedback
	 */
	public function setShowBlockTitlesDuringFeedback($show_block_titles_during_feedback) {
		$this->show_block_titles_during_feedback = $show_block_titles_during_feedback;
	}


	/**
	 * @return boolean
	 */
	public function getShowBlockTitlesDuringFeedback() {
		return $this->show_block_titles_during_feedback;
	}


	/**
	 * @param boolean $show_block_descriptions_during_feedback
	 */
	public function setShowBlockDescriptionsDuringFeedback($show_block_descriptions_during_feedback) {
		$this->show_block_descriptions_during_feedback = $show_block_descriptions_during_feedback;
	}


	/**
	 * @return boolean
	 */
	public function getShowBlockDescriptionsDuringFeedback() {
		return $this->show_block_descriptions_during_feedback;
	}

    /**
     * @param int $sort_random_nr_item_block
     */
    public function setSortRandomNrItemBlock($sort_random_nr_item_block)
    {
        $this->sort_random_nr_item_block = $sort_random_nr_item_block;
    }

    /**
     * @return int
     */
    public function getSortRandomNrItemBlock()
    {
        return $this->sort_random_nr_item_block;
    }

	/**
	 * @return boolean
	 */
	public function isShowFbsOverviewBar()
	{
		return $this->show_fbs_overview_bar;
	}

	/**
	 * @param boolean $show_fbs_overview_bar
	 */
	public function setShowFbsOverviewBar($show_fbs_overview_bar)
	{
		$this->show_fbs_overview_bar = $show_fbs_overview_bar;
	}

	/**
	 * @return boolean
	 */
	public function isShowFbsOverviewSpider()
	{
		return $this->show_fbs_overview_spider;
	}

	/**
	 * @param boolean $show_fbs_overview_spider
	 */
	public function setShowFbsOverviewSpider($show_fbs_overview_spider)
	{
		$this->show_fbs_overview_spider = $show_fbs_overview_spider;
	}

	/**
	 * @return boolean
	 */
	public function isShowFbsOverviewLeftRight()
	{
		return $this->show_fbs_overview_left_right;
	}

	/**
	 * @param boolean $show_fbs_overview_left_right
	 */
	public function setShowFbsOverviewLeftRight($show_fbs_overview_left_right)
	{
		$this->show_fbs_overview_left_right = $show_fbs_overview_left_right;
	}

	/**
	 * @return boolean
	 */
	public function isShowFbsChartBar()
	{
		return $this->show_fbs_chart_bar;
	}

	/**
	 * @param boolean $show_fbs_chart_bar
	 */
	public function setShowFbsChartBar($show_fbs_chart_bar)
	{
		$this->show_fbs_chart_bar = $show_fbs_chart_bar;
	}

	/**
	 * @return boolean
	 */
	public function isShowFbsChartSpider()
	{
		return $this->show_fbs_chart_spider;
	}

	/**
	 * @param boolean $show_fbs_chart_spider
	 */
	public function setShowFbsChartSpider($show_fbs_chart_spider)
	{
		$this->show_fbs_chart_spider = $show_fbs_chart_spider;
	}

	/**
	 * @return boolean
	 */
	public function isShowFbsChartLeftRight()
	{
		return $this->show_fbs_chart_left_right;
	}

	/**
	 * @param boolean $show_fbs_chart_left_right
	 */
	public function setShowFbsChartLeftRight($show_fbs_chart_left_right)
	{
		$this->show_fbs_chart_left_right = $show_fbs_chart_left_right;
	}
}

?>
