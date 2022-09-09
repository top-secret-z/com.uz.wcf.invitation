<?php
namespace wcf\page;
use wcf\data\user\invite\InviteList;
use wcf\data\user\invite\success\InviteSuccess;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows the user's invitations.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteListUserPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.profile.canInvite'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = InviteList::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'user_invite.time DESC';
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 10;
	
	/**
	 * usernames of users who registered entering inviter username
	 */
	public $usernames = '';
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("user_invite.inviterID = ?", [WCF::getUser()->userID]);
		
		$this->usernames = InviteSuccess::getUsernamesOfNameRegistration(WCF::getUser()->userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'usernames' => $this->usernames
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.invite.list');
		
		parent::show();
	}
}
