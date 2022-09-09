<?php
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\column\VarcharDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\WCF;

/**
 * Extend code length
 *
 * @author		2021-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.invitation
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
		WCF::getDB()->getEditor())
)->process();
