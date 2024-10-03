<?php declare(strict_types = 1);
/*
** Copyright (C) 2001-2024 Zabbix SIA
**
** This program is free software: you can redistribute it and/or modify it under the terms of
** the GNU Affero General Public License as published by the Free Software Foundation, version 3.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
** without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
** See the GNU Affero General Public License for more details.
**
** You should have received a copy of the GNU Affero General Public License along with this program.
** If not, see <https://www.gnu.org/licenses/>.
**/
?>

<script>
	const view = new class {

		init({groupid, name}) {
			this.form = document.getElementById('hostgroupForm');
			this.groupid = groupid;
			this.name = name;

			this.form.addEventListener('submit', (e) => this._onSubmit(e));
			this._initActionButtons();
		}

		_initActionButtons() {
			document.addEventListener('click', (e) => {
				if (e.target.classList.contains('js-update-hostgroup')) {
					this._submit(e.target);
				}
				else if (e.target.classList.contains('js-clone-hostgroup')) {
					this._clone();
				}
				else if (e.target.classList.contains('js-delete-hostgroup')) {
					this._delete(e.target);
				}
			});
		}

		_submit(button) {
			this._setLoading(button);

			const fields = getFormFields(this.form);
			fields.name = fields.name.trim();

			const curl = new Curl('zabbix.php');
			curl.setArgument('action', this.groupid !== null ? 'hostgroup.update' : 'hostgroup.create');

			this._post(curl.getUrl(), fields, (response) => {
				postMessageOk(response.success.title);

				if ('messages' in response.success) {
					postMessageDetails('success', response.success.messages);
				}

				const url = new Curl('zabbix.php');
				url.setArgument('action', 'hostgroup.list');

				location.href = url.getUrl();
			});
		}

		_clone() {
			const fields = getFormFields(this.form);
			const curl = new Curl('zabbix.php');
			curl.setArgument('action', 'hostgroup.edit');

			post(curl.getUrl(), {name: fields.name});
		}

		_delete(button) {
			const confirm_text = button.getAttribute('confirm');

			if (!confirm(confirm_text)) {
				return;
			}

			const curl = new Curl('zabbix.php');
			curl.setArgument('action', 'hostgroup.delete');
			curl.setArgument(CSRF_TOKEN_NAME, <?= json_encode(CCsrfTokenHelper::get('hostgroup')) ?>);

			this._post(curl.getUrl(), {groupids: [this.groupid]}, (response) => {
				postMessageOk(response.success.title);

				if ('messages' in response.success) {
					postMessageDetails('success', response.success.messages);
				}

				const url = new Curl('zabbix.php');
				url.setArgument('action', 'hostgroup.list');

				location.href = url.getUrl();
			});
		}

		_setLoading(active_button) {
			active_button.classList.add('is-loading');

			for (const button of this.form.querySelectorAll('.tfoot-buttons button:not(.js-cancel)')) {
				button.disabled = true;
			}
		}

		_unsetLoading() {
			for (const button of this.form.querySelectorAll('.tfoot-buttons button:not(.js-cancel)')) {
				button.classList.remove('is-loading');
				button.disabled = false;
			}
		}

		_post(url, data, success_callback) {
			fetch(url, {
				method: 'POST',
				headers: {'Content-Type': 'application/json'},
				body: JSON.stringify(data)
			})
				.then((response) => response.json())
				.then((response) => {
					if ('error' in response) {
						throw {error: response.error};
					}

					return response
				})
				.then(success_callback)
				.catch((exception) => {
					for (const element of this.form.parentNode.children) {
						if (element.matches('.msg-good, .msg-bad, .msg-warning')) {
							element.parentNode.removeChild(element);
						}
					}

					let title, messages;

					if (typeof exception === 'object' && 'error' in exception) {
						title = exception.error.title;
						messages = exception.error.messages;
					}
					else {
						messages = [<?= json_encode(_('Unexpected server error.')) ?>];
					}

					const message_box = makeMessageBox('bad', messages, title)[0];

					clearMessages();
					addMessage(message_box);
				})
				.finally(() => {
					this._unsetLoading();
				});
		}

		_onSubmit(e) {
			e.preventDefault();
			const button = this.form.querySelector('.tfoot-buttons button[type="submit"]');
			this._submit(button);
		}
}
</script>
