<?php
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
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
require_once dirname(__FILE__).'/behaviors/CMessageBehavior.php';

/**
 * @backup users
 *
 * @backup config
 */
class testLanguage extends CWebTest {

	private const INFO = 'You are not able to choose some of the languages, because'.
		' locales for them are not installed on the web server.';
	private const INFO_RUS = 'Вы не можете выбрать некоторые языки, т.к. локали для'.
		' них не установлены на вашем веб-сервере.';
	private const WARNING_TITLE = 'You are not logged in';
	private const WARNING_TITLE_RUS = 'Вы не выполнили вход';

	/**
	 * Attach MessageBehavior to the test.
	 *
	 * @return array
	 */
	public function getBehaviors() {
		return [CMessageBehavior::class];
	}

	public static function getGuiData() {
		return [
			[
				[
					'field' => [
						'Default language' => 'Russian (ru_RU)'
					],
					'message' => 'Configuration updated',
					'page_title' => 'Настройка веб-интерфейса',
					'body_lang' => 'ru',
					'defaultdb_lang' => 'ru_RU',
					'info' => self::INFO_RUS,
					'login_info' => [
							'name' => 'Имя пользователя',
							'password' => 'Пароль'
					]
				]
			],
			[
				[
					'field' => [
						'Язык по умолчанию' => 'Английский (en_GB)'
					],
					'message' => 'Настройки обновлены',
					'page_title' => 'Configuration of GUI',
					'body_lang' => 'en',
					'defaultdb_lang' => 'en_GB',
					'info' => self::INFO,
					'login_info' => [
							'name' => 'Username',
							'password' => 'Password'
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getGuiData
	 */
	public function testLanguage_Gui($data) {
		$this->page->userLogin('Admin', 'zabbix');
		$this->page->open('zabbix.php?action=gui.edit');

		// Change default language.
		$form = $this->query('xpath://form[@aria-labeledby="page-title-general"]')->one()->asForm();
		$form->fill($data['field']);
		$form->submit();
		$this->page->waitUntilReady();
		$this->checkLanguage($data['message'], $data['page_title'], $data['body_lang'], $data['defaultdb_lang']);

		// Red info icon check.
		$this->query('xpath://span[@class="icon-info status-red"]')->one()->click();
		$this->assertEquals($data['info'], $this->query('class:red')->one()->getText());

		// After logout, warning message and login menu has system language.
		$this->page->logout();
		$this->page->refresh();
		$warning = ($data['body_lang'] == 'ru') ? self::WARNING_TITLE_RUS : self::WARNING_TITLE;
		$this->assertMessage(TEST_BAD, $warning);
		$this->query('id:login')->one()->click();
		$this->assertEquals($data['body_lang'], $this->query('xpath://body')->one()->getAttribute('lang'));

		foreach ($data['login_info'] as $key => $value) {
			$this->assertEquals($value, $this->query('xpath://label[@for="'.$key.'"]')->one()->getText());
		}
	}

	public static function getUserData() {
		return [
			[
				[
					'field' => [
						'Language' => 'Russian (ru_RU)'
					],
					'message' => 'User updated',
					'page_title' => 'Панель',
					'body_lang' => 'ru',
					'menu_lang' => 'en',
					'userdb_lang' => 'ru_RU',
					'defaultdb_lang' => 'en_GB',
					'info' => self::INFO
				]
			],
			[
				[
					'field' => [
						'Язык' => 'Английский (en_GB)'
					],
					'message' => 'Пользователь обновлен',
					'page_title' => 'Dashboard',
					'body_lang' => 'en',
					'menu_lang' => 'en',
					'userdb_lang' => 'en_GB',
					'defaultdb_lang' => 'en_GB',
					'info' => self::INFO_RUS
				]
			],
			[
				[
					'field' => [
						'Language' => 'System default'
					],
					'message' => 'User updated',
					'page_title' => 'Dashboard',
					'body_lang' => 'en',
					'menu_lang' => 'en',
					'userdb_lang' => 'default',
					'defaultdb_lang' => 'en_GB',
					'info' => self::INFO
				]
			]
		];
	}

	/**
	 * @dataProvider getUserData
	 */
	public function testLanguage_User($data) {
		$this->page->userLogin('user-zabbix', 'zabbix');
		$this->page->open('zabbix.php?action=userprofile.edit');
		$form = $this->query('name:user_form')->one()->asForm();

		// Red info icon check.
		$this->query('xpath://span[@class="icon-info status-red"]')->one()->click();
		$this->assertEquals($data['info'], $this->query('class:red')->one()->getText());

		// Change user language to different from System.
		$form->fill($data['field']);
		$form->submit();
		$this->page->waitUntilReady();
		$this->checkLanguage($data['message'], $data['page_title'], $data['body_lang'], $data['defaultdb_lang']);
		$this->assertEquals($data['userdb_lang'], CDBHelper::getValue('SELECT lang FROM users WHERE username='.zbx_dbstr('user-zabbix')));


		// After logout, login menu has system language.
		$this->page->logout();
		$this->page->refresh();
		$this->assertMessage(TEST_BAD, self::WARNING_TITLE);
		$this->query('button:Login')->one()->click();
		$this->assertEquals($data['menu_lang'], $this->query('xpath://body')->one()->getAttribute('lang'));
		$this->assertEquals('Username', $this->query('xpath://label[@for="name"]')->one()->getText());
		$this->assertEquals('Password', $this->query('xpath://label[@for="password"]')->one()->getText());
	}

	public static function getCreateUserData() {
		return [
			[
				[
					'fields' => [
						'Username' => 'testRU',
						'Groups' => [
							'Selenium user group'
						],
						'Password' => 'test',
						'Password (once again)' => 'test',
						'Language' => 'Russian (ru_RU)'
					],
					'page_title' => 'Панель',
					'body_lang' => 'ru',
					'userdb_lang' => 'ru_RU',
					'defaultdb_lang' => 'en_GB'
				]
			],
			[
				[
					'fields' => [
						'Username' => 'testDEF',
						'Groups' => [
							'Selenium user group'
						],
						'Password' => 'test',
						'Password (once again)' => 'test',
						'Language' => 'System default'
					],
					'page_title' => 'Dashboard',
					'body_lang' => 'en',
					'userdb_lang' => 'default',
					'defaultdb_lang' => 'en_GB'
				]
			],
			[
				[
					'fields' => [
						'Username' => 'testENG',
						'Groups' => [
							'Selenium user group'
						],
						'Password' => 'test',
						'Password (once again)' => 'test',
						'Language' => 'English (en_GB)'
					],
					'page_title' => 'Dashboard',
					'body_lang' => 'en',
					'userdb_lang' => 'en_GB',
					'defaultdb_lang' => 'en_GB'
				]
			]
		];
	}

	/**
	 * @dataProvider getCreateUserData
	 */
	public function testLanguage_CreateUser($data) {
		$this->page->userLogin('Admin', 'zabbix');
		$this->page->open('zabbix.php?action=user.edit');
		$form = $this->query('name:user_form')->asForm()->waitUntilVisible()->one();
		$form->fill($data['fields']);
		$form->selectTab('Permissions');
		$form->fill(['Role' => 'Super admin role']);
		$form->submit();
		$this->assertMessage(TEST_GOOD, 'User added');
		$this->page->logout();
		$this->page->userLogin($data['fields']['Username'], $data['fields']['Password']);
		$this->page->assertTitle($data['page_title']);
		$this->assertEquals($data['body_lang'], $this->query('xpath://body')->one()->getAttribute('lang'));
		$this->assertEquals($data['userdb_lang'], CDBHelper::getValue('SELECT lang FROM users WHERE username='.
				zbx_dbstr($data['fields']['Username'])));
		$this->assertEquals($data['defaultdb_lang'], CDBHelper::getValue('SELECT default_lang FROM config'));
	}

	private function checkLanguage($message, $page_title, $body_lang, $defaultdb_lang) {
		$this->assertMessage(TEST_GOOD, $message);
		$this->page->assertTitle($page_title);
		$this->assertEquals($body_lang, $this->query('xpath://body')->one()->getAttribute('lang'));
		$this->assertEquals($defaultdb_lang, CDBHelper::getValue('SELECT default_lang FROM config'));
	}
}
