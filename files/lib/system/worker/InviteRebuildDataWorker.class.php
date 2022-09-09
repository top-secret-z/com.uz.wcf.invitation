<?php
namespace wcf\system\worker;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Notification event for invites.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = UserList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 100;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'user_table.userID';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if (!$this->loopCount) {
			// reset activity points
			UserActivityPointHandler::getInstance()->reset('com.uz.wcf.invitation.activityPointEvent.submit');
			UserActivityPointHandler::getInstance()->reset('com.uz.wcf.invitation.activityPointEvent.success');
			
			// reset invite counts in user
			$sql = "UPDATE	wcf".WCF_N."_user
					SET	invites = ?, inviteSuccess = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([0, 0]);
		}
		
		if (!count($this->objectList)) {
			return;
		}
		
		$inviteToUser = $successToUser = [];
		foreach ($this->objectList as $user) {
			$invite = $success = 0;
			
			$sql = "SELECT	COUNT(*) AS counter
					FROM	wcf".WCF_N."_user_invite
					WHERE	inviterID = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$user->userID]);
			$invite = $statement->fetchColumn();
			
			$sql = "SELECT	COUNT(*) AS counter
					FROM	wcf".WCF_N."_user_invite_success
					WHERE	inviterID = ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([$user->userID]);
			$success = $statement->fetchColumn();
			
			if ($invite || $success) {
				// update user
				$editor = new UserEditor($user);
				$editor->updateCounters([
						'invites' => $invite,
						'inviteSuccess' => $success
				]);
				
				// update point arrays
				if ($invite) $inviteToUser[$user->userID] = $invite;
				if ($success) $successToUser[$user->userID] = $success;
			}
		}
		
		// update activity points
		if (count($inviteToUser)) UserActivityPointHandler::getInstance()->fireEvents('com.uz.wcf.invitation.activityPointEvent.submit', $inviteToUser, true);
		if (count($successToUser)) UserActivityPointHandler::getInstance()->fireEvents('com.uz.wcf.invitation.activityPointEvent.success', $successToUser, true);
	}
}
