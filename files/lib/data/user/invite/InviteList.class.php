<?php
namespace wcf\data\user\invite;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of invitations.
 *  
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Invite::class;
}
