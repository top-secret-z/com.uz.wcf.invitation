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

use Exception;
use wcf\data\DatabaseObject;
use wcf\data\user\invite\success\InviteSuccessList;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * @property int $inviteID
 * @property string $code
 * @property int $codeExpires
 * @property string $emails
 * @property int $inviterID
 * @property string $inviterName
 * @property string $message
 * @property string $subject
 * @property int $successCount
 * @property int $time
 * @property string $additionalData
 */
class Invite extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'user_invite';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'inviteID';

    /**
     * Returns true, if the user can use invitations (and see it in activities)
     */
    public function canSee()
    {
        return WCF::getSession()->getPermission('user.profile.canInvite');
    }

    /**
     * Returns 0 if the code does not exist or has expired
     *
     * @throws \wcf\system\database\exception\DatabaseQueryException
     * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
     */
    public static function checkCodeExist(string $code): int
    {
        // get code and check expiration
        $sql = "SELECT *
                FROM wcf1_user_invite
                WHERE code = ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$code]);
        $row = $statement->fetchArray();

        if (!$row) {
            return 0;
        }

        if (INVITE_CODE_EXPIRE && $row['time'] + INVITE_CODE_EXPIRE * 86400 < TIME_NOW) {
            return 0;
        }

        // code exists, check usage
        $sql = "SELECT used
                FROM wcf1_user_invite_code
                WHERE code = ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$code]);
        $used = $statement->fetchColumn();

        if (!$used || !INVITE_CODE_LIMIT || (INVITE_CODE_LIMIT && $used < INVITE_CODE_LIMIT)) {
            return 1;
        }

        return 0;
    }

    /**
     * Returns invite by code
     *
     * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
     */
    public static function getInviteByCode(string $code): self
    {
        $sql = "SELECT *
                FROM wcf1_user_invite
                WHERE code = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$code]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Returns invite by email (latest inviter)
     *
     * @throws \wcf\system\database\exception\DatabaseQueryException
     * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
     */
    public static function getInviteByEmail(string $email): self
    {
        $sql = "SELECT inviteID
                FROM wcf1_user_invite_email
                WHERE email = ?
                ORDER BY time DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$email]);
        $row = $statement->fetchArray();

        if (!$row) {
            return new self(null, []);
        }

        $sql = "SELECT *
                FROM wcf1_user_invite
                WHERE inviteID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$row['inviteID']]);
        $row = $statement->fetchArray();

        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Return 0 if email is not used within time otherwise return days
     *
     * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
     */
    public static function checkEmailBlocked(string $email)
    {
        $sql = "SELECT time
                FROM wcf1_user_invite_email
                WHERE email = ?
                ORDER BY time DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$email]);

        if ($row = $statement->fetchArray()) {
            if ($row['time'] + INVITE_EMAIL_TIME * 86400 < TIME_NOW) {
                return 0;
            }

            return \ceil(($row['time'] + INVITE_EMAIL_TIME * 86400 - TIME_NOW) / 86400);
        }

        return 0;
    }

    /**
     * returns names of users who registered after invitation
     *
     * @throws \wcf\system\exception\SystemException
     */
    public function getUsernames(): string
    {
        $nameList = new InviteSuccessList();
        $nameList->getConditionBuilder()->add('inviteID = ?', [$this->inviteID]);
        $nameList->sqlOrderBy = 'time DESC';
        $nameList->readObjects();

        $names = [];

        /** @var \wcf\data\user\invite\success\InviteSuccess $name */
        foreach ($nameList->getObjects() as $name) {
            if ($name->userID) {
                $user = UserRuntimeCache::getInstance()->getObject($name->userID);

                if (null !== $user) {
                    $names[] = '<a class="userLink" href="' . $user->getLink(
                    ) . '" data-user-id="' . $name->userID . '">' . $name->username . '</a>';
                }
            } else {
                $names[] = $name->username;
            }
        }

        return \implode(', ', $names);
    }

    /**
     * returns formatted message
     */
    public function getExcerpt(int $maxLength = 255): string
    {
        return StringUtil::truncateHTML($this->message, $maxLength);
    }

    /**
     * Returns 0 if unused codes does not exist
     *
     * @throws \wcf\system\database\exception\DatabaseQueryException
     */
    public static function checkUnusedCodeExist(): int
    {
        // code unused limit exists, check unused usage
        $sCount = 0;
        $sql = "SELECT successCount
                FROM wcf1_user_invite
                WHERE inviterID = ?
                ORDER BY successCount";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([WCF::getUser()->userID]);
        while ($key = $statement->fetchArray()) {
            if ($key["successCount"] > 0) {
                $sCount++;
            }
        }

        return $sCount;
    }

    /**
     * Returns a code xxxx-xxxx-xxxx-xxxx
     *
     * @throws \wcf\system\database\exception\DatabaseQueryExecutionException
     * @throws Exception
     */
    public static function getNewCode(): string
    {
        while (1) {
            $code = \strtoupper(\implode('-', \str_split(\bin2hex(\random_bytes(8)), 4)));

            $sql = "SELECT *
                    FROM wcf1_user_invite
                    WHERE code = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$code]);
            $row = $statement->fetchArray();

            if (!$row) {
                return $code;
            }
        }
    }
}
