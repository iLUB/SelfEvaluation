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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinition.php');

/**
 * Class ilSelfEvaluationMetaQuestion
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaQuestion extends iLubFieldDefinition
{

    const TABLE_NAME = 'rep_robj_xsev_mqst';

    /**
     * @param string $container_id
     * @param int    $id
     */
    public function __construct($container_id, $id = 0)
    {
        parent::__construct(self::TABLE_NAME, $container_id, $id);
    }

    /**
     * @param $parent_id
     * @return ilSelfEvaluationMetaQuestion
     */
    public function cloneTo($parent_id)
    {
        $clone = new self($parent_id);
        $clone = $this->cloneToObject($clone);
        $clone->setContainerId($parent_id);
        $clone->save();
        return $clone;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public function toXml(SimpleXMLElement $xml)
    {
        $child_xml = $xml->addChild("metaQuestion");
        $child_xml->addAttribute("containerId", $this->getContainerId());
        $child_xml->addAttribute("name", $this->getName());
        $child_xml->addAttribute("shortTitle", $this->getShortTitle());
        $child_xml->addAttribute("typeId", $this->getTypeId());
        $child_xml->addAttribute("values", serialize($this->getValues()));
        $child_xml->addAttribute("enableRequired", $this->isRequired());
        $child_xml->addAttribute("position", $this->getPosition());
        return $xml;
    }

    /**
     * @param                  $parent_id
     * @param SimpleXMLElement $xml
     * @return SimpleXMLElement
     */
    public static function fromXml($parent_id, SimpleXMLElement $xml)
    {
        $attributes = $xml->attributes();
        $question = new self($parent_id);
        $question->setName($attributes["name"]);
        $question->setShortTitle($attributes["shortTitle"]);
        $question->setTypeId($attributes["typeId"]);
        $question->setValues(unserialize($attributes["values"]));
        $question->enableRequired($attributes["enableRequired"] == "1" ? true : false);
        $question->setPosition($attributes["position"]);
        $question->save();
        return $xml;
    }

    /**
     * @param int $field_id
     * @return bool
     */
    public static function isObject($field_id)
    {
        global $ilDB;

        $set = $ilDB->query('SELECT field_id FROM ' . self::TABLE_NAME . ' WHERE field_id = '
            . $ilDB->quote($field_id, 'integer'));

        while ($rec = $ilDB->fetchObject($set)) {
            return true;
        }

        return false;
    }

    //
    // Static
    //
    /**
     * @param int  $parent_id is a meta_block id
     * @param bool $as_array
     * @return ilSelfEvaluationMetaQuestion[]
     */
    public static function _getAllInstancesForParentId($parent_id, $as_array = false)
    {
        global $ilDB;
        $return = array();
        $set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' ' . ' WHERE container_id = '
            . $ilDB->quote($parent_id, 'integer') . ' ORDER BY position ASC');

        while ($rec = $ilDB->fetchObject($set)) {
            if ($as_array) {
                $return[$rec->field_id] = (array) new self($parent_id, $rec->field_id);
            } else {
                $return[$rec->field_id] = new self($parent_id, $rec->field_id);
            }
        }

        return $return;
    }
} 