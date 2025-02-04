<?php
/*
** Zabbix
** Copyright (C) 2001-2025 Zabbix SIA
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
 * Get 2FA authentication label.
 *
 * @param int $type
 *
 * @return string
 */
function twofa2str($type) {
	$authentications = [
		ZBX_AUTH_2FA_NONE => _('No 2FA'),
		ZBX_AUTH_2FA_DUO => _('DUO two factor authentication'),
		ZBX_AUTH_2FA_GGL => _('Google Authenticator app')
	];

	return $authentications[$type];
}

/***********************************************
	CHECK USER ACCESS TO SYSTEM STATUS
************************************************/
/* Function: check_perm2system()
 *
 * Description:
 * 		Checking user permissions to access system (affects server side: no notification will be sent)
 *
 * Comments:
 *		return true if permission is positive
 */
function check_perm2system($userid) {
	$sql = 'SELECT g.usrgrpid'.
			' FROM usrgrp g,users_groups ug'.
			' WHERE ug.userid='.zbx_dbstr($userid).
				' AND g.usrgrpid=ug.usrgrpid'.
				' AND g.users_status='.GROUP_STATUS_DISABLED;
	if ($res = DBfetch(DBselect($sql, 1))) {
		return false;
	}
	return true;
}

/**
 * Get user gui access.
 *
 * @param string $userid
 *
 * @return int
 */
function getUserGuiAccess(string $userid): int {
	if (isset(CWebUser::$data['gui_access'])) {
		return CWebUser::$data['gui_access'];
	}

	$gui_access = DBfetch(DBselect(
		'SELECT MAX(g.gui_access) AS gui_access'.
		' FROM usrgrp g,users_groups ug'.
		' WHERE g.usrgrpid=ug.usrgrpid'.
			' AND ug.userid='.zbx_dbstr($userid)
	));

	return $gui_access ? $gui_access['gui_access'] : GROUP_GUI_ACCESS_SYSTEM;
}

/**
 * Returns array of user groups by $userId
 *
 * @param int $userId
 *
 * @return array
 */
function getUserGroupsByUserId($userId) {
	static $userGroups;

	if (!isset($userGroups[$userId])) {
		$userGroups[$userId] = [];

		$result = DBselect('SELECT usrgrpid FROM users_groups WHERE userid='.zbx_dbstr($userId));
		while ($row = DBfetch($result)) {
			$userGroups[$userId][] = $row['usrgrpid'];
		}
	}

	return $userGroups[$userId];
}
