<?php
use ilDBInterface;
use ilub\plugin\SelfEvaluation\Question\MetaQuestion;

require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationMetaQuestionFactory.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinitionContainer.php');

/**
 * Class ilSelfEvaluationMetaBlock
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaBlock extends ilSelfEvaluationBlock
{

    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var int
     */
    protected $position = 99;
    /**
     * @var int
     */
    protected $parent_id = 0;

    /**
     * @var iLubFieldDefinitionContainer
     */
    protected $meta_container;

    /**
     * @param $parent_ref_id
     * @return ilSelfEvaluationMetaBlock
     */
    public function cloneTo($parent_id)
    {
        $clone = new self();
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setDescription($this->getDescription());
        $clone->setPosition($this->getPosition());
        $clone->update();

        $old_questions = ilSelfEvaluationMetaQuestion::_getAllInstancesForParentId($this->db, $this->getId());

        foreach ($old_questions as $question) {
            $question->cloneTo($clone->getId());
        }

        return $clone;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public function toXml(SimpleXMLElement $xml)
    {
        $child_xml = $xml->addChild("metaBlock");
        $child_xml->addAttribute("parentId", $this->getParentId());
        $child_xml->addAttribute("title", $this->getTitle());
        $child_xml->addAttribute("description", $this->getDescription());
        $child_xml->addAttribute("position", $this->getPosition());

        $questions = MetaQuestion::_getAllInstancesForParentId($this->db, $this->getId());

        foreach ($questions as $question) {
            $child_xml = $question->toXml($child_xml);
        }

        return $xml;
    }

    static function fromXml(ilDBInterface $db, $parent_id, SimpleXMLElement $xml)
    {
        $attributes = $xml->attributes();
        $block = new self();
        $block->setParentId($parent_id);
        $block->setTitle($attributes["title"]);
        $block->setDescription($attributes["description"]);
        $block->setPosition($attributes["position"]);
        $block->create();

        foreach ($xml->metaQuestion as $question) {
            MetaQuestion::fromXML($db,$block->getId(), $question);
        }

        return $xml;
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'rep_robj_xsev_mblock';
    }
    /**
     * @return ilSelfEvaluationBlockTableRow
     */
    public function getBlockTableRow()
    {
        require_once(dirname(__FILE__) . '/Table/class.ilSelfEvaluationMetaBlockTableRow.php');
        $row = new ilSelfEvaluationMetaBlockTableRow($this);

        return $row;
    }

    public function delete()
    {
        $questions = MetaQuestion::_getAllInstancesForParentId($this->db, $this->getId());

        foreach ($questions as $question) {
            $question->delete();
        }
        return parent::delete();
    }
}