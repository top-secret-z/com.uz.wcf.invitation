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

namespace wcf\system\bulk\processing\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\UserList;
use wcf\system\cache\builder\InviteTopMembersBoxCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Bulk processing action implementation for deleting invitations.
 */
class InviteDeleteBulkProcessingAction extends AbstractUserBulkProcessingAction
{
    /**
     * @inheritDoc
     */
    public function executeAction(DatabaseObjectList $objectList)
    {
        if (!($objectList instanceof UserList)) {
            return;
        }

        $users = $objectList->getObjects();

        if (!empty($users)) {
            $userIDs = $inviteToUser = $successToUser = [];
            foreach ($users as $user) {
                $userIDs[] = $user->userID;

                if (!isset($inviteToUser[$user->userID])) {
                    $inviteToUser[$user->userID] = 0;
                }
                $inviteToUser[$user->userID] += $user->invites;

                if (!isset($successToUser[$user->userID])) {
                    $successToUser[$user->userID] = 0;
                }
                $successToUser[$user->userID] += $user->inviteSuccess;
            }

            // remove points
            UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.invitation.activityPointEvent.submit', $inviteToUser);
            UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.invitation.activityPointEvent.success', $successToUser);

            // remove user counts
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("userID IN (?)", [$userIDs]);

            $sql = "UPDATE    wcf" . WCF_N . "_user
                    SET invites = 0, inviteSuccess = 0
                " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());

            // reset invitation box cache
            InviteTopMembersBoxCacheBuilder::getInstance()->reset();

            // remove invites and success
            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("inviterID IN (?)", [$userIDs]);
            $sql = "DELETE FROM    wcf" . WCF_N . "_user_invite
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());

            $conditions = new PreparedStatementConditionBuilder();
            $conditions->add("inviterID IN (?)", [$userIDs]);
            $conditions->add("inviteID IS NULL");
            $sql = "DELETE FROM    wcf" . WCF_N . "_user_invite_success
                    " . $conditions;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditions->getParameters());
        }
    }

    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        $userList = parent::getObjectList();

        // only users with invites
        $userList->getConditionBuilder()->add("(user_table.invites > ? OR user_table.inviteSuccess > ?)", [0, 0]);

        return $userList;
    }
}
