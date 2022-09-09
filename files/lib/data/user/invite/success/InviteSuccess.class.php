<?php 
namespace wcf\data\user\invite\success;
use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\WCF;

/**
 * Represents an invitation success.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteSuccess extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_invite_success';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'successID';
	
	/**
	 * returns for a specific user the names of users who registered per username registration
	 */
	public static function getUsernamesOfNameRegistration($userID) {
		
		$nameList = new InviteSuccessList();
		$nameList->getConditionBuilder()->add('inviterID = ?', [$userID]);
		$nameList->getConditionBuilder()->add('inviteID IS NULL');
		$nameList->sqlOrderBy = 'username ASC';
		$nameList->readObjects();
		
		$names = [];
		foreach($nameList->getObjects() as $name) {
			if ($name->userID) {
				$user = UserRuntimeCache::getInstance()->getObject($name->userID);
				$names[] = '<a class="userLink" href="' . $user->getLink() . '" data-user-id="' . $name->userID . '">' . $name->username . '</a>';
			}
			else {
				$names[] = $name->username;
			}
		}
		return implode(', ', $names);
	}
}
