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
require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinitionContainerGUI.php');

/**
 * Class ilSelfEvaluationMetaQuestionGUI
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class ilSelfEvaluationMetaQuestionGUI extends iLubFieldDefinitionContainerGUI
{

    const POSTVAR_PREFIX = 'mqst_';
    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;
    /**
     * @var string
     */
    protected $block_title;

    /**
     * @param iLubFieldDefinitionContainer $container
     * @param string                       $block_title
     * @param ilSelfEvaluationPlugin       $plugin
     * @param int                          $self_eval_id
     */
    public function __construct(
        iLubFieldDefinitionContainer $container,
        $block_title,
        ilSelfEvaluationPlugin $plugin,
        $self_eval_id
    ) {
        $this->plugin = $plugin;
        $this->block_title = $block_title;

        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/class.iLubFieldDefinitionLng.php');
        $lng = new iLubFieldDefinitionLng();
        parent::__construct($container, self::getTypesArray(), $lng, $self_eval_id);
    }

    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'block_id');
        $this->tabs_gui->setTabActive('administration');
        parent::executeCommand();
    }

    protected function listFields()
    {
        /**
         * @var ilToolbarGUI $ilToolbar
         */
        global $ilToolbar;

        $ilToolbar->addButton('<b>&lt;&lt; ' . $this->plugin->txt('back_to_blocks') . '</b>',
            $this->ctrl->getLinkTargetByClass('ilSelfEvaluationListBlocksGUI', 'showContent'));
        parent::listFields();
        $this->tpl->addJavaScript($this->plugin->getDirectory() . '/templates/sortable.js');
    }

    /**
     * @return iLubFieldDefinitionTableGUI
     */
    protected function createILubFieldDefinitionTableGUI()
    {
        $table = parent::createILubFieldDefinitionTableGUI();
        $table->setTitle($this->block_title . ': ' . $this->plugin->txt('question_table_title'));

        return $table;
    }

    static function getTypesArray()
    {
        // Add the allowed types here
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeText.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeSelect.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeSingleChoice.php');
        require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/iLubFieldDefinition/classes/types/class.iLubFieldDefinitionTypeMatrix.php');

        $types[] = new iLubFieldDefinitionTypeText();
        $types[] = new iLubFieldDefinitionTypeSelect();
        $types[] = new iLubFieldDefinitionTypeSingleChoice();
        $types[] = new iLubFieldDefinitionTypeMatrix();

        /*
         * TODO add a radio button type for the gender, a text-area type for arbitrarily long text and a select-year-of-birth type
         * -> create new iLubFieldDefinitionTypeXYZ objects and add them here
         * @see http://ilublx3.unibe.ch:8080/mantis/view.php?id=514#c928
         */
        return $types;
    }
} 