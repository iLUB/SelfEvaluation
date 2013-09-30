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


	function __construct() {
		$this->pl = new ilSelfEvaluationPlugin();
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		//		$this->form = new ilPropertyFormGUI();
		//		$te = new ilTextAreaInputGUI($this->pl->txt('test'), 'test');
		//		$te->setUseRte(true);
		//		$this->form->addItem($te);
		$this->html = $this->pl->getTemplate('default/tpl.async.html', false, false);
		$this->html->setVariable('FUNCTION_NAME', self::FUNCTION_NAME);

		//$this->html->setVariable('FORM', $this->form->getHTML());
		return $this->html->get();
	}


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