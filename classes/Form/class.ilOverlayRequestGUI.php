<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
/**
 * GUI-Class ilOverlayRequestGUI
 *
 * @author            Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version           $Id:
 */
class ilOverlayRequestGUI {

	const AJAX = true;
	const FUNCTION_NAME = 'overlayRequest';
	/**
	 * @var string
	 */
	protected $add_new_link = '';
	/**
	 * @var string
	 */
	protected $html = '';


	function __construct() {
		$this->pl = new ilSelfEvaluationPlugin();
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		$this->html = $this->pl->getTemplate('default/tpl.async.html', true, true);
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
	 *
	 * @return string
	 */
	public static function getLink($link = '') {
		if (self::AJAX) {
			return 'javascript:$.fn.' . self::FUNCTION_NAME . '(\'' . $link . '\');';
		} else {
			return $link;
		}
	}
}

?>