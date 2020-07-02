<?php
require_once __DIR__ . '/../vendor/autoload.php';

class ilObjSelfEvaluationListGUI extends ilObjectPluginListGUI
{

    /**
     * @var ilSelfEvaluationPlugin
     */
    protected $plugin;

    /**
     *
     */
    function initType()
    {
        $this->enableTimings(false);
        $this->setType('xsev');
    }

    function getGuiClass()
    {
        return 'ilObjSelfEvaluationGUI';
    }

    function initCommands()
    {
        return [
            [
                'permission' => 'read',
                'cmd' => 'showContent',
                'default' => true
            ],
            [
                'permission' => 'write',
                'cmd' => 'editProperties',
                'txt' => $this->txt('edit'),
                'default' => false
            ],
        ];
    }

    /**
     * @return array
     */
    function getProperties()
    {
        $props = [];
        $object = new ilObjSelfEvaluation($this->ref_id);
        if (!$object->isOnline()) {
            $props[] = [
                'alert' => true,
                'property' => $this->txt('status'),
                'value' => $this->txt('offline')
            ];
        }

        return $props;
    }
}


