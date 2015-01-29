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
require_once('./Customizing/global/plugins/Libraries/Export/class.csvExport.php');
/**
 * Class ilSelfEvaluationCsvExport
 *
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationCsvExport extends csvExport{
    /**
     * @var int
     */
    protected $object_id = 0;

    /**
     * @var ilSelfEvaluationMetaQuestion[]
     */
    protected $meta_questions = array();
    /**
     * @var ilSelfEvaluationQuestion[]
     */
    protected $questions = array();
    /**
     * @var ilSelfEvaluationDataset[]
     */
    protected $datasets= array();

    /**
     * @var string
     */
    protected $date_format = "Y-m-d H:i:s";


    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $pl = null;
    /**
     * @param ilSelfEvaluationPlugin $pl
     * @param int $object_id
     */
    function __construct(ilSelfEvaluationPlugin $pl, $object_id = 0){
        parent::__construct();
        $this->setObjectId($object_id);
        $this->pl = $pl;


    }

    public function getCsvExport(){
        $this->getData();
        $this->setColumns();
        $this->setRows();
        parent::getCsvExport(";");
    }

    protected function getData(){
        $meta_blocks = ilSelfEvaluationMetaBlock::getAllInstancesByParentId($this->getObjectId());
        foreach($meta_blocks as $meta_block){
            $this->addMetaQuestions(ilSelfEvaluationMetaQuestion::_getAllInstancesForParentId($meta_block->getId()));
        }
        $blocks = ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($this->getObjectId());
        foreach($blocks as $block){
            $this->addQuestions(ilSelfEvaluationQuestion::_getAllInstancesForParentId($block->getId()));
        }

        $this->setDatasets(ilSelfEvaluationDataset::_getAllInstancesByObjectId($this->getObjectId()));
    }

    protected function setColumns(){
        $position = 0;
        $this->getTable()->addColumn(new csvExportColumn("identity", $this->pl->txt("identity"),-5));
        $this->getTable()->addColumn(new csvExportColumn("starting_date", $this->pl->txt("starting_date"),-4));
        $this->getTable()->addColumn(new csvExportColumn("ending_date", $this->pl->txt("ending_date"),-3));
        $this->getTable()->addColumn(new csvExportColumn("duration", $this->pl->txt("duration"),-2));

        $this->getTable()->setSortColumn("ending_date");

        foreach($this->getMetaQuestions() as $meta_question){
            $this->getTable()->addColumn(new csvExportColumn($meta_question->getName(),$meta_question->getName(),$position));
            $position++;
        }

        foreach($this->getQuestions() as $question){
            $this->getTable()->addColumn(new csvExportColumn($this->getTitleForQuestion($question),$this->getTitleForQuestion($question),$position));
            $position++;
        }
    }

    protected function setRows(){
        foreach($this->getDatasets() as $dataset)
        {
            $row = new csvExportRow();
            $row->addValue(new csvExportValue("identity", substr(md5($dataset->getIdentifierId()), 0, 8)));
            try{
                $invalid = false;
                if($dataset->getCreationDate()){
                    $row->addValue(new csvExportValue("starting_date", date($this->getDateFormat(),$dataset->getCreationDate())));
                }else{
                    $row->addValue(new csvExportValue("starting_date", "Invalid"));
                    $invalid = true;
                }
                if($dataset->getSubmitDate()){
                    $row->addValue(new csvExportValue("ending_date", date($this->getDateFormat(),$dataset->getSubmitDate())));
                }else{
                    $row->addValue(new csvExportValue("ending_date", "Invalid"));
                    $invalid = true;
                }
                if($invalid){
                    $row->addValue(new csvExportValue("duration", "Invalid"));
                }else{
                    $row->addValue(new csvExportValue("duration", $dataset->getDuration()));
                }
            }catch(Exception $e){
                $row->addValue(new csvExportValue("Error", "Invalid Date"));
            }


            $entries = ilSelfEvaluationData::_getAllInstancesByDatasetId($dataset->getId());
            foreach($entries as $entry){
                if($this->getMetaQuestion($entry->getQuestionId()) || $this->getQuestion($entry->getQuestionId()))
                {
                    if($entry->getQuestionType() == ilSelfEvaluationData::META_QUESTION_TYPE){
                        $column_name = $this->getMetaQuestion($entry->getQuestionId())->getName();
                    }
                    else{
                        $column_name = $this->getTitleForQuestion($this->getQuestion($entry->getQuestionId()));
                    }
                    $row->addValue(new csvExportValue($column_name,$entry->getValue()));
                }
            }
            $this->getTable()->addRow($row);

        }
    }

    /**
     * @param ilSelfEvaluationQuestion $question
     * @return string
     */
    protected function getTitleForQuestion(ilSelfEvaluationQuestion $question){
        $block = new ilSelfEvaluationQuestionBlock($question->getParentId());
        $title = $question->getTitle() ? $question->getTitle() : $this->pl->txt('question') . ' ' . $block->getPosition() . '.' . $question->getPosition();
        return $title;
    }

    /**
     * @param int $object_id
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * @param \ilSelfEvaluationDataset[] $datasets
     */
    public function setDatasets($datasets)
    {
        $this->datasets = $datasets;
    }

    /**
     * @return \ilSelfEvaluationDataset[]
     */
    public function getDatasets()
    {
        return $this->datasets;
    }


    /**
     * @param \ilSelfEvaluationMetaQuestion[] $meta_questions
     */
    public function setMetaQuestions($meta_questions)
    {
        $this->meta_questions = $meta_questions;
    }

    /**
     * @param \ilSelfEvaluationMetaQuestion[] $meta_questions
     */
    public function addMetaQuestions($meta_questions)
    {
        $this->meta_questions = $this->meta_questions + $meta_questions;
    }

    /**
     * @return \ilSelfEvaluationMetaQuestion[]
     */
    public function getMetaQuestions()
    {
        return $this->meta_questions;
    }
    /**
     * @param $id
     * @return ilSelfEvaluationMetaQuestion
     */
    public function getMetaQuestion($id)
    {
        return $this->meta_questions[$id];
    }
    /**
     * @param \ilSelfEvaluationQuestion[] $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    /**
     * @param \ilSelfEvaluationQuestion[] $questions
     */
    public function addQuestions($questions)
    {
        $this->questions = $this->questions + $questions;
    }

    /**
     * @return \ilSelfEvaluationQuestion[]
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param $id
     * @return ilSelfEvaluationQuestion
     */
    public function getQuestion($id)
    {
        return $this->questions[$id];
    }

    /**
     * @param string $date_format
     */
    public function setDateFormat($date_format)
    {
        $this->date_format = $date_format;
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        return $this->date_format;
    }


}