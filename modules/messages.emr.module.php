<?php
 // $Id$
 //
 // Authors:
 // 	Jeff Buchbinder <jeff@freemedsoftware.org>
 //
 // Copyright (C) 1999-2006 FreeMED Software Foundation
 //
 // This program is free software; you can redistribute it and/or modify
 // it under the terms of the GNU General Public License as published by
 // the Free Software Foundation; either version 2 of the License, or
 // (at your option) any later version.
 //
 // This program is distributed in the hope that it will be useful,
 // but WITHOUT ANY WARRANTY; without even the implied warranty of
 // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 // GNU General Public License for more details.
 //
 // You should have received a copy of the GNU General Public License
 // along with this program; if not, write to the Free Software
 // Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

LoadObjectDependency('org.freemedsoftware.core.EMRModule');

class MessagesTable extends EMRModule {

	var $MODULE_NAME = "Messages";
	var $MODULE_VERSION = "0.8.0";
	var $MODULE_FILE = __FILE__;
	var $MODULE_UID = "03633733-9ec0-4535-b233-83a1686318ff";
	var $MODULE_HIDDEN = true;

	var $PACKAGE_MINIMUM_VERSION = '0.8.0';

	var $table_name    = "messages";
	var $patient_field = "msgpatient";
	var $order_by      = "msgunique DESC";
	var $widget_hash   = "##msgsubject## [##msgtime##]";

	public function __construct () {
		// Set configuration stuff
	/*
		$this->_SetMetaInformation('global_config_vars', array(
			'message_delete'
		));
		$this->_SetMetaInformation('global_config', array(
			__("Allow Direct Message Deletion") =>
			'html_form::select_widget("message_delete", '.
				'array( '.
					'"'.__("Disable").'" => "0", '.
					'"'.__("Enable").'" => "1" '.
				')'.
			')'
		));
	*/

		// Add main menu handler item
		$this->_SetHandler('MainMenu', 'UnreadMessages');

		// Call parent constructor
		parent::__construct();
	} // end constructor MessagesTable

	function UnreadMessages ( ) {
		// Ask the API how many messages we have
		$m = CreateObject('org.freemedsoftware.api.Messages');
		if (!$m->view_per_user(true)) { return false; }
		if (($c = count($m->view_per_user(true))) > 0) {
			return array (
				__("Unread Messages"),
				sprintf(__("You have %s unread messages."), $c).
				" <a href=\"messages.php\">[".__("View")."]</a>",
				"img/envelope_icon.png"
			);
		} else {
			// Don't show up if there are no unread messages
			return false;
		}
	} // end method UnreadMessages

	protected function additional_move ( $id, $from, $to ) {
		$r = freemed::get_link_rec($id, $this->table_name);
		$q = $GLOBALS['sql']->update_query(
			$this->table_name,
			array ( 'msgpatient' => $to ),
			array ( 'msgunique' => $r['msgunique'] )
		);
		if ($r['msgunique'] > 0) {
			syslog(LOG_INFO, "Messages| moved messages with msgunique of ".$r['msgunique']." to $to");
			$result = $GLOBALS['sql']->query($q);
			if (!$result) { return false; }			
		}
		return true;
	} // end method additional_move

} // end class MessagesTable

register_module('MessagesTable');

?>
