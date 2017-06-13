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
require_once('Services/Form/classes/class.ilRadioOption.php');

/**
 * Class xbgRadioOptionWithImage
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class xbgRadioOptionWithImage extends ilRadioOption {

	/**
	 * @param string $radio_group_field_id  use ilRadioGroupInputGUI->getFieldId()
	 * @param string $title                 text of the radio option
	 * @param string $value                 value of the radio option
	 * @param string $image_path            path to the image
	 * @param string $image_alt_text        alternative text
	 * @param string $class                 this class is set to the image element
	 */
	function __construct($radio_group_field_id, $title, $value, $image_path, $image_alt_text = '', $class = 'radio_img') {
		$this->setTitle($title);
		$this->setValue($value);
		if ($image_alt_text = '') {
			$image_alt_text = $value;
		}

		$template = new ilTemplate('Customizing/global/plugins/Services/Repository/RepositoryObject/SelfEvaluation/classes/InputGUIs/templates/tpl.radio_option_with_images.html', TRUE, TRUE);
		$template->setVariable('OP_ID', $radio_group_field_id . '_' . $value);
		$template->setVariable('IMG_PATH', $image_path);
		$template->setVariable('IMG_CLASS', $class);
		$template->setVariable('ALT_TXT', $image_alt_text);

		$this->setInfo($template->get());
	}
}