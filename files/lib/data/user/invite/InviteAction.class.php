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

namespace wcf\data\user\invite;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IDeleteAction;
use wcf\data\user\invite\success\InviteSuccessAction;
use wcf\data\user\invite\success\InviteSuccessList;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\cache\builder\InviteTopMembersBoxCacheBuilder;
use wcf\system\cache\builder\InviteTopSuccessMembersBoxCacheBuilder;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;

/**
 * Executes invitation-related actions.
 */
class InviteAction extends AbstractDatabaseObjectAction implements IDeleteAction
{
    /**
     * @inheritDoc
     */
    protected $className = InviteEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['user.profile.canInvite'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['user.profile.canInvite', 'admin.user.canManageInvite'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['user.profile.canInvite'];

    /**
     * @inheritDoc
     */
    protected $requireACP = [];

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getGroupedUserList'];

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $itemsToUserSubmit = $itemsToUserSuccess = [];
        foreach ($this->objectIDs as $id) {
            $invite = new Invite($id);
            if (!$invite->inviteID) {
                continue;
            }

            $user = new User($invite->inviterID);
            if (!$user->userID) {
                continue;
            }

            // delete success first with action for other purposes
            $successList = new InviteSuccessList();
            $successList->getConditionBuilder()->add("user_invite_success.inviteID = ?", [$invite->inviteID]);
            $successList->readObjectIDs();
            $action = new InviteSuccessAction($successList->getObjectIDs(), 'delete');
            $action->executeAction();

            // update user counts
            $editor = new UserEditor($user);
            $editor->updateCounters([
                'invites' => -1,
                'inviteSuccess' => -1 * $invite->success,
            ]);

            // store points
            if (!isset($itemsToUserSubmit[$invite->inviterID])) {
                $itemsToUserSubmit[$invite->inviterID] = 0;
            }
            $itemsToUserSubmit[$invite->inviterID]++;

            if (!isset($itemsToUserSuccess[$invite->inviterID])) {
                $itemsToUserSuccess[$invite->inviterID] = 0;
            }
            $itemsToUserSuccess[$invite->inviterID] += $invite->successCount;
        }

        // remove activity event
        UserActivityEventHandler::getInstance()->removeEvents('com.uz.wcf.invitation.recentActivityEvent.submit', [$invite->inviteID]);

        // remove points
        if (\count($itemsToUserSubmit)) {
            UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.invitation.activityPointEvent.submit', $itemsToUserSubmit);
        }
        if (\count($itemsToUserSuccess)) {
            UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.invitation.activityPointEvent.success', $itemsToUserSuccess);
        }

        // update cache
        InviteTopMembersBoxCacheBuilder::getInstance()->reset();
        InviteTopSuccessMembersBoxCacheBuilder::getInstance()->reset();

        parent::delete();
    }

    /**
     * @inheritDoc
     */
    public function validateUpdate()
    {
    }
}
