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

require_once dirname(__FILE__).'/../common/testCalculatedFormula.php';

/**
 * @backup items
 */
class testFormulaCalculatedItem extends testCalculatedFormula {

	public $url = 'items.php?form=create&hostid=40001&context=host';

	public function getItemValidationData() {
		return [
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'avg(/host/trap,{#LLD})',
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "avg(/host/trap,{#LLD})".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'avg(/host/trap,"{#LLD}")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "avg".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'avg(/host/trap,"{#LLD}h")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "avg".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'bitand(last(/host/key,"{#LLD}:now-24h"),123)',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "last".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'count(/host/trap,"{#LLD}m",,"0")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "count".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => "min(//trap,\"#4:now-{#LLD}m\")",
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "min".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'sum(/host/trap,"5:{#LLD}")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "sum".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'sum(/host/trap,"5:now/{#LLD}")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "sum".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'timeleft(/host/trap,"{#LLD}:now-6h",20G,"power")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "timeleft".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'trendavg(/host/item,"1M:now/M-{#LLD}")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "trendavg".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'trendavg(/host/item,1M:now/M-{#LLD})',
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "trendavg(/host/item,1M:now/M-{#LLD})".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'trendmin(/host/key,"3600:{#LLD}-3600")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "trendmin".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'trendmin(/host/key,3600:{#LLD}-3600)',
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "trendmin(/host/key,3600:{#LLD}-3600)".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => "stddevsamp(//trap,{#LLD})",
					'error' => "Invalid parameter \"/1/params\": incorrect expression starting from \"stddevsamp(//trap,{#LLD})\"."
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => "stddevsamp(//trap,\"{#LLD}\")",
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "stddevsamp".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'between(5,(last(/host/trap)),{#LLD})',
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "between(5,(last(/host/trap)),{#LLD})".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'in(5,(last(/host/trap)),{#LLD},5,10)',
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "in(5,(last(/host/trap)),{#LLD},5,10)".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'bitrshift(last(/host/trap),{#LLD})',
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "bitrshift(last(/host/trap),{#LLD})".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => "max(min_foreach(/*/trap?[group=\"Servers\"],{#LLD}))+avg(count_foreach(/*/trap?[tag=\"tag1\"],\"{#LLD}h\"))-bitrshift".
							"(last(/host/trap),1)/between(5,(last(/host/trap)),10)*fuzzytime(/host/trap,60)>=trendsum(/host/item,\"{#LLD}:now/h\")",
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "max(min_foreach(/*/trap?[group="Servers"],'.
							'{#LLD}))+avg(count_foreach(/*/trap?[tag="tag1"],"{#LLD}h"))-bitrshift(last(/host/trap),1)/between(5,(last(/host/trap)),10)*'.
							'fuzzytime(/host/trap,60)>=trendsum(/host/item,"{#LLD}:now/h")".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'bitand(last(/host/key,{#LLD}:now-24h),123)',
					'error' => 'Invalid parameter "/1/params": incorrect expression starting from "bitand(last(/host/key,{#LLD}:now-24h),123)".'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'formula' => 'avg(/host/trap,"{#LLD}h")',
					'error' => 'Invalid parameter "/1/params": invalid second parameter in function "avg".'
				]
			]
		];
	}

	/**
	 * Test for checking formula field in calculated item.
	 *
	 * @dataProvider getCommonValidationData
	 * @dataProvider getItemValidationData
	 */
	public function testFormulaCalculatedItem_Validation($data) {
		$this->executeValidation($data);
	}
}
