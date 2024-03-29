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

namespace wcf\data\user\invite\success;

use wcf\data\DatabaseObject;
use wcf\system\cache\runtime\UserRuntimeCache;

/**
 * @property int $successID
 * @property null|int $inviteID
 * @property null|int $inviterID
 * @property string $inviterName
 * @property null|int $userID
 * @property string $username
 * @property int $time
 */
class InviteSuccess extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'user_invite_success';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'successID';

    /**
     * returns for a specific user the names of users who registered per username registration
     *
     * @throws \wcf\system\exception\SystemException
     */
    public static function getUsernamesOfNameRegistration(int $userID): string
    {
        $nameList = new InviteSuccessList();
        $nameList->getConditionBuilder()->add('inviterID = ?', [$userID]);
        $nameList->getConditionBuilder()->add('inviteID IS NULL');
        $nameList->sqlOrderBy = 'username ASC';
        $nameList->readObjects();

        $names = [];

        /** @var self $name */
        foreach ($nameList->getObjects() as $name) {
            if ($name->userID) {
                $user = UserRuntimeCache::getInstance()->getObject($name->userID);

                if (null !== $user) {
                    $names[] = '<a class="userLink" href="' . $user->getLink(
                    ) . '" data-user-id="' . $name->userID . '">' . $name->username . '</a>';
                } else {
                    $names[] = $name->username;
                }
            } else {
                $names[] = $name->username;
            }
        }

        return \implode(', ', $names);
    }
}
