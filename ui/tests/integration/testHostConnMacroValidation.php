<?php
/*
** Zabbix
** Copyright (C) 2001-2024 Zabbix SIA
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

require_once dirname(__FILE__).'/../include/CIntegrationTest.php';

/**
 * Test suite for action notifications
 *
 * @required-components server, agent
 * @configurationDataProvider defaultConfigurationProvider
 * @hosts test_hostconn
 */
class testHostConnMacroValidation extends CIntegrationTest {

	private static $hostid;
	private static $triggerid;
	private static $triggerid_action;
	private static $triggerid_neg;
	private static $triggerid_action_neg;
	private static $trigger_actionid;
	private static $trigger_actionid_neg;
	private static $eventid;
	private static $scriptid_action;
	private static $scriptid;
	private static $hostmacroid;
	private static $interfaceid;

	const ITEM_TRAP = 'trap1';
	const HOST_NAME = 'test_hostconn';

	/**
	 * @inheritdoc
	 */
	public function prepareData() {
		$response = $this->call('host.create', [
			'host' => self::HOST_NAME,
			'interfaces' => [
				[
					'type' => 1,
					'main' => 1,
					'useip' => 1,
					'ip' => '127.0.0.1',
					'dns' => '',
					'port' => $this->getConfigurationValue(self::COMPONENT_AGENT, 'ListenPort')
				]
			],
			'groups' => [
				[
					'groupid' => 4
				]
			]
		]);

		$this->assertArrayHasKey('hostids', $response['result']);
		$this->assertArrayHasKey(0, $response['result']['hostids']);
		self::$hostid = $response['result']['hostids'][0];

		$response = $this->call('host.get', [
			'output' => ['host'],
			'hostids' => [self::$hostid],
			'selectInterfaces' => ['interfaceid']
		]);

		$this->assertArrayHasKey(0, $response['result']);
		$this->assertArrayHasKey('interfaces', $response['result'][0]);
		$this->assertArrayHasKey(0, $response['result'][0]['interfaces']);
		self::$interfaceid = $response['result'][0]['interfaces'][0]['interfaceid'];

		$response = $this->call('item.create', [
			'hostid' => self::$hostid,
			'name' => self::ITEM_TRAP,
			'key_' => self::ITEM_TRAP,
			'type' => ITEM_TYPE_TRAPPER,
			'value_type' => ITEM_VALUE_TYPE_UINT64
		]);
		$this->assertArrayHasKey('itemids', $response['result']);
		$this->assertEquals(1, count($response['result']['itemids']));

		$response = $this->call('trigger.create', [
			'description' => 'Trigger for HOST.CONN test',
			'expression' => 'last(/'.self::HOST_NAME.'/'.self::ITEM_TRAP.')>0'
		]);
		$this->assertArrayHasKey('triggerids', $response['result']);
		$this->assertCount(1, $response['result']['triggerids']);
		self::$triggerid = $response['result']['triggerids'][0];

		$response = $this->call('trigger.create', [
			'description' => 'Trigger for HOST.CONN test via action',
			'expression' => 'last(/'.self::HOST_NAME.'/'.self::ITEM_TRAP.')>100'
		]);
		$this->assertArrayHasKey('triggerids', $response['result']);
		$this->assertCount(1, $response['result']['triggerids']);
		self::$triggerid_action = $response['result']['triggerids'][0];

		$response = $this->call('trigger.create', [
			'description' => 'Trigger for negative HOST.CONN test',
			'expression' => 'last(/'.self::HOST_NAME.'/'.self::ITEM_TRAP.')>0'
		]);
		$this->assertArrayHasKey('triggerids', $response['result']);
		$this->assertCount(1, $response['result']['triggerids']);
		self::$triggerid_neg = $response['result']['triggerids'][0];

		$response = $this->call('trigger.create', [
			'description' => 'Trigger for negative HOST.CONN test via action',
			'expression' => 'last(/'.self::HOST_NAME.'/'.self::ITEM_TRAP.')>2000'
		]);
		$this->assertArrayHasKey('triggerids', $response['result']);
		$this->assertCount(1, $response['result']['triggerids']);
		self::$triggerid_action_neg = $response['result']['triggerids'][0];

		$response = $this->call('usermacro.create', [
			'hostid' => self::$hostid,
			'macro' => '{$INJADDR}',
			'value' => '127.0.0.1'
		]);
		$this->assertArrayHasKey('result', $response);
		$this->assertArrayHasKey('hostmacroids', $response['result']);
		self::$hostmacroid = $response['result']['hostmacroids'][0];

		$response = $this->call('hostinterface.update', [
			'interfaceid' => self::$interfaceid,
			'useip' => 0,
			'dns' => '{$INJADDR}',
			'ip' => ''
		]);
		$this->assertArrayHasKey('interfaceids', $response['result']);
		$this->assertArrayHasKey(0, $response['result']['interfaceids']);

		$response = $this->call('script.create', [
			'name' => 'inj test',
			'command' => 'echo -n hello {HOST.CONN}',
			'execute_on' => ZBX_SCRIPT_EXECUTE_ON_SERVER,
			'scope' => ZBX_SCRIPT_SCOPE_HOST,
			'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT
		]);
		$this->assertArrayHasKey('scriptids', $response['result']);
		self::$scriptid = $response['result']['scriptids'][0];

		$response = $this->call('script.create', [
			'name' => 'inj test action',
			'command' => 'echo -n hello {HOST.CONN}',
			'execute_on' => ZBX_SCRIPT_EXECUTE_ON_SERVER,
			'scope' => ZBX_SCRIPT_SCOPE_ACTION,
			'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT
		]);
		$this->assertArrayHasKey('scriptids', $response['result']);
		self::$scriptid_action = $response['result']['scriptids'][0];

		$response = $this->call('action.create', [
			'esc_period' => '1m',
			'eventsource' => EVENT_SOURCE_TRIGGERS,
			'status' => 0,
			'filter' => [
				'conditions' => [
					[
						'conditiontype' => ZBX_CONDITION_TYPE_TRIGGER,
						'operator' => CONDITION_OPERATOR_EQUAL,
						'value' => self::$triggerid_action
					]
				],
				'evaltype' => CONDITION_EVAL_TYPE_AND_OR
			],
			'name' => 'action_trigger_trap',
			'operations' => [
				[
					'operationtype' => OPERATION_TYPE_COMMAND,
					'esc_period' => '0s',
					'esc_step_from' => 1,
					'esc_step_to' => 2,
					'evaltype' => CONDITION_EVAL_TYPE_AND_OR,
					'opcommand_grp' => [
						[
							'groupid' => 4
						]
					],
					'opcommand' => [
						'scriptid' => self::$scriptid_action
					]
				]
			],
			'pause_suppressed' => 0
		]);
		$this->assertArrayHasKey('actionids', $response['result']);
		$this->assertEquals(1, count($response['result']['actionids']));
		self::$trigger_actionid = $response['result']['actionids'][0];

		$response = $this->call('action.create', [
			'esc_period' => '1m',
			'eventsource' => EVENT_SOURCE_TRIGGERS,
			'status' => 0,
			'filter' => [
				'conditions' => [
					[
						'conditiontype' => ZBX_CONDITION_TYPE_TRIGGER,
						'operator' => CONDITION_OPERATOR_EQUAL,
						'value' => self::$triggerid_action_neg
					]
				],
				'evaltype' => CONDITION_EVAL_TYPE_AND_OR
			],
			'name' => 'Action on trigger (negative case)',
			'operations' => [
				[
					'operationtype' => OPERATION_TYPE_COMMAND,
					'esc_period' => '0s',
					'esc_step_from' => 1,
					'esc_step_to' => 2,
					'evaltype' => CONDITION_EVAL_TYPE_AND_OR,
					'opcommand_grp' => [
						[
							'groupid' => 4
						]
					],
					'opcommand' => [
						'scriptid' => self::$scriptid_action
					]
				]
			],
			'pause_suppressed' => 0
		]);
		$this->assertArrayHasKey('actionids', $response['result']);
		$this->assertEquals(1, count($response['result']['actionids']));
		self::$trigger_actionid_neg = $response['result']['actionids'][0];

		return true;
	}

	/**
	 * Component configuration provider for agent related tests.
	 *
	 * @return array
	 */
	public function defaultConfigurationProvider() {
		return [
			self::COMPONENT_SERVER => [
				'DebugLevel' => 4,
				'LogFileSize' => 20
			],
			self::COMPONENT_AGENT => [
				'Hostname' => self::HOST_NAME,
				'AllowKey' => 'system.run[*]',
			]
		];
	}

	/**
	 * Test code injection via HOST.CONN macro.
	 *
	 * @required-components server, agent
	 * @configurationDataProvider defaultConfigurationProvider
	 */
	public function testHostConnMacroValidation_testValidMacroManualHostScript() {
		$response = $this->callUntilDataIsPresent('script.execute', [
			'scriptid' => self::$scriptid,
			'hostid' => self::$hostid
		], 30, 2);
		$this->assertArrayHasKey('response', $response['result']);
		$this->assertEquals('success', $response['result']['response']);
		$this->assertArrayHasKey('value', $response['result']);
		$this->assertEquals('hello 127.0.0.1', $response['result']['value']);
	}

	/**
	 * Test code injection via HOST.CONN macro.
	 *
	 * @required-components server, agent
	 * @configurationDataProvider defaultConfigurationProvider
	 */
	public function testHostConnMacroValidation_testValidMacroManualEventScript() {
		$response = $this->call('script.update', [
			'scriptid' => self::$scriptid,
			'scope' => ZBX_SCRIPT_SCOPE_EVENT
		]);
		$this->assertArrayHasKey('scriptids', $response['result']);

		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 1);

		$response = $this->callUntilDataIsPresent('event.get', [
			'objectids' => self::$triggerid,
			'sortfield' => 'clock',
			'sortorder' => 'DESC',
			'limit' => 1
		], 30, 2);
		$this->assertArrayHasKey(0, $response['result']);
		$eventid = $response['result'][0]['eventid'];

		$response = $this->callUntilDataIsPresent('script.execute', [
			'scriptid' => self::$scriptid,
			'eventid' => $eventid
		], 30, 2);
		$this->assertArrayHasKey('response', $response['result']);
		$this->assertEquals('success', $response['result']['response']);
		$this->assertArrayHasKey('value', $response['result']);
		$this->assertEquals('hello 127.0.0.1', $response['result']['value']);
		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 0);
	}

	/**
	 * Test code injection via HOST.CONN macro.
	 *
	 * @required-components server, agent
	 * @configurationDataProvider defaultConfigurationProvider
	 */
	public function testHostConnMacroValidation_testValidMacroAction() {
		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 101);

		$response = $this->callUntilDataIsPresent('alert.get', [
			'actionids' => [self::$trigger_actionid],
			'sortfield' => 'alertid',
			'sortorder' => 'DESC',
			'limit' => 1
		], 5, 2);
		$this->assertArrayHasKey(0, $response['result']);
		$this->assertEquals('test_hostconn:echo -n hello 127.0.0.1', $response['result'][0]['message']);

		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 0);
	}

	/**
	 * Test code injection via HOST.CONN macro.
	 *
	 * @required-components server, agent
	 * @configurationDataProvider defaultConfigurationProvider
	 */
	public function testHostConnMacroValidation_testInvalidMacro() {
		$response = $this->call('script.update', [
			'scriptid' => self::$scriptid,
			'scope' => ZBX_SCRIPT_SCOPE_HOST
		]);
		$this->assertArrayHasKey('scriptids', $response['result']);

		$response = $this->call('usermacro.update', [
			'hostmacroid' => self::$hostmacroid,
			'value' => '127.0.0.1;uname'
		]);
		$this->assertArrayHasKey('result', $response);
		$this->assertArrayHasKey('hostmacroids', $response['result']);

		$this->reloadConfigurationCache();
		$this->waitForLogLineToBePresent(self::COMPONENT_SERVER, "End of DCsync_configuration()", true, 30, 1);

		$response = CAPIHelper::call('script.execute', [
			'scriptid' => self::$scriptid,
			'hostid' => self::$hostid
		]);
		$this->assertArrayHasKey('error', $response);
		$this->assertArrayHasKey('data', $response['error']);
		$this->assertEquals("Invalid macro '{HOST.CONN}' value", $response['error']['data']);
	}

	/**
	 * Test code injection via HOST.CONN macro.
	 *
	 * @required-components server, agent
	 * @configurationDataProvider defaultConfigurationProvider
	 */
	public function testHostConnMacroValidation_testInvalidMacroManualEventScript() {
		$response = $this->call('script.update', [
			'scriptid' => self::$scriptid,
			'scope' => ZBX_SCRIPT_SCOPE_EVENT
		]);
		$this->assertArrayHasKey('scriptids', $response['result']);

		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 222);

		$response = $this->callUntilDataIsPresent('event.get', [
			'objectids' => self::$triggerid,
			'sortfield' => 'clock',
			'sortorder' => 'DESC',
			'limit' => 1
		], 30, 2);
		$this->assertArrayHasKey(0, $response['result']);
		$eventid = $response['result'][0]['eventid'];

		$response = CAPIHelper::call('script.execute', [
			'scriptid' => self::$scriptid,
			'eventid' => $eventid
		]);
		$this->assertArrayHasKey('error', $response);
		$this->assertArrayHasKey('data', $response['error']);
		$this->assertEquals("Invalid macro '{HOST.CONN}' value", $response['error']['data']);
		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 0);
	}

	/**
	 * Test code injection via HOST.CONN macro.
	 *
	 * @required-components server, agent
	 * @configurationDataProvider defaultConfigurationProvider
	 */
	public function testHostConnMacroValidation_testInvalidMacroAction() {
		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 101);

		$response = $this->callUntilDataIsPresent('alert.get', [
			'actionids' => [self::$trigger_actionid_neg],
			'sortfield' => 'alertid',
			'sortorder' => 'DESC',
			'limit' => 1,
			'filter' => [
				'status' => 0
			]
		], 5, 2);
		$this->assertArrayHasKey(0, $response['result']);
		$this->assertEquals("Invalid macro '{HOST.CONN}' value", $response['result'][0]['error']);

		$this->sendSenderValue(self::HOST_NAME, self::ITEM_TRAP, 0);
	}
}
