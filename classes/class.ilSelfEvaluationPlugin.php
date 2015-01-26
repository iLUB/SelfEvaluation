<?php

include_once('./Services/Repository/classes/class.ilRepositoryObjectPlugin.php');
require_once('class.ilSelfEvaluationConfig.php');

/**
 * SelfEvaluation repository object plugin
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ilSelfEvaluationPlugin extends ilRepositoryObjectPlugin {

	/**
	 * @return string
	 */
	function getPluginName() {
		return 'SelfEvaluation';
	}


	/**
	 * @return ilSelfEvaluationConfig
	 */
	public function getConfigObject() {
		$conf = new ilSelfEvaluationConfig($this->getConfigTableName());

		return $conf;
	}


	/**
	 * @return string
	 */
	public function getConfigTableName() {
		return 'rep_robj_xsev_c';
	}
}

?>
