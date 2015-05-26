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

$( function() {
	console.log( "tasklist / Task Master (c) Copyright Owen Maule 2015 <o@owen-m.com> http://owen-m.com/" );
	console.log( "Latest version: https://github.com/owenmaule/   Licence: GNU Affero General Public License" );
	if( debugToConsole )
	{	// Transfer debug alerts to console
		$( "#alert .alert-debug" ).each( function() {
			// Replace <br /> with \n
			console.log( 'debug: ' + $( this ).html().replace( /<br\s*\/?>/mg, "\n" ) );
			$( this ).hide();
		} );
	}

	// Date widgets
	$( '.datepicker' ).each( function() {
		var date = $( this ).val();

		if( ! date )
		{
			date = null;
		}
		$( this ).datepicker()
			.datepicker( 'option', 'dateFormat', 'yy-mm-dd' )
			.datepicker( 'setDate' , date );
	} );

	// Dismiss the alerts
	$( "#alert span" ).dblclick( function() {
		$( this ).fadeOut( 600 );
	} );

	// Click list, to view entries
	$( "#selector" ).click( function() {
		// Very basic solution, will be improved to make an asynchronous call to json feed
		location.href = appLocation + 'entry/' + $( this ).val();
	} );
	$( "#select-entry" ).hide();

	// Delete confirmation
	$( "#entry-form input[type=submit][value='Delete']" ).click( function() {
		return confirm( "Are you sure you want to delete " + $( "#label" ).val() + "?" );
	} );
	
	// Enable the extra buttons (if any)
	$( "body" ).addClass( "js-enable" );

	/* Very serious folder/ task scheduling calender logo */

	// Overlap more elements
	$( "nav.pure-menu" ).css( "z-index", 0 );
	$( "form#entry-form input[type=text]" ).css( "z-index", 0 );
} );