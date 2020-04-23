<?php
namespace ilub\plugin\SelfEvaluation\Block\Meta;

use ilDBInterface;
use ilub\plugin\SelfEvaluation\Question\Meta\MetaQuestion;
use ilub\plugin\SelfEvaluation\Block\Block;
use SimpleXMLElement;
use ilCtrl;
use ilSelfEvaluationPlugin;
use ilub\plugin\SelfEvaluation\Block\BlockTableRow;

class MetaBlock extends Block
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
     * @var MetaQuestion
     */
    protected $meta_container;

    public function cloneTo($parent_id) : self
    {
        $clone = new self($this->db);
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setDescription($this->getDescription());
        $clone->setPosition($this->getPosition());
        $clone->update();

        $old_questions = MetaQuestion::_getAllInstancesForParentId($this->db, $this->getId());

        foreach ($old_questions as $question) {
            $question->cloneTo($this->db, $clone->getId());
        }

        return $clone;
    }

    public function toXml(SimpleXMLElement $xml) : SimpleXMLElement
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

    static function fromXml(ilDBInterface $db, $parent_id, SimpleXMLElement $xml) : SimpleXMLElement
    {
        $attributes = $xml->attributes();
        $block = new self($db);
        $block->setParentId($parent_id);
        $block->setTitle($attributes["title"]);
        $block->setDescription($attributes["description"]);
        $block->setPosition((int) $attributes["position"]);
        $block->create();

        foreach ($xml->metaQuestion as $question) {
            MetaQuestion::fromXML($db, $block->getId(), $question);
        }

        return $xml;
    }

    public static function getTableName() : string
    {
        return 'rep_robj_xsev_mblock';
    }

    public function getMetaContainer() : MetaQuestion
    {
        return $this->meta_container;
    }

    public function delete()
    {
        $questions = MetaQuestion::_getAllInstancesForParentId($this->db, $this->getId());

        foreach ($questions as $question) {
            $question->delete();
        }
        return parent::delete();
    }

    public function getBlockTableRow(ilDBInterface $db,ilCtrl $ilCtrl, ilSelfEvaluationPlugin $plugin) : BlockTableRow
    {
        return new MetaBlockTableRow($ilCtrl,$plugin,$this);
    }
}