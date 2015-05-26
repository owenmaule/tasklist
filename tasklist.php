<?php
/*
	tasklist
	Copyright Owen Maule 2015
	o@owen-m.com
	https://github.com/owenmaule/

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
	3. Create a simple browser based task list. The task list should have full CRUD functionality and should
	have the following fields per task that are stored in a single MySQL table :

	Task id
	Task name
	Task description
	Deadline
	Completed

	The task list does not require any CSS but must be a valid HTML document. There is no need for a user system
	or login, just a simple table of all task list entries with view, add, update and delete functionality and
	appropriate web forms.
	
	The purpose of this exercise is for us to assess how you approach the problem and have a look at your coding
	style. Don't worry if you can't get all of the code completely finished in the given time, as long as your
	intentions for the implementation are there for us to see. Please provide comments in your code to explain
	decisions you have made where appropriate.

	---
	First use of appBase taken from pwm Password Manager. Much further generalisation work to be done on appBase.
	
	Functionality to add for tasklist:
		Search by deadline & by completion buttons
		Sort tasks by deadline toggle button
		Delete all completed tasks button
*/

require_once( 'appBase.php' );

class tasklist extends appBase
{
	private $taskTable = '';
	private $selected = 0;
	public $fields = array ( 'task_id', 'task_name', 'task_description', 'task_deadline', 'task_completed' );
	public $entries = array ( );
	public $entry = array ( );

	public function __construct()
	{
		parent::__construct();
	}

	# Set up database
	public function install()
	{
		/* Requirements specification:
			Task id
			Task name
			Task description
			Deadline
			Completed
		*/

		# Supply default table configuration
		# Save shortcut to table name
		$this->taskTable = $this->supplyTableDefaults( 'tasks', '`tasklist`', '(
`task_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`task_name` VARCHAR( 128 ) NOT NULL ,
`task_description` VARCHAR( 1024 ) NOT NULL ,
`task_deadline` DATE DEFAULT NULL ,
`task_completed` BOOLEAN DEFAULT FALSE
) ENGINE = InnoDB' );

		return parent::install();
	}

	public function loadEntries( $search = '' )
	{
		# To do: InnoDB fulltext index ( Requires MySQL 5.6+ )
		$queryParams = array ( /*'user_id' => $_SESSION[ 'user_id' ]*/ );
		$searchQuery = '';
		if( $search )
		{
			$searchQuery = /*' AND*/ ' WHERE '
				. '( task_name LIKE :search OR task_description LIKE :search )';
			$queryParams[ 'search' ] = '%' . $search . '%';
		}
		
		$query = $this->database->prepare( 'SELECT task_id, task_name FROM ' . $this->taskTable
			/*. ' WHERE ' user_id = :user_id'*/ . $searchQuery . ' ORDER BY task_name' );
		$query->execute( $queryParams );
		$result = $query->fetchAll();
		if( false != $result )
		{
			$this->entries = $result;
		}
	}

	public function entryIsMine( $selected )
	{
		/*
		$query = $this->database->prepare( 'SELECT user_id FROM ' . $this->taskTable
			. ' where task_id = ? LIMIT 1' );
		$query->execute( array ( $selected ) );
		$result = $query->fetch();
		if( false == $result )
		{
			$this->alert( 'Entry not found', ALERT_ERROR );
			return false;
		}		
		
		if( $result[ 'user_id' ] != $_SESSION[ 'user_id' ] )
		{
			$this->alert( 'The selected entry does not belong to you', ALERT_DENY );
			return false;
		}
		*/
		return true;
	}
	
	public function entryChanged( &$entry )
	{
		if( empty( $entry[ 'task_id' ] ) )
		{
			$this->alert( 'entryChanged(): entry has no task_id', ALERT_DEBUG );
			return true;
		}

		$fields = $this->fields;
		if( ! is_array( $fields ) )
		{
			throw new Exception( 'entryChanged(): fields is not an array' );
		}

		if( ! $this->loadEntry( $entry[ 'task_id' ] ) )
		{
			$this->alert( 'entryChanged(): failed to load entry', ALERT_DEBUG );
			return true;
		}

		foreach( $fields as $field )
		{
			if( ! isset( $entry[ $field ] ) )
			{
				$this->alert( 'entryChanged(): entry is missing field(s)', ALERT_DEBUG );
				return true;
			}

			if( ! isset( $entry[ $field ] ) )
			{
				$this->alert( 'entryChanged(): entry from loadEntry() is missing field(s)', ALERT_DEBUG );
				return true;
			}			

			if( $this->entry[ $field ] != $entry[ $field ] )
			{
				# Entry was changed
				return true;
			}
		}

		# Entry matches database
		return false;
	}
	
	public function loadEntry( $selected )
	{
		$selected = (int) $selected;
		if( ! $selected || ! $this->entryIsMine( $selected ) )
		{
			return false;
		}
		$query = $this->database->prepare( 'SELECT * FROM ' . $this->taskTable
			. ' where task_id = ? LIMIT 1' );
		$query->execute( array ( $selected ) );
		$result = $query->fetch();
		$this->entry = $result;
		
		# Datetime
		if( '0000-00-00' == $this->entry[ 'task_deadline' ] )
		{
			$this->entry[ 'task_deadline' ] = '';
		}

		$this->alert( 'Entry ' . $selected . ' loaded', ALERT_DEBUG );
		return true;
	}
	
	public function saveEntry( $entry )
	{
		if( ! $this->copyEntryFields( $entry ) )
		{
			$this->alert( 'Invalid entry submitted for storage', ALERT_ERROR );
			return false;
		}
		
		if( empty( $entry[ 'task_name' ] ) )
		{
			$this->alert( 'Tasks must have a name', ALERT_DENY );
			return false;
		}
		
		/*
		if( ! empty( $entry[ 'url' ] ) )
		{
			# Validate it
			$checkURL = $entry[ 'url' ];
			if( $this->validURL( $checkURL ) )
			{
				if( $checkURL != $entry[ 'url' ] )
				{
					$this->alert( 'Your website was adjusted to ' . htmlspecialchars( $checkURL ), ALERT_NOTE );
					$entry[ 'url' ] = $checkURL;
					$this->alert( 'Set entry to ' . htmlspecialchars( $entry[ 'url' ] ), ALERT_DEBUG );
				}
			} else {
				# I guess we have to save their nonsense
				$this->alert( 'Saving invalid URL ' . htmlspecialchars( $entry[ 'url' ] ), ALERT_DEBUG );
			}
		}
		$this->alert( 'Entry to save ' . htmlspecialchars( $entry[ 'url' ] ), ALERT_DEBUG );
		*/

		if( empty( $entry[ 'task_id' ] ) )
		{
			# Insert entry
#			$user_id = $_SESSION[ 'user_id' ];
			$query = $this->database->prepare( 'INSERT INTO ' . $this->taskTable
				. ' (' /*user_id, */ .'task_name, task_description, task_deadline, task_completed) VALUES (?, ?, ?, ?)' );
			$query->execute( array ( /*$_SESSION[ 'user_id' ],*/ $entry[ 'task_name' ],
				$entry[ 'task_description' ],
				$entry[ 'task_deadline' ] ? $entry[ 'task_deadline' ] : 'NULL',
				$entry[ 'task_completed' ] ? 1 : 0 ) );
			$_SESSION[ 'selected' ] = $this->selected = $this->database->lastInsertId();
			$this->alert( 'Created ' . htmlspecialchars( $entry[ 'task_name' ] ), ALERT_NOTE );
		} else {
			$task_id = $entry[ 'task_id' ];
			if( ! $this->entryIsMine( $task_id ) )
			{
				return false;
			}
			# Update entry
			$query = $this->database->prepare( 'UPDATE ' . $this->taskTable
				. ' SET task_name = ?, task_description = ?, task_deadline = ?, task_completed = ? WHERE task_id = ?' );
			$query->execute( array ( $entry[ 'task_name' ], $entry[ 'task_description' ],
				$entry[ 'task_deadline' ] ? $entry[ 'task_deadline' ] : 'NULL',
				$entry[ 'task_completed' ] ? 1 : 0, $task_id ) );
			$this->alert( 'Updated ' . htmlspecialchars( $entry[ 'task_name' ] ), ALERT_NOTE );
		}
		return true;
	}
	
	public function deleteEntry( $task_id )
	{
		if( ! $task_id )
		{
			$this->alert( 'Cannot delete entry 0', ALERT_ERROR );
			return false;
		}
		if( ! $this->entryIsMine( $task_id ) )
		{
			return false;
		}
		$query = $this->database->prepare( 'DELETE FROM ' . $this->taskTable . ' WHERE task_id = ?' );
		$query->execute( array ( $task_id ) );

		$this->alert( 'Deleted entry', ALERT_NOTE );
		return true;
	}
	
	public function editAction()
	{
		if( isset( $_POST[ 'edit' ] ) )
		{
			$this->alert( 'Edit action: ' . htmlspecialchars( $_POST[ 'edit' ] ), ALERT_DEBUG );

			# Checkbox(es)
			if( ! isset( $_POST[ 'task_completed' ] ) )
			{
				$_POST[ 'task_completed' ] = false;
			}
			
			$entry = $this->copyEntryFields( $_POST );
			if( ! $entry )
			{
				$this->alert( 'Entry data incomplete', ALERT_ERROR );
				return false;
			}

			$task_id = $entry[ 'task_id' ] = (int) $entry[ 'task_id' ];
			switch( $_POST[ 'edit' ] )
			{
				case ENTRY_CREATE:
					if( $task_id )
					{
						$this->alert( 'Entry ID should not be specified when creating', ALERT_ERROR );
					}
					$entry[ 'task_id' ] = 0;
					$this->saveEntry( $entry );
					break;

				case ENTRY_UPDATE:
					$this->saveEntry( $entry );
					break;

				case ENTRY_DELETE:
					if( ! $this->deleteEntry( $task_id ) )
					{
						return false;
					}
					$this->selected = 0;
					break;
/*
				case ENTRY_SHOW:
					if( $task_id && $this->entryChanged( $entry ) )
					{
						$this->alert( ERROR_UNSAVED_ENTRY, ALERT_ERROR );
					}
					$_SESSION[ 'show_password' ] = $this->showPassword = true;
					break;

				case ENTRY_HIDE:
					if( $task_id && $this->entryChanged( $entry ) )
					{
						$this->alert( ERROR_UNSAVED_ENTRY, ALERT_ERROR );
					}
					$_SESSION[ 'show_password' ] = $this->showPassword = false;
					break;

				case ENTRY_GO:
					# Check the database incase they changed it
					if( empty( $entry[ 'url' ] ) )
					{
						$this->alert( ERROR_NO_WEBSITE, ALERT_ERROR );
					} else
					{
						if( $task_id && $this->entryChanged( $entry ) )
						{
							$this->alert( ERROR_UNSAVED_ENTRY, ALERT_ERROR );
						}
						/*
						$urlChanged = $entry[ 'task_id' ]
								&& $this->loadEntry( $entry[ 'task_id' ] )
								&& ! empty( $this->entry[ 'url' ] )
								&& ( $this->entry[ 'url' ] != $entry[ 'url' ] );

						if( $urlChanged )
						{
							$this->alert( ERROR_UNSAVED_ENTRY, ALERT_ERROR );
							$this->alert( 'database url=' . htmlspecialchars( $this->entry[ 'url' ] ) . BR
								. 'rest url=' . htmlspecialchars( $entry[ 'url' ] ), ALERT_DEBUG );
						}
						* /
						if( $this->validURL( $entry[ 'url' ] ) )
						{
							$this->alert( 'Click [Go!] to open ' . htmlspecialchars( $entry[ 'url' ] ),
								ALERT_NOTE );
							# Populate text box with new one, so it visually matches ?
							$this->entry[ 'url' ] = $entry[ 'url' ];
							$this->urlLink = $entry[ 'url' ];
						}
					}
					break;
*/
				default:
					$this->alert( 'Invalid edit action requested', ALERT_ERROR );
					return false;
			}
		}
		return true;
	}

	public function actionTasklist()
	{
		# Logged into application
#		$this->alert( 'User ID ' . $_SESSION[ 'user_id' ], ALERT_DEBUG );

/*	 	Use these again when extending authentication
#		$this->content[ 'menu' ][ 'New entry' ] = 'new';
		$this->content[ 'menu' ][ $_SESSION[ 'user' ] ] = '';
		$this->content[ 'menu' ][ 'Change password' ] = 'change';
		$this->content[ 'menu' ][ 'Log out' ] = 'logout';
*/

		$this->selected = isset( $_POST[ 'selected' ] ) ? (int) $_POST[ 'selected' ] : 
			( isset( $_GET[ 'selected' ] ) ? (int) $_GET[ 'selected' ] :
			( isset( $_SESSION[ 'selected' ] ) ? (int) $_SESSION[ 'selected' ] : '' ) );

		$this->editAction();

		$search = '';
		if( ! isset( $_POST[ 'reset' ] ) )
		{
			$search = isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] :
				( isset( $_GET[ 'search' ] ) ? $_GET[ 'search' ] :
				( isset( $_SESSION[ 'search' ] ) ? $_SESSION[ 'search' ] : '' ) );
		}
		$_SESSION[ 'search' ] = $search;

		$this->loadEntries( $search );
#		$this->alert( 'Entries: ' . var_export( $this->entries, true ), ALERT_DEBUG );

		# Check selected is in list
		if( $this->selected )
		{
			$foundSelected = false;
			foreach( $this->entries as $entry )
			{
				if( $this->selected == $entry[ 'task_id' ] )
				{
					$foundSelected = true;
					break;
				}
			}
			if( ! $foundSelected )
			{
				$this->alert( 'Selected not in search results', ALERT_DEBUG );
				$this->selected = 0;
			}
		}

		$newEntry = isset( $_POST[ 'new' ] ) || isset( $_GET[ 'new' ] ) || ! $this->selected;

		$this->alert( 'Search: ' . htmlspecialchars( $search ) . ' Selected: '
			. (int) $this->selected, ALERT_DEBUG );

		# Could optimise to check if already loaded, but will load it twice if necessary
		if( $newEntry || ! $this->loadEntry( $this->selected ) )
		{
			$this->selected = 0;
			$newEntry = true;
			$this->entry = array_fill_keys( $this->fields, '' );
			$this->alert( 'New entry', ALERT_DEBUG );
		}
		$_SESSION[ 'selected' ] = $this->selected;

		$main = 
'		<form id="search-form" action="' . $this->content[ 'rel_path' ]
			. 'search" method="post" class="pure-form">
			<div class="textinput-bar">
				<input type="text" id="search" name="search" value="'
				. htmlspecialchars( $search ) . '" placeholder="Search" />
				<span class="nowrap">
					<input type="submit" id="search-button" value="Search" />
					<input type="submit" name="reset" value="X" />
				</span>
			</div>
		</form>
		<form id="selector-form" action="'
			. $this->content[ 'rel_path' ] . 'select" method="post" class="pure-form">
			<select id="selector" name="selected" size="5">
';

		foreach( $this->entries as $entry )
		{
#			$this->alert( 'Entry: ' . var_export( $entry, true ), ALERT_DEBUG );
			
			$task_id = $entry[ 'task_id' ];
			$task_name = $entry[ 'task_name' ];
			$main .= 
'					<option value="' . $task_id . '" title="' . $task_name . '"'
				. ( $task_id == $this->selected ? ' selected="selected"' : '' ) . '>'
				. $task_name . '</option>
';
		}

		/*
			Task id - hidden
			Task name - text
			Task description - textarea
			Deadline - calender widget
			Completed - checkbox
		*/

#		$this->alert( 'Entry: ' . var_export( $this->entry, true ), ALERT_DEBUG );
		
		$main .= 
'			</select>
			<div id="selector-buttons" class="button-bar">
				<input id="select-entry" type="submit" value="Select" />
				<input type="submit"' . ( ! (int) $this->selected ? ' class="hidden"' : '' )
				. ' name="new" value="New" />
			</div>
		</form>
		<form id="entry-form" action="' . $this->content[ 'rel_path' ]
			. 'edit" method="post" class="pure-form">
			<input type="hidden" name="task_id" value="' . $this->selected . '" />
			<label for="label">Task name: </label><span class="compress-field zero-button">
				<input type="text" id="label" name="task_name" value="'
					. $this->entry[ 'task_name' ] . '" autocomplete="off" />
				</span>
			<label for="deadline">Deadline: </label><span class="compress-field zero-button">
				<input type="text" class="datepicker" id="deadline" name="task_deadline" value="'
					. $this->entry[ 'task_deadline' ] . '" autocomplete="off" />
				</span>
			<label for="completed" class="checkradiolabel" >Completed: </label><span class="">
				<input type="checkbox" id="completed" name="task_completed" value="true"'
					. ( $this->entry[ 'task_completed' ] ? ' checked="checked"' : '' ) . ' />
				</span>
			<textarea id="description" name="task_description">' . $this->entry[ 'task_description' ] . '</textarea>
			<div class="button-bar">
				<input type="submit" name="edit" value="' . ( $newEntry ? ENTRY_CREATE : ENTRY_UPDATE )
				. '" />
				<input type="submit" name="edit" value="' . ENTRY_DELETE . '"' . ( $newEntry ? ' style="display: none"' : '' ) . ' />
			</div>
		</form>';

		$this->content[ 'main' ] = $main;
	}
}