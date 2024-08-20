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

#include "browser_alert.h"
#include "browser_error.h"
#include "duktape.h"
#include "embed.h"
#include "webdriver.h"
#include "zbxcommon.h"
#include "zbxembed.h"
#include "zbxtypes.h"

#ifdef HAVE_LIBCURL

typedef struct
{
	zbx_webdriver_t	*wd;
}
zbx_wd_alert_t;

#ifdef HAVE_LIBCURL

/******************************************************************************
 *                                                                            *
 * Purpose: return backing C structure embedded in alert object               *
 *                                                                            *
 ******************************************************************************/
static zbx_wd_alert_t *wd_alert(duk_context *ctx)
{
	zbx_wd_alert_t	*alert;
	zbx_es_env_t	*env;

	if (NULL == (env = zbx_es_get_env(ctx)))
	{
		(void)duk_push_error_object(ctx, DUK_RET_EVAL_ERROR, "cannot access internal environment");

		return NULL;
	}

	if (NULL == (alert = (zbx_wd_alert_t *)es_obj_get_this(env, ES_OBJ_ALERT)))
		(void)duk_push_error_object(ctx, DUK_RET_EVAL_ERROR, "cannot find native data attached to object");

	return alert;
}

/******************************************************************************
 *                                                                            *
 * Purpose: alert destructor                                                  *
 *                                                                            *
 ******************************************************************************/
static duk_ret_t	wd_alert_dtor(duk_context *ctx)
{
	zbx_wd_alert_t	*alert;
	zbx_es_env_t	*env;

	zabbix_log(LOG_LEVEL_TRACE, "Alert::~Alert()");

	if (NULL == (env = zbx_es_get_env(ctx)))
		return duk_error(ctx, DUK_RET_EVAL_ERROR, "cannot access internal environment");

	if (NULL != (alert = (zbx_wd_alert_t *)es_obj_detach_data(env, ES_OBJ_ALERT)))
	{
		webdriver_release(alert->wd);
		zbx_free(alert);
	}

	return 0;
}

/******************************************************************************
 *                                                                            *
 * Purpose: alert constructor                                                 *
 *                                                                            *
 ******************************************************************************/
static duk_ret_t	wd_alert_ctor(duk_context *ctx, zbx_webdriver_t *wd, const char *text)
{
	zbx_wd_alert_t	*alert;
	zbx_es_env_t	*env;

	zabbix_log(LOG_LEVEL_TRACE, "Alert::Alert()");

	if (NULL == (env = zbx_es_get_env(ctx)))
		return duk_error(ctx, DUK_RET_TYPE_ERROR, "cannot access internal environment");

	alert = (zbx_wd_alert_t *)zbx_malloc(NULL, sizeof(zbx_wd_alert_t));
	alert->wd = webdriver_addref(wd);

	duk_push_object(ctx);
	duk_push_string(ctx, text);
	duk_put_prop_string(ctx, -2, "text");

	es_obj_attach_data(env, alert, ES_OBJ_ALERT);

	duk_push_c_function(ctx, wd_alert_dtor, 1);
	duk_set_finalizer(ctx, -2);

	return 0;
}

/******************************************************************************
 *                                                                            *
 * Purpose: accept alert                                                      *
 *                                                                            *
 ******************************************************************************/
static duk_ret_t	wd_alert_accept(duk_context *ctx)
{
	zbx_wd_alert_t	*alert;
	char		*error = NULL;
	int		err_index = -1;

	if (NULL == (alert = wd_alert(ctx)))
		return duk_throw(ctx);

	if (SUCCEED != webdriver_accept_alert(alert->wd, &error))
	{
		err_index = browser_push_error(ctx, alert->wd, "cannot accept alert: %s", error);
		zbx_free(error);
	}

	if (-1 != err_index)
		return duk_throw(ctx);

	return 0;
}

/******************************************************************************
 *                                                                            *
 * Purpose: dismiss alert                                                     *
 *                                                                            *
 ******************************************************************************/
static duk_ret_t	wd_alert_dismiss(duk_context *ctx)
{
	zbx_wd_alert_t	*alert;
	char		*error = NULL;
	int		err_index = -1;

	if (NULL == (alert = wd_alert(ctx)))
		return duk_throw(ctx);

	if (SUCCEED != webdriver_dismiss_alert(alert->wd, &error))
	{
		err_index = browser_push_error(ctx, alert->wd, "cannot dismiss alert: %s", error);
		zbx_free(error);
	}

	if (-1 != err_index)
		return duk_throw(ctx);

	return 0;
}

static const duk_function_list_entry	alert_methods[] = {
	{"accept", wd_alert_accept, 0},
	{"dismiss", wd_alert_dismiss, 0},
	{0}
};

#else

static duk_ret_t	wd_alert_ctor(duk_context *ctx)
{
	if (!duk_is_constructor_call(ctx))
		return DUK_RET_EVAL_ERROR;

	return duk_error(ctx, DUK_RET_EVAL_ERROR, "missing cURL library");
}

static const duk_function_list_entry	alert_methods[] = {
	{NULL, NULL, 0}
};
#endif

/******************************************************************************
 *                                                                            *
 * Purpose: create alert and push it on stack                                 *
 *                                                                            *
 * Parameters: ctx  - [IN] duktape context                                    *
 *             wd   - [IN] webdriver object                                   *
 *             text - [IN] alert text                                         *
 *                                                                            *
 ******************************************************************************/
void	wd_alert_create(duk_context *ctx, zbx_webdriver_t *wd, const char *text)
{
	wd_alert_ctor(ctx, wd, text);
	duk_put_function_list(ctx, -1, alert_methods);
}

#endif
