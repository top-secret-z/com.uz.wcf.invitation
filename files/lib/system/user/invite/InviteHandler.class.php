<?php
namespace wcf\system\user\invite;
use wcf\data\user\User;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles invite.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteHandler extends SingletonFactory {
	/**
	 * Returns a user's invite count.
	 */
	public function getCount($user = null) {
		if ($user === null) $user = WCF::getUser();
		if (!$user->userID) return 0;
		
		$sql = "SELECT  COUNT(*) AS count
				FROM    wcf".WCF_N."_user_invite
				WHERE   inviterID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$user->userID]);
		return $statement->fetchColumn();
	}
}
