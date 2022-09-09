<?php
namespace wcf\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds 'invites' sort field for members list.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteMembersListPageListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$eventObj->validSortFields[] = 'invites';
		$eventObj->validSortFields[] = 'inviteSuccess';
	}
}
