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

namespace wcf\system\user\notification\object;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\invite\success\InviteSuccess;
use wcf\system\WCF;

/**
 * Represents an invitation as a notification object.
 */
class InviteSuccessUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = InviteSuccess::class;

    /**
     * returns the title
     */
    public function getTitle(): string
    {
        return '';
    }

    /**
     * returns the URL
     */
    public function getURL(): string
    {
        return '';
    }

    /**
     * returns the userID
     */
    public function getAuthorID(): int
    {
        //return $this->userID;
        return WCF::getUser()->userID;
    }
}
