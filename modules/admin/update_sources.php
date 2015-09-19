<?php
// Exit if not admin
if (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel()))
	die($LangUI->_('You must have Administer privilages in order to customize the database!'));

$mode = isset( $_POST['mode'] ) ? $_POST['mode'] : '';
$total_entries = isset( $_POST['total_entries'] ) ? $_POST['total_entries'] : 0;
$new_desc = isset( $_POST['new_desc'] ) ? $_POST['new_desc'] : '';
$new_text = isset( $_POST['new_text'] ) ? $_POST['new_text'] : '';

if (!$SMObj->isUserLoggedIn())
	die($LangUI->_('You must be logged into perform the requested action.'));

if ($mode == "add") {
	$sql = "INSERT INTO $db_table_sources (source_title, source_desc, source_user) VALUES (?,?,?)";
	$stmt = $DB_LINK->Prepare($sql);
	$rc = $DB_LINK->Execute($stmt, array($new_desc, $new_text, $SMObj->getUserID()));
	echo $LangUI->_('New Entry Added') . "<br />";
} else {
	$error = false;
	for ($i=0; $i<$total_entries; $i++) {
		$entry_delete = "delete_".$i;
		$entry_id = "entry_".$i;
		$entry_desc = "desc_".$i;
		$entry_text = "text_".$i;
		$source_id = $_POST[$entry_id];
		
		if ($source_id && !$SMObj->checkAccessLevel("EDITOR")) {
			// Figure out who the owner of this recipe is, Editors can edit anyones
			// The owner of does not change when someone edits it.
			$sql = "SELECT source_user FROM $db_table_sources WHERE source_id = ?";
			$stmt = $DB_LINK->Prepare($sql);
			$rc = $DB_LINK->Execute($stmt, array($source_id));
			// If the recipe is owned by someone else then do not allow editing
			if ($rc->fields['source_user'] != "" && $rc->fields['source_user'] != $SMObj->getUserID())
				die($LangUI->_('You are not the owner of this source, you are not allowed to edit it'));
		}

		if (isset($_POST[$entry_delete])) {
			// then delete it from the database
			$sql = "DELETE FROM $db_table_sources WHERE source_id=?";
			$stmt = $DB_LINK->Prepare($sql);
			$rc = $DB_LINK->Execute($stmt, array($source_id));
			DBUtils::checkResult($rc, NULL, NULL, $sql);
		} else {
			// update the entry to the new value
			$sql = "UPDATE $db_table_sources SET source_title=?, source_desc=? WHERE source_id=?";
			$stmt = $DB_LINK->Prepare($sql);
			$rc = $DB_LINK->Execute($stmt, array($_POST[$entry_desc], $_POST[$entry_text], $source_id));
			DBUtils::checkResult($rc, NULL, NULL, $sql);
		}
	}
	echo $LangUI->_('Sources Updated') . "<br />";
}
?>
