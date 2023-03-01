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

use wcf\system\database\table\column\IntDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\NotNullInt10DatabaseTableColumn;
use wcf\system\database\table\column\NotNullVarchar255DatabaseTableColumn;
use wcf\system\database\table\column\ObjectIdDatabaseTableColumn;
use wcf\system\database\table\column\TextDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTable;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;

return [
    PartialDatabaseTable::create('wcf1_user')
        ->columns([
            NotNullInt10DatabaseTableColumn::create('invites')
                ->defaultValue(0),
            NotNullInt10DatabaseTableColumn::create('inviteSuccess')
                ->defaultValue(0),
        ]),
    DatabaseTable::create('wcf1_user_invite')
        ->columns([
            ObjectIdDatabaseTableColumn::create('inviteID'),
            VarcharDatabaseTableColumn::create('code')
                ->length(100)
                ->notNull()
                ->defaultValue(''),
            NotNullInt10DatabaseTableColumn::create('codeExpires')
                ->defaultValue(0),
            TextDatabaseTableColumn::create('emails'),
            IntDatabaseTableColumn::create('inviterID')
                ->defaultValue(null),
            NotNullVarchar255DatabaseTableColumn::create('inviterName')
                ->defaultValue(''),
            TextDatabaseTableColumn::create('message'),
            NotNullVarchar255DatabaseTableColumn::create('subject')
                ->defaultValue(''),
            NotNullInt10DatabaseTableColumn::create('successCount')
                ->defaultValue(0),
            NotNullInt10DatabaseTableColumn::create('time')
                ->defaultValue(0),
            MediumtextDatabaseTableColumn::create('additionalData'),
        ])
        ->indices([
            DatabaseTableIndex::create('inviterID')
                ->type(DatabaseTableIndex::DEFAULT_TYPE)
                ->columns(['inviterID']),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['inviterID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
        ]),
    DatabaseTable::create('wcf1_user_invite_code')
        ->columns([
            ObjectIdDatabaseTableColumn::create('codeID'),
            VarcharDatabaseTableColumn::create('code')
                ->length(100)
                ->notNull()
                ->defaultValue(''),
            NotNullInt10DatabaseTableColumn::create('used')
                ->defaultValue(0),
        ]),
    DatabaseTable::create('wcf1_user_invite_email')
        ->columns([
            ObjectIdDatabaseTableColumn::create('emailID'),
            NotNullVarchar255DatabaseTableColumn::create('email')
                ->defaultValue(''),
            NotNullInt10DatabaseTableColumn::create('inviteID')
                ->defaultValue(0),
            NotNullInt10DatabaseTableColumn::create('time')
                ->defaultValue(0),
        ]),
    DatabaseTable::create('wcf1_user_invite_success')
        ->columns([
            ObjectIdDatabaseTableColumn::create('successID'),
            IntDatabaseTableColumn::create('inviteID')
                ->length(10),
            IntDatabaseTableColumn::create('inviterID')
                ->length(10),
            NotNullVarchar255DatabaseTableColumn::create('inviterName')
                ->defaultValue(''),
            IntDatabaseTableColumn::create('userID')
                ->length(10),
            NotNullVarchar255DatabaseTableColumn::create('username')
                ->defaultValue(''),
            NotNullInt10DatabaseTableColumn::create('time')
                ->defaultValue(0),
        ])
        ->foreignKeys([
            DatabaseTableForeignKey::create()
                ->columns(['inviteID'])
                ->referencedTable('wcf1_user_invite')
                ->referencedColumns(['inviteID'])
                ->onDelete('CASCADE'),
            DatabaseTableForeignKey::create()
                ->columns(['inviterID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
            DatabaseTableForeignKey::create()
                ->columns(['userID'])
                ->referencedTable('wcf1_user')
                ->referencedColumns(['userID'])
                ->onDelete('SET NULL'),
        ]),
];
