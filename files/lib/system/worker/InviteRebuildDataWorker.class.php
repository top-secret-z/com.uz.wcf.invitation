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

namespace wcf\system\worker;

use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Notification event for invites.
 */
class InviteRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = UserList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\exception\SystemException
     */
    protected function initObjectList(): void
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'user_table.userID';
    }

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\exception\SystemException
     */
    public function execute(): void
    {
        parent::execute();

        if (!$this->loopCount) {
            // reset activity points
            UserActivityPointHandler::getInstance()->reset(
                'com.uz.wcf.invitation.activityPointEvent.submit'
            );

            UserActivityPointHandler::getInstance()->reset(
                'com.uz.wcf.invitation.activityPointEvent.success'
            );

            // reset invite counts in user
            $sql = "UPDATE wcf1_user
                    SET invites = ?, inviteSuccess = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([0, 0]);
        }

        if (!\count($this->objectList)) {
            return;
        }

        $inviteToUser = $successToUser = [];

        /** @var \wcf\data\user\User $user */
        foreach ($this->objectList as $user) {
            $sql = "SELECT COUNT(*) AS counter
                    FROM wcf1_user_invite
                    WHERE inviterID = ?";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute([$user->userID]);
            $invite = $statement->fetchColumn();

            $sql = "SELECT COUNT(*) AS counter
                    FROM wcf1_user_invite_success
                    WHERE inviterID = ?";
            $statement = WCF::getDB()->prepare($sql, 1);
            $statement->execute([$user->userID]);
            $success = $statement->fetchColumn();

            if ($invite || $success) {
                // update user
                (new UserEditor($user))->updateCounters([
                    'invites' => $invite,
                    'inviteSuccess' => $success,
                ]);

                // update point arrays
                if ($invite) {
                    $inviteToUser[$user->userID] = $invite;
                }

                if ($success) {
                    $successToUser[$user->userID] = $success;
                }
            }
        }

        // update activity points
        if (\count($inviteToUser)) {
            UserActivityPointHandler::getInstance()->fireEvents(
                'com.uz.wcf.invitation.activityPointEvent.submit',
                $inviteToUser
            );
        }

        if (\count($successToUser)) {
            UserActivityPointHandler::getInstance()->fireEvents(
                'com.uz.wcf.invitation.activityPointEvent.success',
                $successToUser
            );
        }
    }
}
