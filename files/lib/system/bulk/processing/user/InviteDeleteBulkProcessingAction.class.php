<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\DatabaseObjectList;
use wcf\data\user\UserList;
use wcf\system\cache\builder\InviteTopMembersBoxCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for deleting invitations.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteDeleteBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		$users = $objectList->getObjects();
		
		if (!empty($users)) {
			$userIDs = $inviteToUser = $successToUser = [];
			foreach ($users as $user) {
				$userIDs[] = $user->userID;
				
				if (!isset($inviteToUser[$user->userID])) {
					$inviteToUser[$user->userID] = 0;
				}
				$inviteToUser[$user->userID] += $user->invites;
				
				if (!isset($successToUser[$user->userID])) {
					$successToUser[$user->userID] = 0;
				}
				$successToUser[$user->userID] += $user->inviteSuccess;
			}
			
			// remove points
			UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.invitation.activityPointEvent.submit', $inviteToUser);
			UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.invitation.activityPointEvent.success', $successToUser);
			
			// remove user counts
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("userID IN (?)", [$userIDs]);
			
			$sql = "UPDATE	wcf".WCF_N."_user
					SET invites = 0, inviteSuccess = 0
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			// reset invitation box cache
			InviteTopMembersBoxCacheBuilder::getInstance()->reset();
			
			// remove invites and success
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("inviterID IN (?)", [$userIDs]);
			$sql = "DELETE FROM	wcf".WCF_N."_user_invite
					".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("inviterID IN (?)", [$userIDs]);
			$conditions->add("inviteID IS NULL");
			$sql = "DELETE FROM	wcf".WCF_N."_user_invite_success
					".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$userList = parent::getObjectList();
		
		// only users with invites
		$userList->getConditionBuilder()->add("(user_table.invites > ? OR user_table.inviteSuccess > ?)", [0, 0]);
		
		return $userList;
	}
}
