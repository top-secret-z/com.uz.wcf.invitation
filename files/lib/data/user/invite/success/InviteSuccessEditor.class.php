<?php
namespace wcf\data\user\invite\success;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit invitation successes.
 *  
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteSuccessEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = InviteSuccess::class;
}
