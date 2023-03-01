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

namespace wcf\form;

use wcf\data\user\invite\Invite;
use wcf\data\user\invite\InviteAction;
use wcf\data\user\UserEditor;
use wcf\system\cache\builder\InviteTopMembersBoxCacheBuilder;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\UserMailbox;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\UserMenu;
use wcf\system\request\LinkHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the invitation add form.
 */
class InviteAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.profile.canInvite'];

    /**
     * invite data
     */
    public $emailField = '';

    public $emails = [];

    public $code = '';

    public $description = '';

    public $method = 'email';

    public $codeOption = '';

    public $subject = '';

    public $message = '';

    /**
     * affected user
     */
    public $user;

    /**
     * @var int
     */
    private $unusedCode = 0;

    /**
     * @var int
     */
    private $emailCode = 0;

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\database\exception\DatabaseQueryException
     * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
     */
    public function readParameters(): void
    {
        parent::readParameters();

        // set user
        $this->user = WCF::getUser();

        if (INVITE_CODE_LENGTH) {
            $this->code = Invite::getNewCode();
        } else {
            $this->code = PasswordUtil::getRandomPassword();
        }

        // Template 0 = display, 1 = locked
        if (!INVITE_CODE_LIMIT_UNUSED) {
            $this->unusedCode = 0;
        } elseif (INVITE_CODE_LIMIT_UNUSED > Invite::checkUnusedCodeExist()) {
            $this->unusedCode = 0;
        } else {
            $this->unusedCode = 1;
        }

        if (!INVITE_EMAIL_LIMIT) {
            $this->emailCode = 1;
        } else {
            $this->emailCode = 0;
        }

        $this->method = 'copy';

        switch (INVITE_CODE_OPTION) {
            case 'may':
                $this->description = WCF::getLanguage()->getDynamicVariable('wcf.user.invite.description.may');
                break;
            case 'maynot':
                $this->code = '';
                $this->description = WCF::getLanguage()->getDynamicVariable('wcf.user.invite.description.maynot');
                $this->method = 'email';
                break;
            case 'must':
                $this->description = WCF::getLanguage()->getDynamicVariable('wcf.user.invite.description.must');
                break;
        }

        $this->subject = WCF::getLanguage()->getDynamicVariable('wcf.user.invite.subject.default');
        $this->message = WCF::getLanguage()->getDynamicVariable('wcf.user.invite.message.default', ['code' => $this->code]);
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters(): void
    {
        parent::readFormParameters();

        if (isset($_POST['code'])) {
            $this->code = $_POST['code'];
        }

        if (isset($_POST['emailField'])) {
            $this->emailField = StringUtil::trim($_POST['emailField']);
        }

        if (isset($_POST['message'])) {
            $this->message = StringUtil::trim($_POST['message']);
        }

        if (isset($_POST['subject'])) {
            $this->subject = StringUtil::trim($_POST['subject']);
        }

        if ((INVITE_CODE_OPTION !== 'maynot') && isset($_POST['method'])) {
            $this->method = $_POST['method'];
        }
    }

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\exception\UserInputException
     * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
     */
    public function validate(): void
    {
        parent::validate();

        // check email
        if ($this->method === 'email') {
            // emailField not empty
            if (empty($this->emailField)) {
                throw new UserInputException('emailField');
            }

            // emails must be correct and are limited
            $emails = \array_unique(ArrayUtil::trim(\explode("\n", $this->emailField)));

            if (\count($emails) > INVITE_EMAIL_LIMIT) {
                throw new UserInputException('emailField', 'tooMany');
            }

            foreach ($emails as $email) {
                if (!UserUtil::isValidEmail($email)) {
                    WCF::getTPL()->assign([
                        'invalidEmail' => $email,
                    ]);
                    throw new UserInputException('emailField', 'notValid');
                }

                // check email (spam)
                $days = Invite::checkEmailBlocked($email);
                if ($days) {
                    WCF::getTPL()->assign([
                        'blockedEmail' => $email,
                        'days' => $days,
                    ]);
                    throw new UserInputException('emailField', 'blockedEmail');
                }
            }

            $this->emailField = \implode(', ', $emails);

            // subject not empty and < 256 chars
            if (empty($this->subject)) {
                throw new UserInputException('subject');
            }
            if (\mb_strlen($this->subject) > 255) {
                throw new UserInputException('subject', 'tooLong');
            }

            // message not empty
            if (empty($this->message)) {
                throw new UserInputException('message');
            }

            // either subject or message must contain the code, if required
            if (
                INVITE_CODE_OPTION === 'must'
                && \mb_stripos($this->subject, $this->code) === false
                && \mb_stripos($this->message, $this->code) === false
            ) {
                throw new UserInputException('message', 'noCode');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables(): void
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'code' => $this->code,
            'unusedCode' => $this->unusedCode,
            'emailCode' => $this->emailCode,
            'description' => $this->description,
            'emailField' => $this->emailField,
            'message' => $this->message,
            'method' => $this->method,
            'subject' => $this->subject,
            'user' => $this->user,
        ]);
    }

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\exception\PermissionDeniedException
     * @throws \wcf\system\exception\SystemException
     */
    public function show(): void
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.invite.add');

        parent::show();
    }

    /**
     * @inheritDoc
     *
     * @throws \wcf\system\exception\SystemException
     */
    public function save(): void
    {
        parent::save();

        $data = [
            'code' => $this->code,
            'codeExpires' => INVITE_CODE_EXPIRE ? TIME_NOW + 86400 * INVITE_CODE_EXPIRE : 0,
            'emails' => ($this->method === 'email' ? $this->emailField : ''),
            'inviterID' => $this->user->userID,
            'inviterName' => $this->user->username,
            'message' => ($this->method === 'email' ? $this->message : ''),
            'subject' => ($this->method === 'email' ? $this->subject : ''),
            'time' => TIME_NOW,
            'additionalData' => '',
        ];

        $action = new InviteAction([], 'create', ['data' => $data]);
        $returnValues = $action->executeAction();
        $inviteID = $returnValues['returnValues']->inviteID;

        // update user count
        $editor = new UserEditor($this->user);
        $editor->updateCounters([
            'invites' => 1,
        ]);

        // send emails and store them
        if ($this->method === 'email') {
            $language = WCF::getLanguage();
            $receivers = \explode(', ', $this->emailField);
            $sender = WCF::getUser();

            // build message data
            $messageData = [
                'message' => $this->message,
                'username' => $sender->username,
            ];

            foreach ($receivers as $receiver) {
                // build mail
                $email = new Email();
                $email->addRecipient(new Mailbox($receiver, null, $language));
                $email->setSubject($sender->getLanguage()->getDynamicVariable('wcf.user.invite.email.subject', [
                    'username' => $sender->username,
                    'subject' => $this->subject,
                ]));
                $email->setBody(new MimePartFacade([
                    new RecipientAwareTextMimePart(
                        'text/html',
                        'invite_email',
                        'wcf',
                        $messageData
                    ),

                    new RecipientAwareTextMimePart(
                        'text/plain',
                        'invite_email',
                        'wcf',
                        $messageData
                    ),
                ]));

                // add reply-to tag
                $email->setReplyTo(new UserMailbox($sender));

                // send mail
                $email->send();
            }

            // block emails
            WCF::getDB()->beginTransaction();
            $sql = "INSERT INTO wcf1_user_invite_email (email, time, inviteID)
                    VALUES (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($receivers as $receiver) {
                if (\mb_strlen($receiver) < 256) {
                    $statement->execute([$receiver, TIME_NOW - 1, $inviteID]);
                }
            }
            WCF::getDB()->commitTransaction();
        }

        // recent activity and points (always)
        if (MODULE_INVITE_ACTIVITY) {
            UserActivityEventHandler::getInstance()->fireEvent(
                'com.uz.wcf.invitation.recentActivityEvent.submit',
                $inviteID
            );
        }

        UserActivityPointHandler::getInstance()->fireEvent(
            'com.uz.wcf.invitation.activityPointEvent.submit',
            $inviteID,
            $this->user->userID
        );

        // reset box cache
        InviteTopMembersBoxCacheBuilder::getInstance()->reset();

        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);

        // forward to list page
        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink('InviteListUser'),
            WCF::getLanguage()->get('wcf.user.invite.add.success'),
            1
        );

        exit;
    }
}
