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

use wcf\data\DatabaseObject;
use wcf\data\user\invite\success\InviteSuccessList;
use wcf\data\user\User;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an invitation.
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
     */
    public static function checkCodeExist($code)
    {
        // get code and check expiration
        $sql = "SELECT    *
                FROM    wcf" . WCF_N . "_user_invite
                WHERE    code = ?";
        $statement = WCF::getDB()->prepareStatement($sql, 1);
        $statement->execute([$code]);
        $row = $statement->fetchArray();

        if (!$row) {
            return 0;
        }
        if (INVITE_CODE_EXPIRE && $row['time'] + INVITE_CODE_EXPIRE * 86400 < TIME_NOW) {
            return 0;
        }

        // code exists, check usage
        $sql = "SELECT        used
                FROM        wcf" . WCF_N . "_user_invite_code
                WHERE        code = ?";
        $statement = WCF::getDB()->prepareStatement($sql, 1);
        $statement->execute([$code]);
        $used = $statement->fetchColumn();

        if (!$used || !INVITE_CODE_LIMIT) {
            return 1;
        }
        if (INVITE_CODE_LIMIT && $used < INVITE_CODE_LIMIT) {
            return 1;
        }

        return 0;
    }

    /**
     * Returns invite by code
     */
    public static function getInviteByCode($code)
    {
        $sql = "SELECT        *
                FROM        wcf" . WCF_N . "_user_invite
                WHERE        code = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$code]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Returns invite by email (latest inviter)
     */
    public static function getInviteByEmail($email)
    {
        $sql = "SELECT        inviteID
                FROM        wcf" . WCF_N . "_user_invite_email
                WHERE        email = ?
                ORDER BY    time DESC";
        $statement = WCF::getDB()->prepareStatement($sql, 1);
        $statement->execute([$email]);
        $row = $statement->fetchArray();
        if (!$row) {
            return new self(null, []);
        }

        $sql = "SELECT        *
                FROM        wcf" . WCF_N . "_user_invite
                WHERE        inviteID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$row['inviteID']]);
        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * Return 0 if email is not used within time otherwise return days
     */
    public static function checkEmailBlocked($email)
    {
        $sql = "SELECT        time
                FROM        wcf" . WCF_N . "_user_invite_email
                WHERE        email = ?
                ORDER BY    time DESC";
        $statement = WCF::getDB()->prepareStatement($sql, 1);
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
     */
    public function getUsernames()
    {
        $nameList = new InviteSuccessList();
        $nameList->getConditionBuilder()->add('inviteID = ?', [$this->inviteID]);
        $nameList->sqlOrderBy = 'time DESC';
        $nameList->readObjects();

        $names = [];
        foreach ($nameList->getObjects() as $name) {
            if ($name->userID) {
                $user = UserRuntimeCache::getInstance()->getObject($name->userID);
                $names[] = '<a class="userLink" href="' . $user->getLink() . '" data-user-id="' . $name->userID . '">' . $name->username . '</a>';
            } else {
                $names[] = $name->username;
            }
        }

        return \implode(', ', $names);
    }

    /**
     * returns formatted message
     */
    public function getExcerpt($maxLength = 255)
    {
        return StringUtil::truncateHTML($this->message, $maxLength);
    }

    /**
     * Returns a code xxxx-xxxx-xxxx-xxxx
     */
    public static function getNewCode()
    {
        while (1) {
            $code = \strtoupper(\implode('-', \str_split(\bin2hex(\random_bytes(8)), 4)));
            $sql = "SELECT    *
                    FROM    wcf" . WCF_N . "_user_invite
                    WHERE    code = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$code]);
            $row = $statement->fetchArray();
            if (!$row) {
                return $code;
            }
        }
    }
}
