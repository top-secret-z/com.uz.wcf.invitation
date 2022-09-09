<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches members with the most invites.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
 */
class InviteTopMembersBoxCacheBuilder extends AbstractSortedUserCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 300;
	
	/**
	 * @inheritDoc
	 */
	protected $defaultLimit = INVITE_DISPLAY_BOX_ENTRIES + 1;
	
	/**
	 * @inheritDoc
	 */
	protected $positiveValuesOnly = true;
	
	/**
	 * @inheritDoc
	 */
	protected $sortField = 'invites';
}
