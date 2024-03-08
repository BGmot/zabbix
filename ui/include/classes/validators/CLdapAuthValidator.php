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


/**
 * A class for validating LDAP credentials.
 */
class CLdapAuthValidator extends CValidator {

	/**
	 * Initialized LDAP service instance to test user credentials.
	 *
	 * @var CLdap
	 */
	public $ldap;

	/**
	 * Switch between more detailed or more generic error message mode.
	 *
	 * @var type
	 */
	protected $detailed_errors = false;

	/**
	 * Checks if the given user name and password are valid.
	 *
	 * The $value array must have the following attributes:
	 * - username   - user name
	 * - password   - password
	 *
	 * @param array $value
	 *
	 * @return bool
	 */
	public function validate($value) {
		$status = $this->ldap->checkCredentials($value['username'], $value['password']);

		if (!$status) {
			$this->setError($this->ldap->error);
		}

		return $status;
	}

	/**
	 * Return error message.
	 *
	 * @return string
	 */
	public function getError() {
		$error = parent::getError();
		$messages = [
			CLdap::ERR_PHP_EXTENSION => _('PHP LDAP extension missing.'),
			CLdap::ERR_SERVER_UNAVAILABLE => _('Cannot connect to LDAP server.'),
			CLdap::ERR_BIND_FAILED => _('Cannot bind to LDAP server.'),
			CLdap::ERR_BIND_ANON_FAILED => _('Cannot bind anonymously to LDAP server.'),
			CLdap::ERR_OPT_PROTOCOL_FAILED => _('Setting LDAP protocol failed.'),
			CLdap::ERR_OPT_TLS_FAILED => _('Starting TLS failed.'),
			CLdap::ERR_OPT_REFERRALS_FAILED => _('Setting LDAP referrals to "Off" failed.'),
			CLdap::ERR_OPT_DEREF_FAILED => _('Setting LDAP dereferencing mode failed.'),
			CLdap::ERR_BIND_DNSTRING_UNAVAILABLE => _('Cannot bind to LDAP server.')
		];

		$messages[CLdap::ERR_USER_NOT_FOUND] = $this->detailed_errors
			? _('Login name or password is incorrect.')
			: _('Incorrect user name or password or account is temporarily blocked.');

		return array_key_exists($error, $messages) ? $messages[$error] : '';
	}

	/**
	 * Check if connection error.
	 *
	 * @return bool
	 */
	public function isConnectionError() {
		return (parent::getError() !== CLdap::ERR_USER_NOT_FOUND);
	}
}
