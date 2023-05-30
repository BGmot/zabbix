<?php
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../include/CWebTest.php';
require_once dirname(__FILE__).'/traits/TableTrait.php';
require_once dirname(__FILE__).'/behaviors/CMessageBehavior.php';

class testPageAdministrationGeneralRegexp extends CWebTest {

	use TableTrait;
	private $sqlHashRegexps = '';
	private $oldHashRegexps = '';
	private $sqlHashExpressions = '';
	private $oldHashExpressions = '';

	/**
	 * Calculates a hash for the data in regexps table.
	 *
	 * @param $conditions Optional WHERE clause for the data query.
	 */
	private function calculateHash($conditions = null) {
		$this->sqlHashRegexps =
			'SELECT * FROM regexps'.
			($conditions ? ' WHERE '.$conditions : '').
			' ORDER BY regexpid';
		$this->oldHashRegexps = CDBHelper::getHash($this->sqlHashRegexps);

		$this->sqlHashExpressions =
			'SELECT * FROM expressions'.
			($conditions ? ' WHERE '.$conditions : '').
			' ORDER BY expressionid';
		$this->oldHashExpressions = CDBHelper::getHash($this->sqlHashExpressions);
	}

	/**
	 * Verify that data in regexps table has not changed since last hash calculation.
	 */
	private function verifyHash() {
		$this->assertEquals($this->oldHashRegexps, CDBHelper::getHash($this->sqlHashRegexps));
		$this->assertEquals($this->oldHashExpressions, CDBHelper::getHash($this->sqlHashExpressions));
	}

	public static function allRegexps() {
		return CDBHelper::getDataProvider('SELECT regexpid FROM regexps');
	}

	/**
	 * Attach MessageBehavior to the test.
	 */
	public function getBehaviors() {
		return [
			'class' => CMessageBehavior::class
		];
	}

	/**
	 * Test the layout for the Regular expressions page.
	 */
	public function testPageAdministrationGeneralRegexp_Layout() {
		$this->page->login()->open('zabbix.php?action=regex.list');
		$this->page->assertTitle('Configuration of regular expressions');
		$this->page->assertHeader('Regular expressions');

		// Validate the dropdown menu under header.
		$popup_menu = $this->query('id:page-title-general')->asPopupButton()->one()->getMenu();
		$this->assertEquals([
			'GUI', 'Autoregistration', 'Housekeeping', 'Images', 'Icon mapping', 'Regular expressions', 'Macros',
			'Value mapping', 'Working time', 'Trigger severities', 'Trigger displaying options', 'Modules', 'Other'
		], $popup_menu->getItems()->asText());

		// Check if the New regular expression button is clickable.
		$this->assertTrue($this->query('button:New regular expression')->one()->isClickable());

		// Check the data table.
		$this->assertEquals(['', 'Name', 'Expressions'],
			$this->query('class:list-table')->asTable()->one()->getHeadersText());
		$name_list = [];
		foreach (CDBHelper::getColumn('SELECT name FROM regexps', 'name') as $name){
			$name_list[] = ["Name" => $name];
		}
		$this->assertTableHasData($name_list);
		$this->assertEquals('0 selected', $this->query('id:selected_count')->one()->getText());

		// Check the Delete button.
		$this->assertFalse($this->query('button:Delete')->one()->isEnabled());
	}

	/**
	 * Test pressing mass delete button but then cancelling.
	 */
	public function testPageAdministrationGeneralRegexp_MassDeleteAllCancel() {
		$this->calculateHash();

		$this->page->login()->open('zabbix.php?action=regex.list');
		$this->query('name:all-regexes')->one()->click();
		$this->query('button:Delete')->one()->click();
		$this->page->dismissAlert();
		$this->page->assertTitle('Configuration of regular expressions');

		// Make sure nothing has been deleted.
		$this->assertFalse($this->query('xpath://*[text()="Regular expression deleted"]')->exists());
		$this->assertFalse($this->query('xpath://*[text()="Regular expressions deleted"]')->exists());
		$this->verifyHash();

	}

	/**
	 * Test deleting all regexps one by one.
	 *
	 * @dataProvider allRegexps
	 * @backupOnce regexps
	 */
	public function testPageAdministrationGeneralRegexp_MassDelete($regexp) {
		$this->calculateHash('regexpid<>'.$regexp['regexpid']);

		// Delete a regexp.
		$this->page->login()->open('zabbix.php?action=regex.list');
		$this->query('id:regexids_'.$regexp['regexpid'])->one()->click();
		$this->query('button:Delete')->one()->click();
		$this->page->acceptAlert();

		// Check the result.
		$this->page->assertTitle('Configuration of regular expressions');
		$this->assertMessage(TEST_GOOD, 'Regular expression deleted');
		$this->assertEquals(0, CDBHelper::getCount('SELECT NULL FROM regexps WHERE regexpid='.$regexp['regexpid']));
		$this->verifyHash();
	}

	/**
	 * Test deleting all regexps at once.
	 *
	 * @backupOnce regexps
	 */
	public function testPageAdministrationGeneralRegexp_MassDeleteAll() {
		// Delete all regexps.
		$this->page->login()->open('zabbix.php?action=regex.list');
		$this->query('name:all-regexes')->one()->click();
		$this->query('button:Delete')->one()->click();
		$this->page->acceptAlert();

		// Check the result.
		$this->page->assertTitle('Configuration of regular expressions');
		$this->assertMessage(TEST_GOOD, 'Regular expressions deleted');
		$this->assertEquals(0, CDBHelper::getCount('SELECT NULL FROM regexps'));
	}
}
