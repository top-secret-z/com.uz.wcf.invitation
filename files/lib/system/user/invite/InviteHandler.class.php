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

namespace wcf\system\user\invite;

use wcf\data\user\User;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles invite.
 */
class InviteHandler extends SingletonFactory
{
    /**
     * Returns a user's invite count.
     */
    public function getCount($user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }
        if (!$user->userID) {
            return 0;
        }

        $sql = "SELECT  COUNT(*) AS count
                FROM    wcf" . WCF_N . "_user_invite
                WHERE   inviterID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$user->userID]);

        return $statement->fetchColumn();
    }
}
