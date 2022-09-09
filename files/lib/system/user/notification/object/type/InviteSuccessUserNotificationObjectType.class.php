<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\user\User;
use wcf\data\user\invite\success\InviteSuccess;
use wcf\data\user\invite\success\InviteSuccessList;
use wcf\system\user\notification\object\InviteSuccessUserNotificationObject;

/**
 * Represents a user as a notification object type.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteSuccessUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = InviteSuccessUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = InviteSuccess::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = InviteSuccessList::class;
}
