<?php
namespace wcf\system\user\activity\event;
use wcf\data\user\invite\InviteList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for invite action.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$objectIDs = [];
		foreach ($events as $event) {
			$objectIDs[] = $event->objectID;
		}
		
		// fetch invitations
		$inviteList = new InviteList();
		$inviteList->getConditionBuilder()->add("user_invite.inviteID IN (?)", [$objectIDs]);
		$inviteList->readObjects();
		$invites = $inviteList->getObjects();
		
		// set message
		foreach ($events as $event) {
			if (isset($invites[$event->objectID])) {
				$invite = $invites[$event->objectID];
				
				// check permissions
				if (!$invite->canSee()) {
					continue;
				}
				$event->setIsAccessible();
				
				// title and description
				$text = WCF::getLanguage()->getDynamicVariable('wcf.user.invite.recentActivity.submit', ['user' => WCF::getUser()]);
				$event->setTitle($text);
				$event->setDescription('');
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
