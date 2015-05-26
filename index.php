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

	---
	Initially developed on PHP version: 5.3.3-7+squeeze
*/

	try
	{
		require_once 'error_handling.php';
		require_once 'tasklist.php';

		$app = new tasklist();
		
#		$app->testAlerts();

		# Auth feature disabled until supported in tasklist
#		if( $app->authentication() )
		{
			$app->actionTasklist();
		}
	}
	catch ( Exception $e )
	{
		if( isset( $app ) && is_object( $app ) )
		{
			$app->alert( $e->getMessage(), ALERT_ERROR );
		} else {
			die( 'Error: ' . $e->getMessage() );
		}
	}

	$app->render();