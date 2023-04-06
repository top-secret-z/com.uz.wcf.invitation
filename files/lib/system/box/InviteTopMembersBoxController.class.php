<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace wcf\system\box;

use wcf\system\cache\builder\InviteTopMembersBoxCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows users with the most invites.
 */
class InviteTopMembersBoxController extends AbstractBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    public function hasLink(): bool
    {
        return MODULE_MEMBERS_LIST === 1;
    }

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\exception\SystemException
     */
    public function getLink(): string
    {
        if (MODULE_MEMBERS_LIST) {
            $parameters = 'sortField=invites&sortOrder=DESC';

            return LinkHandler::getInstance()->getLink('MembersList', [], $parameters);
        }

        return '';
    }

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\exception\SystemException
     */
    protected function loadContent(): void
    {
        if (MODULE_INVITE) {
            $userIDs = InviteTopMembersBoxCacheBuilder::getInstance()->getData();

            if (!empty($userIDs)) {
                $userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);

                WCF::getTPL()->assign([
                    'userProfiles' => $userProfiles,
                ]);

                $this->content = WCF::getTPL()->fetch('boxInviteTopMembers');
            }
        }
    }
}
