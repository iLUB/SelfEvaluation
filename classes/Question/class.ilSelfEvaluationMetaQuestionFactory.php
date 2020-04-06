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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinitionFactory.php');
require_once(dirname(__FILE__) . '/class.ilSelfEvaluationMetaQuestion.php');

/**
 * Class ilSelfEvaluaitonMetaQuestionFactory
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaQuestionFactory extends iLubFieldDefinitionFactory
{

    /**
     * @param int $container_id
     * @param int $field_id
     * @return iLubFieldDefinition
     */
    public function createILubFieldDefinition($container_id = 0, $field_id = 0)
    {
        return new ilSelfEvaluationMetaQuestion($container_id, $field_id);
    }
}