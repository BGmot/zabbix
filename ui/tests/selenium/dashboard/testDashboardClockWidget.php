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


require_once dirname(__FILE__) . '/../../include/CWebTest.php';
require_once dirname(__FILE__).'/../../include/helpers/CDataHelper.php';
require_once dirname(__FILE__).'/../behaviors/CMessageBehavior.php';

/**
 * @backup widget, profiles
 * @dataSource ClockWidgets
 */

class testDashboardClockWidget extends CWebTest {

	/**
	 * Attach MessageBehavior to the test.
	 *
	 * @return array
	 */
	public function getBehaviors() {
		return ['class' => CMessageBehavior::class];
	}

	/**
	 * SQL query to get widget and widget_field tables to compare hash values, but without widget_fieldid
	 * because it can change.
	 */
	private $sql = 'SELECT wf.widgetid, wf.type, wf.name, wf.value_int, wf.value_str, wf.value_groupid, wf.value_hostid,'.
			' wf.value_itemid, wf.value_graphid, wf.value_sysmapid, w.widgetid, w.dashboardid, w.type, w.name, w.x, w.y, w.width, w.height'.
			' FROM widget_field wf'.
			' INNER JOIN widget w'.
			' ON w.widgetid=wf.widgetid'.
			' ORDER BY wf.widgetid, wf.name, wf.value_int, wf.value_str, wf.value_groupid, wf.value_hostid, wf.value_itemid, wf.value_graphid';

	/**
	 * Check clock widgets layout.
	 */
	public function testDashboardClockWidget_Layout() {
		$dashboardid = CDataHelper::get('ClockWidgets.dashboardids.Dashboard for creating clock widgets');
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid='.$dashboardid);
		$form = CDashboardElement::find()->one()->edit()->addWidget()->asForm();
		$dialog = COverlayDialogElement::find()->waitUntilReady()->one();
		$this->assertEquals('Add widget', $dialog->getTitle());
		$form->fill(['Type' => CFormElement::RELOADABLE_FILL('Clock')]);

		// Check "Name" field max length.
		$this->assertEquals('255', $form->query('id:name')->one()->getAttribute('maxlength'));

		// Check fields "Refresh interval" and "Time type" values.
		$dropdowns =[
			'Refresh interval' => ['Default (15 minutes)',  'No refresh', '10 seconds', '30 seconds', '1 minute', '2 minutes', '10 minutes', '15 minutes'],
			'Time type' => ['Local time', 'Server time', 'Host time']
		];

		foreach ($dropdowns as $field => $options) {
			$this->assertEquals($options, $form->getField($field)->asDropdown()->getOptions()->asText());
		}

		// Check that it's possible to select host items, when time type is "Host Time".
		$fields = ['Type', 'Name', 'Refresh interval', 'Time type'];

		foreach (['Local time', 'Server time', 'Host time'] as $type) {
			$form->fill(['Time type' => CFormElement::RELOADABLE_FILL($type)]);

			if ($type === 'Host time') {
				array_splice($fields, 4, 0, ['Item']);
				$form->checkValue(['Item' => '']);
				$form->isRequired('Item');
			}

			$this->assertEquals($fields, $form->getLabels()->filter(new CElementFilter(CElementFilter::VISIBLE))->asText());
		}

		// Check that it's possible to change the status of "Show header" checkbox.
		$form->checkValue(['id:show_header' => true]);

		// Check if Apply and Cancel button are clickable and there are two of them.
		$dialog->invalidate();
		$this->assertEquals(2, $dialog->getFooter()->query('button', ['Add', 'Cancel'])->all()
					->filter(new CElementFilter(CElementFilter::CLICKABLE))->count()
		);
	}

	/**
	 * Function checks specific scenario when Clock widget has "Time type" as "Host time"
	 * and name for widget itself isn't provided, after creating widget, host name should be displayed on widget as
	 * the widget name.
	 */
	public function testDashboardClockWidget_CheckClockWidgetsName() {
		$dashboardid = CDataHelper::get('ClockWidgets.dashboardids.Dashboard for creating clock widgets');
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid='.$dashboardid);
		$dashboard = CDashboardElement::find()->one();
		$form = $dashboard->getWidget('LayoutClock')->edit();
		$form->fill(['Name' => '']);
		$this->query('button', 'Apply')->waitUntilClickable()->one()->click();
		$this->page->waitUntilReady();
		$dashboard->save();
		$this->assertEquals('Host for clock widget', $dashboard->getWidget('Host for clock widget')->getHeaderText());
		$form = $dashboard->getWidget('Host for clock widget')->edit();
		$form->fill(['Name' => 'LayoutClock']);
		$this->query('button', 'Apply')->waitUntilClickable()->one()->click();
		$this->page->waitUntilReady();
		$dashboard->save();
		$this->assertEquals('LayoutClock', $dashboard->getWidget('LayoutClock')->getHeaderText());
	}

	public static function getClockWidgetCommonData() {
		return [
			// #0 Name and show header change.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Show header' => true,
						'Name' => 'Name and show header name'
					]
				]
			],
			// #1 Refresh interval change.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Refresh interval' => '10 seconds',
						'Name' => 'Refresh interval change name'
					]
				]
			],
			// #2 Time type changed to Server time.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Name' => 'Time type changed to Server time',
						'Time type' => 'Server time'
					]
				]
			],
			// #3 Time type changed to Local time.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Name' => 'Time type changed to Local time',
						'Time type' => 'Local time'
					]
				]
			],
			// #4 Time type and refresh interval changed.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Time type' => 'Server time',
						'Refresh interval' => '10 seconds',
						'Name' => 'Time type and refresh interval changed'
					]
				]
			],
			// #5 Empty name added.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Name' => ''
					]
				]
			],
			// #6 Symbols/numbers name added.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Name' => '!@#$%^&*()1234567890-='
					]
				]
			],
			// #7 Cyrillic added in name.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Name' => 'Имя кирилицей'
					]
				]
			],
			// #8 all fields changed.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Show header' => true,
						'Name' => 'Updated_name',
						'Refresh interval' => '10 minutes',
						'Time type' => 'Server time'
					]
				]
			],
			// #9 Host time without item.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Clock',
						'Show header' => false,
						'Name' => 'ClockWithoutItem',
						'Refresh interval' => '30 seconds',
						'Time type' => 'Host time'
					],
					'Error message' => [
						'Invalid parameter "Item": cannot be empty.'
					]
				]
			],
			// #10 Time type with item.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Name' => 'Time type with item',
						'Time type' => 'Host time',
						'Item' => 'Item for clock widget'
					]
				]
			],
			// #11 Update item.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Name' => 'Update item',
						'Time type' => 'Host time',
						'Item' => 'Item for clock widget 2'
					]
				]
			],
			// #12.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Show header' => true,
						'Name' => 'HostTimeClock',
						'Refresh interval' => '30 seconds',
						'Time type' => 'Host time',
						'Item' => 'Item for clock widget'
					]
				]
			],
			// #13.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Clock',
						'Show header' => true,
						'Name' => 'LocalTimeClock123',
						'Refresh interval' => '30 seconds',
						'Time type' => 'Local time'
					]
				]
			]
		];
	}

	/**
	 * Function for checking Clock widget form.
	 *
	 * @param array      $data      data provider
	 * @param boolean    $update    true if update scenario, false if create
	 *
	 * @dataProvider getClockWidgetCommonData
	 */
	public function checkFormClockWidget($data, $update = false) {
		if (CTestArrayHelper::get($data, 'expected', TEST_GOOD) === TEST_BAD) {
			$old_hash = CDBHelper::getHash($this->sql);
		}

		$dashboardid = $update
			? CDataHelper::get('ClockWidgets.dashboardids.Dashboard for updating clock widgets')
			: CDataHelper::get('ClockWidgets.dashboardids.Dashboard for creating clock widgets');

		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=' .$dashboardid);
		$dashboard = CDashboardElement::find()->one();

		$form = $update
			? $dashboard->getWidgets()->last()->edit()
			: $dashboard->edit()->addWidget()->asForm();

		$form->fill($data['fields']);
		$form->query('xpath://button[@class="dialogue-widget-save"]')->waitUntilReady()->one()->click();
		if ($data['expected'] === TEST_GOOD) {
			$dashboard->save();
			$this->assertMessage(TEST_GOOD, 'Dashboard updated');

			if ($update = false) {
				// Scenario where data is for creating widget.
				if ($data['fields']['Time type'] === 'Host time') {
					$data['fields'] = array_replace($data['fields'],
						['Item' => 'Host for clock widget: Item for clock widget']);
				}
			}
			else {
				// Scenario where data is for updating widget.
				if (array_key_exists('Item', $data['fields'])) {
					$item_name = ($data['fields']['Item'] === 'Item for clock widget')
						? 'Host for clock widget: Item for clock widget'
						: 'Host for clock widget: Item for clock widget 2';
					$data['fields'] = array_replace($data['fields'], ['Item' => $item_name]);
				}
			}

			// Check that widget updated.
			$dashboard->getWidgets()->last()->edit()->checkValue($data['fields']);

			// Check that widget is saved in DB.
			$this->assertEquals(1, CDBHelper::getCount('SELECT *'.
					' FROM widget w'.
					' WHERE w.dashboardid='.$dashboardid.''.
					' AND w.name ='.zbx_dbstr(CTestArrayHelper::get($data['fields'], 'Name', ''))
				));
		}
		else {
			$this->assertMessage(TEST_BAD, null, $data['Error message']);

			// Check that DB hash is not changed.
			$this->assertEquals($old_hash, CDBHelper::getHash($this->sql));
		}
	}

	/**
	 * Function for checking Clock Widgets creation.
	 *
	 * @param array      $data      data provider
	 * @dataProvider getClockWidgetCommonData
	 */
	public function testDashboardClockWidget_Create($data) {
		$this->checkFormClockWidget($data);
	}

	/**
	 * Function for checking Clock Widgets simple update.
	 */
	public function testDashboardClockWidget_SimpleUpdate() {
		$old_hash = CDBHelper::getHash($this->sql);
		$dashboardid = CDataHelper::get('ClockWidgets.dashboardids.Dashboard for updating clock widgets');
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=' . $dashboardid);
		$dashboard = CDashboardElement::find()->one();
		$dashboard->getWidget('UpdateClock')->edit();
		$this->query('button', 'Apply')->waitUntilClickable()->one()->click();
		$this->page->waitUntilReady();
		$dashboard->save();
		$this->assertEquals($old_hash, CDBHelper::getHash($this->sql));
	}

	/**
	 * Function for checking Clock Widgets successful update.
	 *
	 * @param array      $data      data provider
	 * @dataProvider getClockWidgetCommonData
	 */
	public function testDashboardClockWidget_Update($data) {
		$this->checkFormClockWidget($data, true);
	}

	/**
	 * Check clock widget deletion.
	 */
	public function testDashboardClockWidget_Delete() {
		$dashboardid = CDataHelper::get('ClockWidgets.dashboardids.Dashboard for creating clock widgets');
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid='.$dashboardid);
		$dashboard = CDashboardElement::find()->one();
		$widget = $dashboard->edit()->getWidget('DeleteClock');
		$this->assertTrue($widget->isEditable());
		$dashboard->deleteWidget('DeleteClock');
		$dashboard->save();
		$this->page->waitUntilReady();
		$message = CMessageElement::find()->waitUntilPresent()->one();
		$this->assertTrue($message->isGood());
		$this->assertEquals('Dashboard updated', $message->getTitle());

		// Check that widget is not present on dashboard and in DB.
		$this->assertFalse($dashboard->getWidget('DeleteClock', false)->isValid());
		$this->assertEquals(0, CDBHelper::getCount('SELECT *'.
			' FROM widget_field wf'.
			' LEFT JOIN widget w'.
			' ON w.widgetid=wf.widgetid'.
			' WHERE w.name='.zbx_dbstr('DeleteClock')
		));
	}

	public static function getCancelData() {
		return [
			// Cancel update widget.
			[
				[
					'existing_widget' => 'CancelClock',
					'save_widget' => true,
					'save_dashboard' => false
				]
			],
			[
				[
					'existing_widget' => 'CancelClock',
					'save_widget' => false,
					'save_dashboard' => true
				]
			],
			// Cancel create widget.
			[
				[
					'save_widget' => true,
					'save_dashboard' => false
				]
			],
			[
				[
					'save_widget' => false,
					'save_dashboard' => true
				]
			]
		];
	}

	/**
	 * Function checks if it's possible to cancel creation of clock widget.
	 *
	 * @dataProvider getCancelData
	 */
	public function testDashboardClockWidget_Cancel($data) {
		$old_hash = CDBHelper::getHash($this->sql);
		$dashboardid = CDataHelper::get('ClockWidgets.dashboardids.Dashboard for creating clock widgets');
		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid='.$dashboardid)->waitUntilReady();
		$dashboard = CDashboardElement::find()->one();

		// Start updating or creating a widget.
		if (CTestArrayHelper::get($data, 'existing_widget', false)) {
			$widget = $dashboard->getWidget($data['existing_widget']);
			$form = $widget->edit();
		}
		else {
			$overlay = $dashboard->edit()->addWidget();
			$form = $overlay->asForm();
			$form->fill(['Type' => 'Clock']);
			$widget = $dashboard->getWidgets()->last();
		}

		$form->fill(['Name' => 'Widget to be cancelled']);

		// Save or cancel widget.
		if (CTestArrayHelper::get($data, 'save_widget', false)) {
			$form->submit();
			$this->page->waitUntilReady();

			// Check that changes took place on the unsaved dashboard.
			$this->assertTrue($dashboard->getWidget('Widget to be cancelled')->isValid());
		}
		else {
			$dialog = COverlayDialogElement::find()->one();
			$dialog->query('button:Cancel')->one()->click();
			$dialog->ensureNotPresent();

			// Check that widget changes didn't take place after pressing "Cancel".
			if (CTestArrayHelper::get($data, 'existing_widget', false)) {
				$this->assertNotEquals('Widget to be cancelled', $widget->waitUntilReady()->getHeaderText());
			}
			else {
				// If test fails and widget isn't canceled, need to wait until page with widget appears on the dashboard.
				$this->waitUntilPageIsLoaded();
			}
		}

		// Save or cancel dashboard update.
		if (CTestArrayHelper::get($data, 'save_dashboard', false)) {
			$dashboard->save();
		}
		else {
			$dashboard->cancelEditing();
		}

		// Confirm that no changes were made to the widget.
		$this->assertEquals($old_hash, CDBHelper::getHash($this->sql));
	}

	/**
	 * Function for waiting for page to load.
	 */
	private function waitUntilPageIsLoaded() {
		try {
			for ($i = 0; $i < 9; $i++) {
				if ($widget->getID() !== $dashboard->getWidgets()->last()->getID()) {
					$this->fail('New widget was added after pressing "Cancel"');
				}
			}
		}
		catch (\Exception $ex) {
			// Code is not missing here.
		}
	}
}
