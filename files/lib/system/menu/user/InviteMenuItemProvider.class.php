<?php
namespace wcf\system\menu\user;
use wcf\system\WCF;

/**
 * UserMenuItemProvider for Invitation
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteMenuItemProvider extends DefaultUserMenuItemProvider {
	/**
	 * @inheritDoc
	 */
	public function isVisible() {
		if (WCF::getSession()->getPermission('user.profile.canInvite')) return true;
		return false;
	}
}
