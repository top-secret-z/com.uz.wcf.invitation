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
namespace wcf\system\event\listener;

use wcf\data\user\invite\Invite;
use wcf\data\user\invite\InviteEditor;
use wcf\data\user\invite\success\InviteSuccess;
use wcf\data\user\invite\success\InviteSuccessAction;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\cache\builder\InviteTopSuccessMembersBoxCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\notification\object\InviteSuccessUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles additions to register form.
 */
class InviteRegisterFormListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    protected $eventObj;

    /**
     * invite, code and option
     */
    public $invite;

    public $inviter;

    public $inviteByName = false;

    public $inviteCode = '';

    public $inviteCodeOption = '';

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        if (!MODULE_INVITE) {
            return;
        }

        $this->eventObj = $eventObj;

        $this->{$eventName}();
    }

    /**
     * Handles the readFormParameters event.
     */
    protected function readFormParameters()
    {
        if (isset($_POST['inviteCode'])) {
            WCF::getSession()->unregister('inviteCode');

            $this->inviteCode = StringUtil::trim($_POST['inviteCode']);
        }
    }

    /**
     * Handles the readParameters event.
     */
    protected function readParameters()
    {
        $this->inviteCodeOption = INVITE_CODE_OPTION;

        if (INVITE_CODE_OPTION != 'maynot') {
            if (!empty($_GET['inviteCode'])) {
                WCF::getSession()->register('inviteCode', \strip_tags(StringUtil::trim($_GET['inviteCode'])));
            }

            if (empty($this->inviteCode) && WCF::getSession()->getVar('inviteCode')) {
                $this->inviteCode = WCF::getSession()->getVar('inviteCode');
            }
        }
    }

    /**
     * Handles the assignVariables event.
     */
    protected function assignVariables()
    {
        WCF::getTPL()->assign([
            'inviteCode' => $this->inviteCode,
            'inviteCodeOption' => $this->inviteCodeOption,
        ]);
    }

    /**
     * Handles the validate event.
     */
    protected function validate()
    {
        // code is required
        if ($this->inviteCodeOption == 'must') {
            try {
                // empty
                if (empty($this->inviteCode)) {
                    throw new UserInputException('inviteCode', 'empty');
                }
                // must be correct
                if (!Invite::checkCodeExist($this->inviteCode)) {
                    throw new UserInputException('inviteCode', 'invalid');
                }
            } catch (UserInputException $e) {
                $this->eventObj->errorType[$e->getField()] = $e->getType();
            }
        }

        // must be correct if entered
        if ($this->inviteCodeOption == 'may') {
            if (!empty($this->inviteCode)) {
                try {
                    // either valid code or username
                    if (INVITE_CODE_USERNAME) {
                        if (!Invite::checkCodeExist($this->inviteCode)) {
                            $this->inviter = User::getUserByUsername($this->inviteCode);
                            if (!$this->inviter->userID) {
                                throw new UserInputException('inviteCode', 'invalidUsername');
                            }
                        }
                    } else {
                        if (!Invite::checkCodeExist($this->inviteCode)) {
                            throw new UserInputException('inviteCode', 'invalid');
                        }
                    }
                } catch (UserInputException $e) {
                    $this->eventObj->errorType[$e->getField()] = $e->getType();
                }
            }
        }
    }

    /**
     * Handles the save event.
     */
    protected function save()
    {
        // get invite to honour inviter
        if (!empty($this->inviteCode)) {
            $invite = Invite::getInviteByCode($this->inviteCode);
        } else {
            // get it from emails
            $invite = Invite::getInviteByEmail($this->eventObj->email);
        }
        if (!$invite->inviteID) {
            // last chance username
            if ($this->inviteCodeOption == 'may' && INVITE_CODE_USERNAME) {
                if (!$this->inviter) {
                    return;
                }
                $this->inviteByName = true;
            } else {
                return;
            }
        }

        if ($this->inviteByName === true) {
            // update user invite count
            $editor = new UserEditor($this->inviter);
            $editor->updateCounters([
                'inviteSuccess' => 1,
            ]);
        } else {
            // check inviting user
            $user = new User($invite->inviterID);
            if (!$user->userID) {
                return;
            }

            // successful invitation
            $this->invite = $invite;

            // update invite data
            $usernames = [];
            if (!empty($this->invite->usernames)) {
                $usernames = \explode(', ', $this->invite->usernames);
            }
            $usernames[] = $this->eventObj->username;
            $editor = new InviteEditor($this->invite);
            $editor->updateCounters([
                'successCount' => 1,
            ]);

            // update user invite count
            $editor = new UserEditor($user);
            $editor->updateCounters([
                'inviteSuccess' => 1,
            ]);

            // update code count
            if (!empty($this->inviteCode)) {
                $sql = "INSERT INTO    wcf" . WCF_N . "_user_invite_code
                        (code, used) VALUES    (?, ?)
                        ON DUPLICATE KEY UPDATE    used = used + 1";
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute([$this->inviteCode, 1]);
            }
        }
    }

    /**
     * Handles the saved event.
     */
    protected function saved()
    {
        if ($this->invite || $this->inviteByName) {
            // invite success
            $data = [
                'inviteID' => $this->inviteByName ? null : $this->invite->inviteID,
                'inviterID' => $this->inviteByName ? $this->inviter->userID : $this->invite->inviterID,
                'inviterName' => $this->inviteByName ? $this->inviter->username : $this->invite->inviterName,
                'userID' => WCF::getUser()->userID,
                'username' => WCF::getUser()->username,
                'time' => TIME_NOW,
            ];

            $action = new InviteSuccessAction([], 'create', ['data' => $data]);
            $returnValues = $action->executeAction();
            $successID = $returnValues['returnValues']->successID;

            // notification
            $inviterID = $this->inviteByName ? $this->inviter->userID : $this->invite->inviterID;
            UserNotificationHandler::getInstance()->fireEvent(
                'success',
                'com.uz.wcf.invitation',
                new InviteSuccessUserNotificationObject(new InviteSuccess($successID)),
                [$inviterID]
            );

            // points for successful invitation
            UserActivityPointHandler::getInstance()->fireEvent('com.uz.wcf.invitation.activityPointEvent.success', $successID, $inviterID);

            // update cache
            InviteTopSuccessMembersBoxCacheBuilder::getInstance()->reset();
        }
    }
}
