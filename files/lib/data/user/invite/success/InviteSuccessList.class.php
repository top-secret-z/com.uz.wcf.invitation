<?php
namespace wcf\data\user\invite\success;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of invitation successes.
 *  
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteSuccessList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = InviteSuccess::class;
}
