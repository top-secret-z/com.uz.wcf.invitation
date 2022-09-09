<?php
namespace wcf\data\user\invite;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit invitations.
 *  
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Invite::class;
}
