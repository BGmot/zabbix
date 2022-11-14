
# RabbitMQ cluster by Zabbix agent

## Overview

For Zabbix version: 6.2 and higher.
This template is developed to monitor the messaging broker RabbitMQ by Zabbix that works without any external scripts.

Most of the metrics are collected in one go, thanks to Zabbix bulk data collection.

The template RabbitMQ Cluster — collects metrics by polling [RabbitMQ management plugin](https://www.rabbitmq.com/management.html) with Zabbix agent.



This template was tested on:

- RabbitMQ, versions: 3.5.7, 3.7.17, 3.7.18, 3.7.7, 3.8.5, 3.8.12

## Setup

> See [Zabbix template operation](https://www.zabbix.com/documentation/6.2/manual/config/templates_out_of_the_box/zabbix_agent) for basic instructions.

Enable the RabbitMQ management plugin. See [RabbitMQ's documentation](https://www.rabbitmq.com/management.html) to enable it.

Create a user to monitor the service:

```bash
rabbitmqctl add_user zbx_monitor <PASSWORD>
rabbitmqctl set_permissions  -p / zbx_monitor "" "" ".*"
rabbitmqctl set_user_tags zbx_monitor monitoring
```

Login and password are also set in macros:

- {$RABBITMQ.API.USER}
- {$RABBITMQ.API.PASSWORD}

If your cluster consists of several nodes, it is recommended to assign the `cluster` template to a separate balancing host.
In the case of a single-node installation, you can assign the `cluster` template to one host with a `node` template.

If you use another API endpoint, then don't forget to change `{$RABBITMQ.API.CLUSTER_HOST}` macro.

Install and setup [Zabbix agent](https://www.zabbix.com/documentation/6.2/manual/installation/install_from_packages).


## Zabbix configuration

No specific Zabbix configuration is required.

### Macros used

|Name|Description|Default|
|----|-----------|-------|
|{$RABBITMQ.API.CLUSTER_HOST} |<p>The hostname or IP of RabbitMQ cluster API endpoint</p> |`127.0.0.1` |
|{$RABBITMQ.API.PASSWORD} |<p>-</p> |`zabbix` |
|{$RABBITMQ.API.PORT} |<p>The port of RabbitMQ API endpoint</p> |`15672` |
|{$RABBITMQ.API.SCHEME} |<p>Request scheme which may be http or https</p> |`http` |
|{$RABBITMQ.API.USER} |<p>-</p> |`zbx_monitor` |
|{$RABBITMQ.LLD.FILTER.EXCHANGE.MATCHES} |<p>Filter of discoverable exchanges</p> |`.*` |
|{$RABBITMQ.LLD.FILTER.EXCHANGE.NOT_MATCHES} |<p>Filter to exclude discovered exchanges</p> |`CHANGE_IF_NEEDED` |

## Template links

There are no template links in this template.

## Discovery rules

|Name|Description|Type|Key and additional info|
|----|-----------|----|----|
|Exchanges discovery |<p>Individual exchange metrics</p> |DEPENDENT |rabbitmq.exchanges.discovery<p>**Filter**:</p>AND <p>- {#EXCHANGE} MATCHES_REGEX `{$RABBITMQ.LLD.FILTER.EXCHANGE.MATCHES}`</p><p>- {#EXCHANGE} NOT_MATCHES_REGEX `{$RABBITMQ.LLD.FILTER.EXCHANGE.NOT_MATCHES}`</p> |
|Health Check 3.8.10+ discovery |<p>Version 3.8.10+ specific metrics</p> |DEPENDENT |rabbitmq.healthcheck.v3810.discovery<p>**Preprocessing**:</p><p>- JSONPATH: `$.management_version`</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p> |

## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|RabbitMQ |RabbitMQ: Connections total |<p>Total number of connections</p> |DEPENDENT |rabbitmq.overview.object_totals.connections<p>**Preprocessing**:</p><p>- JSONPATH: `$.object_totals.connections`</p> |
|RabbitMQ |RabbitMQ: Channels total |<p>Total number of channels</p> |DEPENDENT |rabbitmq.overview.object_totals.channels<p>**Preprocessing**:</p><p>- JSONPATH: `$.object_totals.channels`</p> |
|RabbitMQ |RabbitMQ: Queues total |<p>Total number of queues</p> |DEPENDENT |rabbitmq.overview.object_totals.queues<p>**Preprocessing**:</p><p>- JSONPATH: `$.object_totals.queues`</p> |
|RabbitMQ |RabbitMQ: Consumers total |<p>Total number of consumers</p> |DEPENDENT |rabbitmq.overview.object_totals.consumers<p>**Preprocessing**:</p><p>- JSONPATH: `$.object_totals.consumers`</p> |
|RabbitMQ |RabbitMQ: Exchanges total |<p>Total number of exchanges</p> |DEPENDENT |rabbitmq.overview.object_totals.exchanges<p>**Preprocessing**:</p><p>- JSONPATH: `$.object_totals.exchanges`</p> |
|RabbitMQ |RabbitMQ: Messages total |<p>Total number of messages (ready plus unacknowledged)</p> |DEPENDENT |rabbitmq.overview.queue_totals.messages<p>**Preprocessing**:</p><p>- JSONPATH: `$.queue_totals.messages`</p> |
|RabbitMQ |RabbitMQ: Messages ready for delivery |<p>Number of messages ready for deliver</p> |DEPENDENT |rabbitmq.overview.queue_totals.messages.ready<p>**Preprocessing**:</p><p>- JSONPATH: `$.queue_totals.messages_ready`</p> |
|RabbitMQ |RabbitMQ: Messages unacknowledged |<p>Number of unacknowledged messages</p> |DEPENDENT |rabbitmq.overview.queue_totals.messages.unacknowledged<p>**Preprocessing**:</p><p>- JSONPATH: `$.queue_totals.messages_unacknowledged`</p> |
|RabbitMQ |RabbitMQ: Messages acknowledged |<p>Number of messages delivered to clients and acknowledged</p> |DEPENDENT |rabbitmq.overview.messages.ack<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.ack`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages acknowledged per second |<p>Rate of messages delivered to clients and acknowledged per second</p> |DEPENDENT |rabbitmq.overview.messages.ack.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.ack_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages confirmed |<p>Count of messages confirmed</p> |DEPENDENT |rabbitmq.overview.messages.confirm<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.confirm`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages confirmed per second |<p>Rate of messages confirmed per second</p> |DEPENDENT |rabbitmq.overview.messages.confirm.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.confirm_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages delivered |<p>Sum of messages delivered in acknowledgement mode to consumers, in no-acknowledgement mode to consumers, in acknowledgement mode in response to basic.get, and in no-acknowledgement mode in response to basic.get</p> |DEPENDENT |rabbitmq.overview.messages.deliver_get<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.deliver_get`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages delivered per second |<p>Rate per second of the sum of messages delivered in acknowledgement mode to consumers, in no-acknowledgement mode to consumers, in acknowledgement mode in response to basic.get, and in no-acknowledgement mode in response to basic.get</p> |DEPENDENT |rabbitmq.overview.messages.deliver_get.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.deliver_get_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages published |<p>Count of messages published</p> |DEPENDENT |rabbitmq.overview.messages.publish<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.publish`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages published per second |<p>Rate of messages published per second</p> |DEPENDENT |rabbitmq.overview.messages.publish.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.publish_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages publish_in |<p>Count of messages published from channels into this overview</p> |DEPENDENT |rabbitmq.overview.messages.publish_in<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.publish_in`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages publish_in per second |<p>Rate of messages published from channels into this overview per sec</p> |DEPENDENT |rabbitmq.overview.messages.publish_in.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.publish_in_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages publish_out |<p>Count of messages published from this overview into queues</p> |DEPENDENT |rabbitmq.overview.messages.publish_out<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.publish_out`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages publish_out per second |<p>Rate of messages published from this overview into queues per second,0,rabbitmq,total msgs pub out rate</p> |DEPENDENT |rabbitmq.overview.messages.publish_out.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.publish_out_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages returned unroutable |<p>Count of messages returned to publisher as unroutable</p> |DEPENDENT |rabbitmq.overview.messages.return_unroutable<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.return_unroutable`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages returned unroutable per second |<p>Rate of messages returned to publisher as unroutable per second</p> |DEPENDENT |rabbitmq.overview.messages.return_unroutable.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.return_unroutable_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages returned redeliver |<p>Count of subset of messages in deliver_get which had the redelivered flag set</p> |DEPENDENT |rabbitmq.overview.messages.redeliver<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.redeliver`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Messages returned redeliver per second |<p>Rate of subset of messages in deliver_get which had the redelivered flag set per second</p> |DEPENDENT |rabbitmq.overview.messages.redeliver.rate<p>**Preprocessing**:</p><p>- JSONPATH: `$.message_stats.redeliver_details.rate`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Healthcheck: alarms in effect in the cluster{#SINGLETON} |<p>Responds a 200 OK if there are no alarms in effect in the cluster, otherwise responds with a 503 Service Unavailable.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.CLUSTER_HOST}:{$RABBITMQ.API.PORT}/api/health/checks/alarms{#SINGLETON}"]<p>**Preprocessing**:</p><p>- REGEX: `HTTP\/1\.1\b\s(\d+) \1`</p><p>- JAVASCRIPT: `switch(value){  case '200': return 1  case '503': return 0  default: 2}`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages acknowledged |<p>Number of messages delivered to clients and acknowledged</p> |DEPENDENT |rabbitmq.exchange.messages.ack["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.ack.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages acknowledged per second |<p>Rate of messages delivered to clients and acknowledged per second</p> |DEPENDENT |rabbitmq.exchange.messages.ack.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.ack_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages confirmed |<p>Count of messages confirmed</p> |DEPENDENT |rabbitmq.exchange.messages.confirm["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.confirm.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages confirmed per second |<p>Rate of messages confirmed per second</p> |DEPENDENT |rabbitmq.exchange.messages.confirm.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.confirm_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages delivered |<p>Sum of messages delivered in acknowledgement mode to consumers, in no-acknowledgement mode to consumers, in acknowledgement mode in response to basic.get, and in no-acknowledgement mode in response to basic.get</p> |DEPENDENT |rabbitmq.exchange.messages.deliver_get["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.deliver_get.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages delivered per second |<p>Rate per second of the sum of messages delivered in acknowledgement mode to consumers, in no-acknowledgement mode to consumers, in acknowledgement mode in response to basic.get, and in no-acknowledgement mode in response to basic.get</p> |DEPENDENT |rabbitmq.exchange.messages.deliver_get.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.deliver_get_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages published |<p>Count of messages published</p> |DEPENDENT |rabbitmq.exchange.messages.publish["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.publish.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages published per second |<p>Rate of messages published per second</p> |DEPENDENT |rabbitmq.exchange.messages.publish.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.publish_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages publish_in |<p>Count of messages published from channels into this overview</p> |DEPENDENT |rabbitmq.exchange.messages.publish_in["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.publish_in.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages publish_in per second |<p>Rate of messages published from channels into this overview per sec</p> |DEPENDENT |rabbitmq.exchange.messages.publish_in.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.publish_in_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages publish_out |<p>Count of messages published from this overview into queues</p> |DEPENDENT |rabbitmq.exchange.messages.publish_out["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.publish_out.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages publish_out per second |<p>Rate of messages published from this overview into queues per second,0,rabbitmq,total msgs pub out rate</p> |DEPENDENT |rabbitmq.exchange.messages.publish_out.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.publish_out_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages returned unroutable |<p>Count of messages returned to publisher as unroutable</p> |DEPENDENT |rabbitmq.exchange.messages.return_unroutable["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.return_unroutable.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages returned unroutable per second |<p>Rate of messages returned to publisher as unroutable per second</p> |DEPENDENT |rabbitmq.exchange.messages.return_unroutable.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.return_unroutable_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange [{#VHOST}][{#EXCHANGE}][{#TYPE}]: Messages redelivered |<p>Count of subset of messages in deliver_get which had the redelivered flag set</p> |DEPENDENT |rabbitmq.exchange.messages.redeliver["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.redeliver.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Exchange {#VHOST}/{#EXCHANGE}/{#TYPE}: Messages redelivered per second |<p>Rate of subset of messages in deliver_get which had the redelivered flag set per second</p> |DEPENDENT |rabbitmq.exchange.messages.redeliver.rate["{#VHOST}/{#EXCHANGE}/{#TYPE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#EXCHANGE}" && @.vhost == "{#VHOST}" && @.type =="{#TYPE}")].message_stats.redeliver_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|Zabbix raw items |RabbitMQ: Get overview |<p>The HTTP API endpoint that returns cluster-wide metrics</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.CLUSTER_HOST}:{$RABBITMQ.API.PORT}/api/overview"]<p>**Preprocessing**:</p><p>- REGEX: `\n\s?\n(.*) \1`</p> |
|Zabbix raw items |RabbitMQ: Get exchanges |<p>The HTTP API endpoint that returns exchanges metrics</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.CLUSTER_HOST}:{$RABBITMQ.API.PORT}/api/exchanges"]<p>**Preprocessing**:</p><p>- REGEX: `\n\s?\n(.*) \1`</p> |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|RabbitMQ: There are active alarms in the cluster |<p>http://{HOST.CONN}:{$RABBITMQ.API.PORT}/api/index.html</p> |`last(/RabbitMQ cluster by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.CLUSTER_HOST}:{$RABBITMQ.API.PORT}/api/health/checks/alarms{#SINGLETON}"])=0` |AVERAGE | |
|RabbitMQ: Failed to fetch overview data |<p>Zabbix has not received any data for items for the last 30 minutes.</p> |`nodata(/RabbitMQ cluster by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.CLUSTER_HOST}:{$RABBITMQ.API.PORT}/api/overview"],30m)=1` |WARNING |<p>Manual close: YES</p> |

## Feedback

Please report any issues with the template at https://support.zabbix.com.

You can also provide feedback, discuss the template, or ask for help at [ZABBIX forums](https://www.zabbix.com/forum/zabbix-suggestions-and-feedback/387226-discussion-thread-for-official-zabbix-template-rabbitmq).

# RabbitMQ node by Zabbix agent

## Overview

For Zabbix version: 6.2 and higher.
This template is developed to monitor RabbitMQ by Zabbix that works without any external scripts.

Most of the metrics are collected in one go, thanks to Zabbix bulk data collection.

The template RabbitMQ Node — (Zabbix version >= 4.2) collects metrics by polling [RabbitMQ management plugin](https://www.rabbitmq.com/management.html) with Zabbix agent.

It also uses Zabbix agent to collect RabbitMQ Linux process statistics, such as the CPU usage, memory usage, and whether the process is running or not.



This template was tested on:

- RabbitMQ, versions: 3.5.7, 3.7.17, 3.7.18, 3.7.7, 3.8.5, 3.8.12

## Setup

Enable the RabbitMQ management plugin. See [RabbitMQ's documentation](https://www.rabbitmq.com/management.html) to enable it.

Create a user to monitor the service:

```bash
rabbitmqctl add_user zbx_monitor <PASSWORD>
rabbitmqctl set_permissions  -p / zbx_monitor "" "" ".*"
rabbitmqctl set_user_tags zbx_monitor monitoring
```

Login and password are also set in macros:

- {$RABBITMQ.API.USER}
- {$RABBITMQ.API.PASSWORD}

If you use another API endpoint, then don't forget to change `{$RABBITMQ.API.HOST}` macro.
Install and setup [Zabbix agent](https://www.zabbix.com/documentation/6.2/manual/installation/install_from_packages).


## Zabbix configuration

No specific Zabbix configuration is required.

### Macros used

|Name|Description|Default|
|----|-----------|-------|
|{$RABBITMQ.API.HOST} |<p>The hostname or IP of RabbitMQ API endpoint</p> |`127.0.0.1` |
|{$RABBITMQ.API.PASSWORD} |<p>-</p> |`zabbix` |
|{$RABBITMQ.API.PORT} |<p>The port of RabbitMQ API endpoint</p> |`15672` |
|{$RABBITMQ.API.SCHEME} |<p>Request scheme which may be http or https</p> |`http` |
|{$RABBITMQ.API.USER} |<p>-</p> |`zbx_monitor` |
|{$RABBITMQ.CLUSTER.NAME} |<p>The name of RabbitMQ cluster</p> |`rabbit` |
|{$RABBITMQ.LLD.FILTER.QUEUE.MATCHES} |<p>Filter of discoverable queues</p> |`.*` |
|{$RABBITMQ.LLD.FILTER.QUEUE.NOT_MATCHES} |<p>Filter to exclude discovered queues</p> |`CHANGE_IF_NEEDED` |
|{$RABBITMQ.MESSAGES.MAX.WARN} |<p>Maximum number of messages in the queue for trigger expression</p> |`1000` |
|{$RABBITMQ.PROCESS_NAME} |<p>RabbitMQ server process name</p> |`beam.smp` |
|{$RABBITMQ.RESPONSE_TIME.MAX.WARN} |<p>Maximum RabbitMQ response time in seconds for trigger expression</p> |`10` |

## Template links

There are no template links in this template.

## Discovery rules

|Name|Description|Type|Key and additional info|
|----|-----------|----|----|
|Health Check 3.8.10+ discovery |<p>Specific metrics for the versions: up to and including 3.8.10.</p> |DEPENDENT |rabbitmq.healthcheck.v3810.discovery<p>**Preprocessing**:</p><p>- JSONPATH: `$.management_version`</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p> |
|Health Check 3.8.9- discovery |<p>Specific metrics for the versions: up to and including 3.8.4.</p> |DEPENDENT |rabbitmq.healthcheck.v389.discovery<p>**Preprocessing**:</p><p>- JSONPATH: `$.management_version`</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p> |
|Queues discovery |<p>The metrics for an individual queue.</p> |DEPENDENT |rabbitmq.queues.discovery<p>**Filter**:</p>AND <p>- {#QUEUE} MATCHES_REGEX `{$RABBITMQ.LLD.FILTER.QUEUE.MATCHES}`</p><p>- {#QUEUE} NOT_MATCHES_REGEX `{$RABBITMQ.LLD.FILTER.QUEUE.NOT_MATCHES}`</p><p>- {#NODE} MATCHES_REGEX `{$RABBITMQ.CLUSTER.NAME}@{HOST.NAME}`</p> |
|RabbitMQ process discovery |<p>The discovery of RabbitMQ summary processes.</p> |DEPENDENT |rabbitmq.proc.discovery<p>**Filter**:</p>AND <p>- {#NAME} MATCHES_REGEX `{$RABBITMQ.PROCESS_NAME}`</p> |

## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|RabbitMQ |RabbitMQ: Get nodes |<p>The HTTP API endpoint that returns metrics from the nodes.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/nodes/{$RABBITMQ.CLUSTER.NAME}@{HOST.NAME}?memory=true"]<p>**Preprocessing**:</p><p>- REGEX: `\n\s?\n(.*) \1`</p> |
|RabbitMQ |RabbitMQ: Management plugin version |<p>The version of the management plugin in use.</p> |DEPENDENT |rabbitmq.node.overview.management_version<p>**Preprocessing**:</p><p>- JSONPATH: `$.management_version`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|RabbitMQ |RabbitMQ: RabbitMQ version |<p>The version of RabbitMQ on the node which processed this request.</p> |DEPENDENT |rabbitmq.node.overview.rabbitmq_version<p>**Preprocessing**:</p><p>- JSONPATH: `$.rabbitmq_version`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|RabbitMQ |RabbitMQ: Used file descriptors |<p>Used file descriptors</p> |DEPENDENT |rabbitmq.node.fd_used<p>**Preprocessing**:</p><p>- JSONPATH: `$.fd_used`</p> |
|RabbitMQ |RabbitMQ: Free disk space |<p>The current free disk space.</p> |DEPENDENT |rabbitmq.node.disk_free<p>**Preprocessing**:</p><p>- JSONPATH: `$.disk_free`</p> |
|RabbitMQ |RabbitMQ: Memory used |<p>The memory usage expressed in bytes.</p> |DEPENDENT |rabbitmq.node.mem_used<p>**Preprocessing**:</p><p>- JSONPATH: `$.mem_used`</p> |
|RabbitMQ |RabbitMQ: Memory limit |<p>The memory usage with high watermark properties expressed in bytes.</p> |DEPENDENT |rabbitmq.node.mem_limit<p>**Preprocessing**:</p><p>- JSONPATH: `$.mem_limit`</p> |
|RabbitMQ |RabbitMQ: Disk free limit |<p>The free space limit of a disk expressed in bytes.</p> |DEPENDENT |rabbitmq.node.disk_free_limit<p>**Preprocessing**:</p><p>- JSONPATH: `$.disk_free_limit`</p> |
|RabbitMQ |RabbitMQ: Runtime run queue |<p>The average number of Erlang processes waiting to run.</p> |DEPENDENT |rabbitmq.node.run_queue<p>**Preprocessing**:</p><p>- JSONPATH: `$.run_queue`</p> |
|RabbitMQ |RabbitMQ: Sockets used |<p>The number of file descriptors used as sockets.</p> |DEPENDENT |rabbitmq.node.sockets_used<p>**Preprocessing**:</p><p>- JSONPATH: `$.sockets_used`</p> |
|RabbitMQ |RabbitMQ: Sockets available |<p>The file descriptors available for use as sockets.</p> |DEPENDENT |rabbitmq.node.sockets_total<p>**Preprocessing**:</p><p>- JSONPATH: `$.sockets_total`</p> |
|RabbitMQ |RabbitMQ: Number of network partitions |<p>The number of network partitions this node "sees".</p> |DEPENDENT |rabbitmq.node.partitions<p>**Preprocessing**:</p><p>- JSONPATH: `$.partitions`</p><p>- JAVASCRIPT: `return JSON.parse(value).length;`</p> |
|RabbitMQ |RabbitMQ: Is running |<p>Is the node running or not</p> |DEPENDENT |rabbitmq.node.running<p>**Preprocessing**:</p><p>- JSONPATH: `$.running`</p><p>- BOOL_TO_DECIMAL</p> |
|RabbitMQ |RabbitMQ: Memory alarm |<p>It checks whether the host has a memory alarm or not.</p> |DEPENDENT |rabbitmq.node.mem_alarm<p>**Preprocessing**:</p><p>- JSONPATH: `$.mem_alarm`</p><p>- BOOL_TO_DECIMAL</p> |
|RabbitMQ |RabbitMQ: Disk free alarm |<p>It checks whether the node has a disk alarm or not.</p> |DEPENDENT |rabbitmq.node.disk_free_alarm<p>**Preprocessing**:</p><p>- JSONPATH: `$.disk_free_alarm`</p><p>- BOOL_TO_DECIMAL</p> |
|RabbitMQ |RabbitMQ: Uptime |<p>Uptime expressed in milliseconds.</p> |DEPENDENT |rabbitmq.node.uptime<p>**Preprocessing**:</p><p>- JSONPATH: `$.uptime`</p><p>- MULTIPLIER: `0.001`</p> |
|RabbitMQ |RabbitMQ: Get processes summary |<p>The aggregated data of summary metrics for all processes.</p> |ZABBIX_PASSIVE |proc.get[,,,summary] |
|RabbitMQ |RabbitMQ: Service ping |<p>-</p> |ZABBIX_PASSIVE |net.tcp.service["{$RABBITMQ.API.SCHEME}","{$RABBITMQ.API.HOST}","{$RABBITMQ.API.PORT}"]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `10m`</p> |
|RabbitMQ |RabbitMQ: Service response time |<p>-</p> |ZABBIX_PASSIVE |net.tcp.service.perf["{$RABBITMQ.API.SCHEME}","{$RABBITMQ.API.HOST}","{$RABBITMQ.API.PORT}"] |
|RabbitMQ |RabbitMQ: Get process data |<p>The summary metrics aggregated by a process {#NAME}.</p> |DEPENDENT |rabbitmq.proc.get[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.[?(@["name"]=="{#NAME}")].first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> Failed to retrieve process {#NAME} data`</p> |
|RabbitMQ |RabbitMQ: Number of running processes |<p>The number of running processes {#NAME}.</p> |DEPENDENT |rabbitmq.proc.num[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.processes`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1h`</p> |
|RabbitMQ |RabbitMQ: Memory usage (rss) |<p>The summary of resident set size memory used by a process {#NAME} expressed in bytes.</p> |DEPENDENT |rabbitmq.proc.rss[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.rss`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|RabbitMQ |RabbitMQ: Memory usage (vsize) |<p>The summary of virtual memory used by a process {#NAME} expressed in bytes.</p> |DEPENDENT |rabbitmq.proc.vmem[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.vsize`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|RabbitMQ |RabbitMQ: Memory usage, % |<p>The percentage of real memory used by a process {#NAME}.</p> |DEPENDENT |rabbitmq.proc.pmem[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.pmem`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|RabbitMQ |RabbitMQ: CPU utilization |<p>The percentage of the CPU utilization by a process {#NAME}.</p> |ZABBIX_PASSIVE |proc.cpu.util[{#NAME}] |
|RabbitMQ |RabbitMQ: Healthcheck: local alarms in effect on this node{#SINGLETON} |<p>It responds with a status code - 200 OK- if there are no local alarms in effect on the target node; otherwise it responds with a status code - 503 Service Unavailable.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/local-alarms{#SINGLETON}"]<p>**Preprocessing**:</p><p>- REGEX: `HTTP\/1\.1\b\s(\d+) \1`</p><p>- JAVASCRIPT: `switch(value){  case '200': return 1  case '503': return 0  default: 2}`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|RabbitMQ |RabbitMQ: Healthcheck: expiration date on the certificates{#SINGLETON} |<p>It checks the expiration date on the certificates for every listener configured to use the Transport Layer Security (TLS). It responds with a status code -200 OK- if all the certificates are valid (have not expired); otherwise it responds with a status code -503 Service Unavailable.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/certificate-expiration/1/months{#SINGLETON}"]<p>**Preprocessing**:</p><p>- REGEX: `HTTP\/1\.1\b\s(\d+) \1`</p><p>- JAVASCRIPT: `switch(value){  case '200': return 1  case '503': return 0  default: 2}`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|RabbitMQ |RabbitMQ: Healthcheck: virtual hosts on this node{#SINGLETON} |<p>It responds with It responds with a status code -200 OK- if all virtual hosts are running on the target node; otherwise it responds with a status code -503 Service Unavailable.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/virtual-hosts{#SINGLETON}"]<p>**Preprocessing**:</p><p>- REGEX: `HTTP\/1\.1\b\s(\d+) \1`</p><p>- JAVASCRIPT: `switch(value){  case '200': return 1  case '503': return 0  default: 2}`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|RabbitMQ |RabbitMQ: Healthcheck: classic mirrored queues without synchronized mirrors online{#SINGLETON} |<p>It checks if there are classic mirrored queues without synchronized mirrors online (queues that would potentially lose data if the target node is shut down). It responds with a status code -200 OK- if there are no such classic mirrored queues; otherwise it responds with a status code -503 Service Unavailable.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/node-is-mirror-sync-critical{#SINGLETON}"]<p>**Preprocessing**:</p><p>- REGEX: `HTTP\/1\.1\b\s(\d+) \1`</p><p>- JAVASCRIPT: `switch(value){  case '200': return 1  case '503': return 0  default: 2}`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|RabbitMQ |RabbitMQ: Healthcheck: queues with minimum online quorum{#SINGLETON} |<p>It checks if there are quorum queues with minimum online quorum (queues that would lose their quorum and availability if the target node is shut down). It responds with a status code -200 OK- if there are no such quorum queues; otherwise it responds with a status code -503 Service Unavailable.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/node-is-quorum-critical{#SINGLETON}"]<p>**Preprocessing**:</p><p>- REGEX: `HTTP\/1\.1\b\s(\d+) \1`</p><p>- JAVASCRIPT: `switch(value){  case '200': return 1  case '503': return 0  default: 2}`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|RabbitMQ |RabbitMQ: Healthcheck{#SINGLETON} |<p>It checks whether the the RabbitMQ application is running; and the channels and queues can be listed successfully; and that no alarms are in effect.</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/healthchecks/node{#SINGLETON}"]<p>**Preprocessing**:</p><p>- REGEX: `\n\s?\n(.*) \1`</p><p>- JSONPATH: `$.status`</p><p>- BOOL_TO_DECIMAL</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages |<p>The count of total messages in the queue.</p> |DEPENDENT |rabbitmq.queue.messages["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].messages.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages per second |<p>The count of total messages per second in the queue.</p> |DEPENDENT |rabbitmq.queue.messages.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].messages_details.rate.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Consumers |<p>The number of consumers.</p> |DEPENDENT |rabbitmq.queue.consumers["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].consumers.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Memory |<p>The bytes of memory consumed by the Erlang process associated with the queue, including stack, heap and internal structures.</p> |DEPENDENT |rabbitmq.queue.memory["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].memory.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages ready |<p>Number of messages ready to be delivered to clients</p> |DEPENDENT |rabbitmq.queue.messages_ready["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].messages_ready.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages ready per second |<p>Number per second of messages ready to be delivered to clients</p> |DEPENDENT |rabbitmq.queue.messages_ready.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].messages_ready_details.rate.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages unacknowledged |<p>Number of messages delivered to clients but not yet acknowledged</p> |DEPENDENT |rabbitmq.queue.messages_unacknowledged["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].messages_unacknowledged.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages unacknowledged per second |<p>Number per second of messages delivered to clients but not yet acknowledged</p> |DEPENDENT |rabbitmq.queue.messages_unacknowledged.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].messages_unacknowledged_details.rate.first()`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages acknowledged |<p>Number of messages delivered to clients and acknowledged</p> |DEPENDENT |rabbitmq.queue.messages.ack["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.ack.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages acknowledged per second |<p>The number of messages per second delivered to clients and acknowledged.</p> |DEPENDENT |rabbitmq.queue.messages.ack.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.ack_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages delivered |<p>The count of messages delivered to consumers in acknowledgement mode.</p> |DEPENDENT |rabbitmq.queue.messages.deliver["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.deliver.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages delivered per second |<p>The count of messages per second delivered to consumers in acknowledgement mode.</p> |DEPENDENT |rabbitmq.queue.messages.deliver.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.deliver_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Sum of messages delivered |<p>The sum of messages delivered to consumers in acknowledgement mode, in no-acknowledgement mode; in acknowledgement mode in response to basic.get, and in no-acknowledgement mode in response to basic.get.</p> |DEPENDENT |rabbitmq.queue.messages.deliver_get["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.deliver_get.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Sum of messages delivered per second |<p>Rate per second of the sum of messages delivered in acknowledgement mode to consumers, in no-acknowledgement mode to consumers, in acknowledgement mode in response to basic.get, and in no-acknowledgement mode in response to basic.get</p> |DEPENDENT |rabbitmq.queue.messages.deliver_get.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.deliver_get_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages published |<p>Count of messages published</p> |DEPENDENT |rabbitmq.queue.messages.publish["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.publish.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages published per second |<p>Rate per second of messages published</p> |DEPENDENT |rabbitmq.queue.messages.publish.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.publish_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages redelivered |<p>Count of subset of messages in deliver_get which had the redelivered flag set</p> |DEPENDENT |rabbitmq.queue.messages.redeliver["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.redeliver.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|RabbitMQ |RabbitMQ: Queue [{#VHOST}][{#QUEUE}]: Messages redelivered per second |<p>Rate per second of subset of messages in deliver_get which had the redelivered flag set</p> |DEPENDENT |rabbitmq.queue.messages.redeliver.rate["{#VHOST}/{#QUEUE}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.name == "{#QUEUE}" && @.vhost == "{#VHOST}")].message_stats.redeliver_details.rate.first()`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p> |
|Zabbix raw items |RabbitMQ: Get node overview |<p>The HTTP API endpoint that returns cluster-wide metrics</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/overview"]<p>**Preprocessing**:</p><p>- REGEX: `\n\s?\n(.*) \1`</p> |
|Zabbix raw items |RabbitMQ: Get queues |<p>The HTTP API endpoint that returns queues metrics</p> |ZABBIX_PASSIVE |web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/queues"]<p>**Preprocessing**:</p><p>- REGEX: `\n\s?\n(.*) \1`</p> |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|RabbitMQ: Version has changed |<p>The RabbitMQ version has changed. Acknowledge (Ack) to close manually.</p> |`last(/RabbitMQ node by Zabbix agent/rabbitmq.node.overview.rabbitmq_version,#1)<>last(/RabbitMQ node by Zabbix agent/rabbitmq.node.overview.rabbitmq_version,#2) and length(last(/RabbitMQ node by Zabbix agent/rabbitmq.node.overview.rabbitmq_version))>0` |INFO |<p>Manual close: YES</p> |
|RabbitMQ: Number of network partitions is too high |<p>https://www.rabbitmq.com/partitions.html#detecting</p> |`min(/RabbitMQ node by Zabbix agent/rabbitmq.node.partitions,5m)>0` |WARNING | |
|RabbitMQ: Memory alarm |<p>https://www.rabbitmq.com/memory.html</p> |`last(/RabbitMQ node by Zabbix agent/rabbitmq.node.mem_alarm)=1` |AVERAGE | |
|RabbitMQ: Free disk space alarm |<p>https://www.rabbitmq.com/disk-alarms.html</p> |`last(/RabbitMQ node by Zabbix agent/rabbitmq.node.disk_free_alarm)=1` |AVERAGE | |
|RabbitMQ: Host has been restarted |<p>Uptime is less than 10 minutes.</p> |`last(/RabbitMQ node by Zabbix agent/rabbitmq.node.uptime)<10m` |INFO |<p>Manual close: YES</p> |
|RabbitMQ: Process is not running |<p>-</p> |`last(/RabbitMQ node by Zabbix agent/rabbitmq.proc.num[{#NAME}])=0` |HIGH | |
|RabbitMQ: Service is down |<p>-</p> |`last(/RabbitMQ node by Zabbix agent/net.tcp.service["{$RABBITMQ.API.SCHEME}","{$RABBITMQ.API.HOST}","{$RABBITMQ.API.PORT}"])=0 and last(/RabbitMQ node by Zabbix agent/rabbitmq.proc.num[{#NAME}])>0` |AVERAGE |<p>Manual close: YES</p> |
|RabbitMQ: Failed to fetch nodes data |<p>Zabbix has not received any data for items for the last 30 minutes.</p> |`nodata(/RabbitMQ node by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/nodes/{$RABBITMQ.CLUSTER.NAME}@{HOST.NAME}?memory=true"],30m)=1 and last(/RabbitMQ node by Zabbix agent/rabbitmq.proc.num[{#NAME}])>0` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- RabbitMQ: Process is not running</p> |
|RabbitMQ: Node is not running |<p>RabbitMQ node is not running</p> |`max(/RabbitMQ node by Zabbix agent/rabbitmq.node.running,5m)=0 and last(/RabbitMQ node by Zabbix agent/rabbitmq.proc.num[{#NAME}])>0` |AVERAGE |<p>**Depends on**:</p><p>- RabbitMQ: Service is down</p> |
|RabbitMQ: Service response time is too high |<p>-</p> |`min(/RabbitMQ node by Zabbix agent/net.tcp.service.perf["{$RABBITMQ.API.SCHEME}","{$RABBITMQ.API.HOST}","{$RABBITMQ.API.PORT}"],5m)>{$RABBITMQ.RESPONSE_TIME.MAX.WARN} and last(/RabbitMQ node by Zabbix agent/rabbitmq.proc.num[{#NAME}])>0` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- RabbitMQ: Service is down</p> |
|RabbitMQ: There are active alarms in the node |<p>http://{HOST.CONN}:{$RABBITMQ.API.PORT}/api/index.html</p> |`last(/RabbitMQ node by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/local-alarms{#SINGLETON}"])=0` |AVERAGE | |
|RabbitMQ: There are valid TLS certificates expiring in the next month |<p>http://{HOST.CONN}:{$RABBITMQ.API.PORT}/api/index.html</p> |`last(/RabbitMQ node by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/certificate-expiration/1/months{#SINGLETON}"])=0` |AVERAGE | |
|RabbitMQ: There are not running virtual hosts |<p>http://{HOST.CONN}:{$RABBITMQ.API.PORT}/api/index.html</p> |`last(/RabbitMQ node by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/virtual-hosts{#SINGLETON}"])=0` |AVERAGE | |
|RabbitMQ: There are queues that could potentially lose data if this node goes offline. |<p>http://{HOST.CONN}:{$RABBITMQ.API.PORT}/api/index.html</p> |`last(/RabbitMQ node by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/node-is-mirror-sync-critical{#SINGLETON}"])=0` |AVERAGE | |
|RabbitMQ: There are queues that would lose their quorum and availability if this node is shut down. |<p>http://{HOST.CONN}:{$RABBITMQ.API.PORT}/api/index.html</p> |`last(/RabbitMQ node by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/health/checks/node-is-quorum-critical{#SINGLETON}"])=0` |AVERAGE | |
|RabbitMQ: Node healthcheck failed |<p>https://www.rabbitmq.com/monitoring.html#health-checks</p> |`last(/RabbitMQ node by Zabbix agent/web.page.get["{$RABBITMQ.API.SCHEME}://{$RABBITMQ.API.USER}:{$RABBITMQ.API.PASSWORD}@{$RABBITMQ.API.HOST}:{$RABBITMQ.API.PORT}/api/healthchecks/node{#SINGLETON}"])=0` |AVERAGE | |
|RabbitMQ: Too many messages in queue [{#VHOST}][{#QUEUE}] |<p>-</p> |`min(/RabbitMQ node by Zabbix agent/rabbitmq.queue.messages["{#VHOST}/{#QUEUE}"],5m)>{$RABBITMQ.MESSAGES.MAX.WARN:"{#QUEUE}"}` |WARNING | |

## Feedback

Please report any issues with the template at https://support.zabbix.com.

You can also provide feedback, discuss the template, or ask for help at [ZABBIX forums](https://www.zabbix.com/forum/zabbix-suggestions-and-feedback/387226-discussion-thread-for-official-zabbix-template-rabbitmq).

