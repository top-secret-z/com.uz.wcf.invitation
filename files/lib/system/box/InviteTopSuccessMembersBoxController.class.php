<?php
namespace wcf\system\box;
use wcf\system\cache\builder\InviteTopSuccessMembersBoxCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows users with the most successful invites.
 *
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteTopSuccessMembersBoxController extends AbstractBoxController {
	/**
	 * @inheritDoc
	 */
	protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];
	
	/**
	 * @see	wcf\system\box\IBoxController::hasLink()
	 */
	public function hasLink() {
		return MODULE_MEMBERS_LIST == 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		if (MODULE_MEMBERS_LIST) {
			$parameters = 'sortField=inviteSuccess&sortOrder=DESC';
			
			return LinkHandler::getInstance()->getLink('MembersList', [], $parameters);
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		if (MODULE_INVITE) {
			$userIDs = InviteTopSuccessMembersBoxCacheBuilder::getInstance()->getData();
			
			if (!empty($userIDs)) {
				$userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
				
				WCF::getTPL()->assign([
						'userProfiles' => $userProfiles
				]);
				$this->content = WCF::getTPL()->fetch('boxInviteTopSuccessMembers');
			}
		}
	}
}
