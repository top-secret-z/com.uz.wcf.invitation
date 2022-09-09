<?php
namespace wcf\system\user\notification\object;
use wcf\data\user\invite\success\InviteSuccess;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Represents an invitation as a notification object.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteSuccessUserNotificationObject extends DatabaseObjectDecorator implements IStackableUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = InviteSuccess::class;
	
	/**
	 * returns the title
	 */
	public function getTitle() {
		return '';
	}
	
	/**
	 * returns the URL
	 */
	public function getURL() {
		return '';
	}
	
	/**
	 * returns the userID
	 */
	public function getAuthorID() {
		//return $this->userID;
		return WCF::getUser()->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRelatedObjectID() {
	//	return $this->inviterID;
	//	return WCF::getUser()->userID;
	}
}
