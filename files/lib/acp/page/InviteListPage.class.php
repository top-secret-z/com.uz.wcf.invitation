<?php
namespace wcf\acp\page;
use wcf\data\user\invite\InviteList;
use wcf\page\SortablePage;

/**
 * Shows all invitations.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.inviteList';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canEditGroup', 'admin.user.canDeleteGroup'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['inviteID', 'code', 'emails', 'inviterName', 'successCount'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = InviteList::class;
}
