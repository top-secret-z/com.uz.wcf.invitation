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
 */use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\WCF;

/**
 * Extend code length
 */
$tables = [
    // update code and add additionalData in invite
    PartialDatabaseTable::create('wcf1_user_invite')
        ->columns([
            VarcharDatabaseTableColumn::create('code')
                ->length(100)
                ->notNull()
                ->defaultValue(''),
            MediumtextDatabaseTableColumn::create('additionalData'),
        ]),

    // update code in invite_code
    PartialDatabaseTable::create('wcf1_user_invite_code')
        ->columns([
            VarcharDatabaseTableColumn::create('code')
                ->length(100)
                ->notNull()
                ->defaultValue(''),
        ])
        ->indices([
            DatabaseTableIndex::create('code')
                ->type(DatabaseTableIndex::UNIQUE_TYPE)
                ->columns(['code']),
        ]),
];

(new DatabaseTableChangeProcessor(
    $this->installation->getPackage(),
    $tables,
    WCF::getDB()->getEditor()
)
)->process();
