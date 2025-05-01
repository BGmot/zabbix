<?php
class CControllerTwofaUpdate extends CController {

	/**
	 * @var CControllerResponseRedirect
	 */
	private $response;

	protected function init() {
		$this->response = new CControllerResponseRedirect((new CUrl('zabbix.php'))
			->setArgument('action', 'twofa.edit')
		);

		$this->disableSIDValidation();
	}

	protected function checkInput() {
		$fields = [
			'form_refresh' =>		'string',
			'update' =>			'string',
			'twofa_type' =>			'in '.ZBX_AUTH_2FA_NONE.','.ZBX_AUTH_2FA_DUO.','.ZBX_AUTH_2FA_GGL,
			'twofa_duo_api_hostname' =>	'db config.twofa_duo_api_hostname',
			'twofa_duo_integration_key' => 	'db config.twofa_duo_integration_key',
			'twofa_duo_secret_key' =>	'db config.twofa_duo_secret_key',
			'twofa_duo_a_key' =>		'db config.twofa_duo_a_key'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->response->setFormData($this->getInputAll());
			$this->setResponse($this->response);
		}

		return $ret;
	}

	/**
	 * Validate is user allowed to change configuration.
	 *
	 * @return bool
	 */
	protected function checkPermissions() {
		return $this->checkAccess(CRoleHelper::UI_ADMINISTRATION_AUTHENTICATION);
	}

	/**
	 * Validate DUO 2FA settings.
	 *
	 * @return bool
	 */
	private function validateDuoTwofa() {
		$twofa_fields = [
			'twofa_type', 'twofa_duo_api_hostname', 'twofa_duo_integration_key',
			'twofa_duo_secret_key', 'twofa_duo_a_key'
		];
		$twofa_auth = [
			'twofa_type' => CTwofaHelper::get(CTwofaHelper::TWOFA_TYPE),
			'twofa_duo_api_hostname' => CTwofaHelper::get(CTwofaHelper::TWOFA_DUO_API_HOSTNAME),
			'twofa_duo_integration_key' => CTwofaHelper::get(CTwofaHelper::TWOFA_DUO_INTEGRATION_KEY),
			'twofa_duo_secret_key' => CTwofaHelper::get(CTwofaHelper::TWOFA_DUO_SECRET_KEY),
			'twofa_duo_a_key' => CTwofaHelper::get(CTwofaHelper::TWOFA_DUO_A_KEY)
		];
		$this->getInputs($twofa_auth, $twofa_fields);

		if ($twofa_auth['twofa_type'] == ZBX_AUTH_2FA_NONE ||
		    $twofa_auth['twofa_type'] == ZBX_AUTH_2FA_GGL) {
			return true;
		}
		foreach ($twofa_fields as $field) {
			if (trim($twofa_auth[$field]) === '') {
				CMessageHelper::setErrorTitle(_s('Incorrect value for field "%1$s": %2$s.', $field, _('cannot be empty')));

				return false;
			}
		}

		return true;
	}

	protected function doAction() {
		$twofa_params = [
			CTwofaHelper::TWOFA_TYPE,
			CTwofaHelper::TWOFA_DUO_API_HOSTNAME,
			CTwofaHelper::TWOFA_DUO_INTEGRATION_KEY,
			CTwofaHelper::TWOFA_DUO_SECRET_KEY,
			CTwofaHelper::TWOFA_DUO_A_KEY
		];

		$twofa = [];
		foreach ($twofa_params as $param) {
			$twofa[$param] = CTwofaHelper::get($param);
		}

		if ($this->getInput('update', 'no') == 'no' ) {
			// Just switching Tabs
			$this->response->setFormData($this->getInputAll());
			$this->setResponse($this->response);
			return;
		}
		// User clicked 'Update'
		$duo_twofa_valid = $this->validateDuoTwofa();

		if (!$duo_twofa_valid) {
			$this->response->setFormData($this->getInputAll());
			$this->setResponse($this->response);
			return;
		}

		$fields = ['twofa_type' => ZBX_AUTH_2FA_NONE];

		if ($this->getInput('twofa_type', ZBX_AUTH_2FA_NONE) == ZBX_AUTH_2FA_DUO) {
			$fields += [
				'twofa_duo_api_hostname' =>	'',
				'twofa_duo_integration_key' => 	'',
				'twofa_duo_secret_key' =>	'',
				'twofa_duo_a_key' =>		''
			];
		}

		$data = $fields + $twofa;
		$this->getInputs($data, array_keys($fields));
		$data = array_diff_assoc($data, $twofa);

		if ($data) {
			$result = API::Twofa()->update($data);

			if ($result) {
				if (array_key_exists('twofa_type', $data)) {
					$this->invalidateSessions();
				}

				CMessageHelper::setSuccessTitle(_('2FA settings updated'));
			}
			else {
				$this->response->setFormData($this->getInputAll());
				CMessageHelper::setErrorTitle(_('Cannot update 2FA'));
			}
		}

		$this->setResponse($this->response);
	}

	/**
	 * Mark all active GROUP_GUI_ACCESS_INTERNAL sessions, except current user sessions, as ZBX_SESSION_PASSIVE.
	 *
	 * @return bool
	 */
	private function invalidateSessions() {
		$result = true;
		$internal_auth_user_groups = API::UserGroup()->get([
			'output' => [],
			'filter' => [
				'gui_access' => GROUP_GUI_ACCESS_INTERNAL
			],
			'preservekeys' => true
		]);

		$internal_auth_users = API::User()->get([
			'output' => [],
			'usrgrpids' => array_keys($internal_auth_user_groups),
			'preservekeys' => true
		]);
		unset($internal_auth_users[CWebUser::$data['userid']]);

		if ($internal_auth_users) {
			DBstart();
			$result = DB::update('sessions', [
				'values' => ['status' => ZBX_SESSION_PASSIVE],
				'where' => ['userid' => array_keys($internal_auth_users)]
			]);
			$result = DBend($result);
		}

		return $result;
	}
}
