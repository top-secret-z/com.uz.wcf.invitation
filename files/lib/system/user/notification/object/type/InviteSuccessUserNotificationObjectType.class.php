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
namespace wcf\system\user\notification\object\type;

use wcf\data\user\invite\success\InviteSuccess;
use wcf\data\user\invite\success\InviteSuccessList;
use wcf\data\user\User;
use wcf\system\user\notification\object\InviteSuccessUserNotificationObject;

/**
 * Represents a user as a notification object type.
 */
class InviteSuccessUserNotificationObjectType extends AbstractUserNotificationObjectType
{
    /**
     * @inheritDoc
     */
    protected static $decoratorClassName = InviteSuccessUserNotificationObject::class;

    /**
     * @inheritDoc
     */
    protected static $objectClassName = InviteSuccess::class;

    /**
     * @inheritDoc
     */
    protected static $objectListClassName = InviteSuccessList::class;
}
