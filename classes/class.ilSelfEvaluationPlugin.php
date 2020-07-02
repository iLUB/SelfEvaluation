<?php
require_once __DIR__ . '/../vendor/autoload.php';

class ilSelfEvaluationPlugin extends ilRepositoryObjectPlugin
{

    /**
     * @return string
     */
    function getPluginName()
    {
        return 'SelfEvaluation';
    }

    /**
     * @return ilSelfEvaluationConfig
     */
    public function getConfigObject()
    {
        $conf = new ilSelfEvaluationConfig($this->getConfigTableName());

        return $conf;
    }

    /**
     * @return string
     */
    public function getConfigTableName()
    {
        return 'rep_robj_xsev_c';
    }

    protected function uninstallCustom()
    {
        return;
    }

    /**
     * decides if this repository plugin can be copied
     * @return bool
     */
    public function allowCopy()
    {
        return true;
    }
}


