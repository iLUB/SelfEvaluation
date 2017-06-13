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

/**
 * Class iLubFieldDefinitionLng
 *
 * @author  Fabio Heer <fabio.heer@ilub.unibe.ch>
 * @version $Id$
 */
class iLubFieldDefinitionLng {

	/**
	 * @return string
	 */
	public function getTxtFieldAdded() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Eine neues Datenfeld wurde angelegt.';
		} else {
			return 'Added a new data field.';
		}
	}


	/**
	 * @return string
	 */
	public function getTxtConfirmDelete() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Sind Sie sicher, dass Sie dieses Datenfeld löschen möchten?';
		} else {
			return 'Do you really want to delete these user fields?';
		}
	}


	/**
	 * @return string
	 */
	public function getTxtAddField() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Neues Datenfeld anlegen';
		} else {
			return 'Add a data field';
		}
	}


	/**
	 * @return string
	 */
	public function getSortingSaved() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Reihenfolge gespeichert';
		} else {
			return 'Saved order';
		}
	}

	/**
	 * @return string
	 */
	public function getMatrixScale() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Skala';
		} else {
			return 'Scale';
		}
	}


	/**
	 * @return string
	 */
	public function getMatrixScaleDescription() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Skala der Matrixfrage (horizontal)';
		} else {
			return 'Scale of the Matrix question (horizontal)';
		}
	}

	/**
	 * @return string
	 */
	public function getMatrixQuestion() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Fragen';
		} else {
			return 'Fragen';
		}
	}

	/**
	 * @return string
	 */
	public function getMatrixQuestionDescription() {
		global $ilUser;

		if ($ilUser->getCurrentLanguage() == 'de') {
			return 'Liste mit Fragen (vertikal)';
		} else {
			return 'List with questions (vertical)';
		}
	}
}