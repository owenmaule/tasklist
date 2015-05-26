<?php
/*
	tasklist
	Copyright Owen Maule 2015
	o@owen-m.com
	https://github.com/owenmaule/tasklist
	
	License: GNU Affero General Public License v3

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/agpl.html>.
*/

$config = array (
	# Database
	'dsn' => 'mysql:host=localhost;charset=utf8;dbname=tasklist',
	'db_user' => 'tasklist',
	'db_password' => 'YOUR_PASSWORD',
/*	'db_tables' => array (
		'auth' => array( 'name' => '`users`',
			'schema' => '' ), # Use default schema
		'tasks' => array( 'name' => '`tasks`',
			'schema' => '' ), # Use default schema
	), */
	'auto_install' => true,

	# Site
	'admin_email' => 'YOU@YOUR_DOMAIN.TLD',
	'app_location' => 'http://YOUR_DOMAIN.TLD/PATH_TO_TASKLIST/',
	'enforce_https' => false,		# if supported in your environment, set to true

	# Debugging
	'debug_messages' => false,		# can set to 'console' to route to browser console
	'debug_layout' => false,		# Applies CSS rules to observe the page layout elements
	'disable_javascript' => false,	# Runs the site in non-Javascript mode to test graceful degradation ( e.g. NoScript )

	# Security ( You can probably ignore these, the defaults should be fine )
	'salt_length' => 12,
	'hash_algo' => 'sha256',
	'reset_token_timeout' => 3600,	# 1 hour
	'limit_fails' => 10,
	'limit_fails_timer' => 600,		# 10 minutes
);
