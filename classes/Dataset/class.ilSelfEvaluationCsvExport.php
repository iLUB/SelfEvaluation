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
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/Export/class.csvExport.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeMatrix.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeSelect.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeSingleChoice.php');

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

    public function getCsvExport($delimiter = ";",$enclosure = '"'){
        $this->getData();
        $this->setColumns();
        $this->setRows();
        parent::getCsvExport($delimiter);
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
        $this->getTable()->addColumn(new csvExportColumn("identity", $this->pl->txt("identity"),-100));
        $this->getTable()->addColumn(new csvExportColumn("starting_date", $this->pl->txt("starting_date"),-90));
        $this->getTable()->addColumn(new csvExportColumn("ending_date", $this->pl->txt("ending_date"),-80));
        $this->getTable()->addColumn(new csvExportColumn("duration", $this->pl->txt("duration"),-70));
        $this->getTable()->addColumn(new csvExportColumn("average", $this->pl->txt("overview_statistics_median"),-60));
        $this->getTable()->addColumn(new csvExportColumn("max", $this->pl->txt("overview_statistics_max"),-50));
        $this->getTable()->addColumn(new csvExportColumn("min", $this->pl->txt("overview_statistics_min"),-40));
        $this->getTable()->addColumn(new csvExportColumn("variance", $this->pl->txt("overview_statistics_varianz"),-30));
        $this->getTable()->addColumn(new csvExportColumn("sd", $this->pl->txt("overview_statistics_standardabweichung"),-20));
        $this->getTable()->addColumn(new csvExportColumn("sd_per_block", $this->pl->txt("overview_statistics_standardabweichung_per_plock"),-10));

        $this->getTable()->setSortColumn("starting_date");



        foreach($this->getMetaQuestions() as $meta_question){

            if($meta_question->getTypeId() == iLubFieldDefinitionTypeMatrix::TYPE_ID){
                $questions = iLubFieldDefinitionTypeMatrix::getQuestionsFromArray(
                    $meta_question->getValues());
                foreach($questions as $question){
                    $this->getTable()->addColumn(
                        new csvExportColumn(
                            $question,
                            $question,
                            $position
                        ));
                    $this->getTable()->addColumn(
                        new csvExportColumn(
                            $question." ID",
                            $question." ID",
                            $position
                        ));
                    $position++;
                }


            }else{
                if($meta_question->getShortTitle()){
                    $column_name = $meta_question->getShortTitle();

                }else{
                    $column_name = $meta_question->getName();
                }
                $this->getTable()->addColumn(
                    new csvExportColumn(
                        $column_name,
                        $column_name,
                        $position
                    ));

                if($meta_question->getTypeId() == iLubFieldDefinitionTypeSelect::TYPE_ID ||
                    $meta_question->getTypeId() == iLubFieldDefinitionTypeSingleChoice::TYPE_ID) {
                    $this->getTable()->addColumn(
                        new csvExportColumn(
                            $column_name . " ID",
                            $column_name . " ID",
                            $position
                        ));
                }
                $position++;
            }

        }

        foreach($this->getQuestions() as $question){
            $this->getTable()->addColumn(new csvExportColumn($this->getTitleForQuestion($question),$this->getTitleForQuestion($question),$position));
            $position++;
        }
    }

    /**
     * @param $dataset
     * @return csvExportValue
     */
    protected function getIdentity($dataset){
        $identifier = new ilSelfEvaluationIdentity($dataset->getIdentifierId());
        $id = $identifier->getIdentifier();
        if($identifier->getType() ==  ilSelfEvaluationIdentity::TYPE_LOGIN){
            $username = ilObjUser::_lookupName($identifier->getIdentifier());
            $id =  $username['login'];
        }

        return new csvExportValue("identity", $id);
    }

    protected function getDateValues($dataset){
        $meta_csv_values = [];

        try{
            $invalid = false;
            if($dataset->getCreationDate()){
                $meta_csv_values[] = new csvExportValue("starting_date", date($this->getDateFormat(),$dataset->getCreationDate()));
            }else{
                $invalid = true;
                $meta_csv_values[] = new csvExportValue("starting_date", "Invalid");
            }
            if($dataset->getSubmitDate()){
                $meta_csv_values[] = new csvExportValue("ending_date", date($this->getDateFormat(),$dataset->getSubmitDate()));
            }else{
                $invalid = true;
                $meta_csv_values[] = new csvExportValue("ending_date", "Invalid");
            }
            if($invalid){
                $meta_csv_values[] = new csvExportValue("duration", "Invalid");
            }else{
                $meta_csv_values[] = new csvExportValue("duration", $dataset->getDuration());
            }
        }catch(Exception $e){
            $meta_csv_values[] = new csvExportValue("Error", "Invalid Date");
        }
        return $meta_csv_values;
    }

    /**
     * @param ilSelfEvaluationDataset $dataset
     * @return array
     */
    protected function getStatisticValues($dataset){
        $meta_csv_values = [];
        $min = $dataset->getMinPercentageBlock();
        $max = $dataset->getMaxPercentageBlock();
        $sd_per_block = $dataset->getStandardabweichungPerBlock();

        $statistics_sd_per_block = "";
        foreach ($sd_per_block as $key => $sd){
            $statistics_sd_per_block .= $dataset->getBlockById($key)->getTitle().": ".$sd."; ";
        }

        $meta_csv_values[] = new csvExportValue("mean", $dataset->getOverallPercentage());
        $meta_csv_values[] = new csvExportValue("max", $max['block']->getTitle().": ".$max['percentage']."%");
        $meta_csv_values[] = new csvExportValue("min",$min['block']->getTitle().": ".$min['percentage']."%");
        $meta_csv_values[] = new csvExportValue("variance", $dataset->getOverallVarianz());
        $meta_csv_values[] = new csvExportValue("sd", $dataset->getOverallStandardabweichung());
        $meta_csv_values[] = new csvExportValue("sd_per_block", $statistics_sd_per_block);

        return $meta_csv_values;
    }

    protected function getQuestionValues($row, $entry){

        $column_name = $this->getTitleForQuestion($this->getQuestion($entry->getQuestionId()));

        $column_name = $this->generateUniqueName($row,$column_name);

        $value = $this->handledSkipped($entry->getValue());

        return new csvExportValue($column_name,$value);
    }


    protected function generateUniqueName($row,$column_name){
        while($row->getColumns()->columnIdExists($column_name)){
            $column_name = $column_name."_duplicate";
        }
        return $column_name;
    }

    protected function handledSkipped($value){
        if($value == "ilsel_dummy"){
            $value = "übersprungen";
        }
        return $value;
    }

    /**
     * @param $row
     * @param $entry
     * @param $meta_question
     * @return array
     */
    protected function getMetaQuestionValues($row,$entry, $meta_question){
        $meta_csv_values = [];

        if($meta_question->getTypeId() == iLubFieldDefinitionTypeMatrix::TYPE_ID){

            $question_values = $meta_question->getValues();
            $questions = iLubFieldDefinitionTypeMatrix::getQuestionsFromArray($question_values);
            foreach($questions as $key => $question){
                $entry_keys = $entry->getValue();
                if(is_array($entry_keys) && array_key_exists($key, $entry_keys)){
                    $entry_key = $entry_keys[$key];
                    $entry_content = $question_values[$entry_key];

                    $meta_csv_values[] = new csvExportValue($question, $entry_content);
                    $meta_csv_values[] = new csvExportValue($question." ID",
                        str_replace("scale_","",$entry_key));
                }
            }

        }else{

            if($meta_question->getShortTitle()){
                $column_name = $meta_question->getShortTitle();

            }else{
                $column_name = $meta_question->getName();
            }
            $column_name = $this->generateUniqueName($row,$column_name);
            $key = $this->handledSkipped($entry->getValue());

            if($meta_question->getTypeId() == iLubFieldDefinitionTypeSelect::TYPE_ID ||
                $meta_question->getTypeId() == iLubFieldDefinitionTypeSingleChoice::TYPE_ID) {
                $question_values = $meta_question->getValues();
                if(is_array($question_values) && array_key_exists($key, $question_values)){
                    $meta_csv_values[] = new csvExportValue($column_name." ID",$key);
                    $entry_value = $question_values[$key];
                    $meta_csv_values[] = new csvExportValue($column_name, $entry_value);
                }else{
                    //legacy issue, get ID from time with none JSON-Save of values.
                    foreach($question_values as $qkey => $qvalue){
                        if($qvalue == $key){
                            $key = $qkey;
                            $value = $qvalue;
                            break;
                        }
                    }
                    $meta_csv_values[] = new csvExportValue($column_name,$value);
                    $meta_csv_values[] = new csvExportValue($column_name." ID",$key);
                }
            }else{
                $meta_csv_values[] = new csvExportValue($column_name,$key);
            }
        }
        return $meta_csv_values;
    }

    protected function getMetaQuestionValueForSelections(){

    }

    protected function setRows(){
        foreach($this->getDatasets() as $dataset)
        {
            $row = new csvExportRow();
            $row->addValue($this->getIdentity($dataset));
            $values = $this->getDateValues($dataset);
            foreach($values as $value){
                $row->addValue($value);
            }
            $values = $this->getStatisticValues($dataset);
            foreach($values as $value){
                $row->addValue($value);
            }
            $entries = ilSelfEvaluationData::_getAllInstancesByDatasetId($dataset->getId());
            foreach($entries as $entry){
                if($this->getMetaQuestion($entry->getQuestionId()) || $this->getQuestion($entry->getQuestionId()))
                {
                    if($entry->getQuestionType() == ilSelfEvaluationData::META_QUESTION_TYPE){
                        $meta_question = $this->getMetaQuestion($entry->getQuestionId());
                        $values = $this->getMetaQuestionValues($row,$entry,
                            $meta_question);
                        foreach($values as $value){
                            $row->addValue($value);
                        }
                    }else{
                        $row->addValue($this->getQuestionValues($row,$entry));

                    }

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