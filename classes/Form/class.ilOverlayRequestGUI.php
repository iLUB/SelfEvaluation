<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
/**
 * GUI-Class ilOverlayRequestGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 */
class ilOverlayRequestGUI {

	const FUNCTION_NAME = 'overlayRequest';
	/**
	 * @var string
	 */
	protected $add_new_link = '';
	/**
	 * @var string
	 */
	protected $html = '';


	function __construct($disable = false) {
		$this->pl = new ilSelfEvaluationPlugin();
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		$this->html = $this->pl->getTemplate('default/Form/tpl.async.html', true, true);
		$this->html->setVariable('FUNCTION_NAME', self::FUNCTION_NAME);
		if ($this->getAddNewLink()) {
			$this->html->setCurrentBlock('add_new');
			$this->html->setVariable('FUNCTION_NAME', self::FUNCTION_NAME);
			$this->html->setVariable('ADD_NEW', $this->getAddNewLink());
			$this->html->parseCurrentBlock();
		}

		return $this->html->get();
	}


	/**
	 * @param string $add_new_link
	 */
	public function setAddNewLink($add_new_link) {
		$this->add_new_link = $add_new_link;
	}


	/**
	 * @return string
	 */
	public function getAddNewLink() {
		return $this->add_new_link;
	}


	//
	// Static
	//
	/**
	 * @param string $link
	 * @param bool   $disable
	 *
	 * @return string
	 */
	public static function getLink($link = '', $disable = false) {
		$pl = new ilSelfEvaluationPlugin();
		if ($pl->getConfigObject()->getAsync() AND ! $disable) {
			return 'javascript:$.fn.' . self::FUNCTION_NAME . '(\'' . $link . '\');';
		} else {
			return $link;
		}
	}
}

?>