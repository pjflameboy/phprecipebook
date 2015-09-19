<?php
require_once("classes/Restaurant.class.php");
require_once("classes/DBUtils.class.php");

$restaurant_id = isValidID( $_GET['restaurant_id'] ) ? $_GET['restaurant_id'] : 0;

if ($restaurant_id && !$SMObj->checkAccessLevel("EDITOR")) {
	// Figure out who the owner of this restaurant is, Editors can edit anyones items
	$sql = "SELECT restaurant_user FROM $db_table_restaurants WHERE restaurant_id = " . $DB_LINK->addq($restaurant_id, get_magic_quotes_gpc());
	$rc = $DB_LINK->Execute($sql);
	// If the recipe is owned by someone else then do not allow editing
	if ($rc->fields['restaurant_user'] != "" && $rc->fields['restaurant_user'] != $SMObj->getUserID())
		die($LangUI->_('You are not the owner of this restaurant, you are not allowed to delete it'));
}

// clean up the old picture if we are suppose to
if ($g_rb_database_type=="postgres") {
	$sql = "SELECT restaurant_picture FROM $db_table_restaurants WHERE restaurant_id=" . $DB_LINK->addq($restaurant_id, get_magic_quotes_gpc());
	$rc = $DB_LINK->Execute($sql);
	if (trim($rc->fields['restaurant_picture']) != "") {
		$rc = $DB_LINK->BlobDelete($rc->fields['restaurant_picture']);
		DBUtils::checkResult($rc, $LangUI->_('Picture successfully deleted'), NULL, $sql);
	}
}

// In Postgres everything will be cleaned up with one delete
$RestaurantObj = new Restaurant($restaurant_id);
$RestaurantObj->delete();
?>
<I><?php echo $LangUI->_('Restaurant Deleted');?></I>
<P>