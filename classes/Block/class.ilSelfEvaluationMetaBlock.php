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
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationBlock.php');
require_once(dirname(__FILE__) . '/../Question/class.ilSelfEvaluationMetaQuestionFactory.php');
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinitionContainer.php');

/**
 * Class ilSelfEvaluationMetaBlock
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaBlock extends ilSelfEvaluationBlock {

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
    public function cloneTo($parent_id){
        $clone = new self();
        $clone->setParentId($parent_id);
        $clone->setTitle($this->getTitle());
        $clone->setDescription($this->getDescription());
        $clone->setPosition($this->getPosition());
        $clone->update();

        $old_questions = ilSelfEvaluationMetaQuestion::_getAllInstancesForParentId($this->getId());

        foreach ($old_questions as $question){
            $question->cloneTo($clone->getId());
        }

        return $clone;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public function toXml(SimpleXMLElement $xml){
        $child_xml = $xml->addChild("metaBlock");
        $child_xml->addAttribute("parentId",$this->getParentId());
        $child_xml->addAttribute("title",$this->getTitle());
        $child_xml->addAttribute("description",$this->getDescription());
        $child_xml->addAttribute("position",$this->getPosition());

        $questions = ilSelfEvaluationMetaQuestion::_getAllInstancesForParentId($this->getId());

        foreach ($questions as $question){
            $child_xml = $question->toXml($child_xml);
        }

        return $xml;
    }

    /**
     * @param $parent_id
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    static function fromXml($parent_id, SimpleXMLElement $xml){
        $attributes =  $xml->attributes();
        $block = new self();
        $block->setParentId($parent_id);
        $block->setTitle($attributes["title"]);
        $block->setDescription($attributes["description"]);
        $block->setPosition($attributes["position"]);
        $block->create();

        foreach ($xml->metaQuestion as $question){
            ilSelfEvaluationMetaQuestion::fromXML($block->getId(),$question);
        }

        return $xml;
    }

	/**
	 * @param ilSelfEvaluationBlock $block
	 * @param stdClass                  $rec
	 */
	protected static function setObjectValuesFromRecord(ilSelfEvaluationBlock
	                                             &$block = null, $rec = null) {
		parent::setObjectValuesFromRecord($block, $rec);
		$block->initMetaContainer();
	}


	/**
	 * @return array
	 */
	protected function getNonDbFields() {
		return array_merge(parent::getNonDbFields(), array('meta_container'));
	}


	/**
	 * @return string
	 */
	public static function getTableName() {
		return 'rep_robj_xsev_mblock';
	}


	public function initDB() {
		parent::initDB();
		$this->initMetaContainer();
		$this->meta_container->initDB();
	}


	/**
	 * @return ilSelfEvaluationBlockTableRow
	 */
	public function getBlockTableRow() {
		require_once(dirname(__FILE__) . '/Table/class.ilSelfEvaluationMetaBlockTableRow.php');
		$row = new ilSelfEvaluationMetaBlockTableRow($this);

		return $row;
	}


	/**
	 * @return \iLubFieldDefinitionContainer
	 */
	public function getMetaContainer() {
		return $this->meta_container;
	}


	public function initMetaContainer() {
		$factory = new ilSelfEvaluationMetaQuestionFactory();
		$this->meta_container = new iLubFieldDefinitionContainer($factory, $this->getId());
	}


	public function delete() {
		// delete meta questions
		foreach ($this->getMetaContainer()->getFieldDefinitions() as $field) {
			$field->delete();
		}
		// delete meta question block
		return parent::delete();
	}
}