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
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationMetaBlock.php');
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationQuestionBlock.php');

/**
 * Class ilSelfEvaluationBlockFactory
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationBlockFactory
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @param int $self_eval_id
     */
    public function __construct($self_eval_id)
    {
        $this->id = $self_eval_id;
    }

    /**
     * @return ilSelfEvaluationBlock[]
     */
    public function getAllBlocks()
    {
        $blocks = ilSelfEvaluationQuestionBlock::getAllInstancesByParentId($this->id);

        $blocks = array_merge($blocks, ilSelfEvaluationMetaBlock::getAllInstancesByParentId($this->id));

        $this->sortByPosition($blocks);

        return $blocks;
    }

    /**
     * @param $self_eval_id
     * @return int
     */
    public static function getNextPositionAcrossBlocks($self_eval_id)
    {
        $block = new ilSelfEvaluationQuestionBlock();
        $pos = $block->getNextPosition($self_eval_id);
        $block = new ilSelfEvaluationMetaBlock();
        $pos = max($block->getNextPosition($self_eval_id), $pos);

        return $pos;
    }

    /**
     * usort callback function
     * @param ilSelfEvaluationBlock $a
     * @param ilSelfEvaluationBlock $b
     * @return int
     */
    protected function positionSort(ilSelfEvaluationBlock $a, ilSelfEvaluationBlock $b)
    {
        if ($a->getPosition() == $b->getPosition()) {

            return 0; // a and b are equal

        } else {
            if ($a->getPosition() > $b->getPosition()) {

                return 1; // a is after b
            } else {

                return -1; // a is before b
            }
        }
    }

    /**
     * @param ilSelfEvaluationBlock[] $blocks
     * @return bool
     */
    public function sortByPosition(&$blocks)
    {
        return usort($blocks, [get_class(), "positionSort"]);
    }
}