<?php
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/../Scale/class.ilSelfEvaluationScale.php');
require_once('int.ilSelfEvaluationQuestionBlockInterface.php');

class ilSelfEvaluationQuestionBlock extends ilSelfEvaluationBlock implements ilSelfEvaluationQuestionBlockInterface
{
    /**
     * @var string
     */
    protected $abbreviation = '';

    /**
     * @param $parent_ref_id
     * @return ilSelfEvaluationQuestionBlock
     */
    public function cloneTo($parent_id)
    {
        $clone = new self();
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setAbbreviation($this->getAbbreviation());
        $clone->setDescription($this->getDescription());
        $clone->setPosition($this->getPosition());
        $clone->update();

        $old_questions = ilSelfEvaluationQuestion::_getAllInstancesForParentId($this->getId());
        foreach ($old_questions as $question) {
            $question->cloneTo($clone->getId());
        }

        $old_feedbacks = ilSelfEvaluationFeedback::_getAllInstancesForParentId($this->getId());
        foreach ($old_feedbacks as $feedback) {
            $feedback->cloneTo($clone->getId());
        }

        return $clone;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public function toXml(SimpleXMLElement $xml)
    {
        $child_xml = $xml->addChild("questionBlock");
        $child_xml->addAttribute("parentId", $this->getParentId());
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("abbreviation", $this->getAbbreviation());
        $child_xml->addAttribute("description", $this->getDescription());
        $child_xml->addAttribute("position", $this->getPosition());

        $questions = ilSelfEvaluationQuestion::_getAllInstancesForParentId($this->getId());

        foreach ($questions as $question) {
            $child_xml = $question->toXml($child_xml);
        }

        $feedbacks = ilSelfEvaluationFeedback::_getAllInstancesForParentId($this->getId());
        foreach ($feedbacks as $feedback) {
            $child_xml = $feedback->toXml($child_xml);
        }

        return $xml;
    }

    static function fromXml(ilDBInterface $db, int $parent_id, SimpleXMLElement $xml)
    {
        $attributes = $xml->attributes();
        $block = new self();
        $block->setParentId($parent_id);
        $block->setTitle($attributes["title"]);
        $block->setAbbreviation($attributes["abbreviation"]);
        $block->setDescription($attributes["description"]);
        $block->setPosition($attributes["position"]);
        $block->create();

        foreach ($xml->question as $question) {
            ilSelfEvaluationQuestion::fromXML($block->getId(), $question);
        }

        foreach ($xml->feedback as $feedback) {
            ilSelfEvaluationFeedback::fromXML($block->getId(), $feedback);
        }

        return $xml;
    }

    /**
     * @param ilSelfEvaluationQuestionBlock $block
     * @param stdClass                      $rec
     */
    protected static function setObjectValuesFromRecord(ilSelfEvaluationBlock &$block = null, $rec = null)
    {
        parent::setObjectValuesFromRecord($block, $rec);
    }

    /**
     * @return array
     */
    protected function getNonDbFields()
    {
        return array_merge(parent::getNonDbFields(), ['scale']);
    }

    /**
     * @param string $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    /**f
     * @return string
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'rep_robj_xsev_block';
    }

    /**
     * @return ilSelfEvaluationBlockTableRow
     */
    public function getBlockTableRow()
    {
        require_once(dirname(__FILE__) . '/Table/class.ilSelfEvaluationQuestionBlockTableRow.php');
        $row = new ilSelfEvaluationQuestionBlockTableRow($this);

        return $row;
    }

    /**
     * @return ilSelfEvaluationQuestion[]
     */
    public function getQuestions()
    {
        return (ilSelfEvaluationQuestion::_getAllInstancesForParentId($this->getId()));
    }
}

