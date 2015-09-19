<?php
// Exit if not admin
if ($SMObj->getUserLoginID() == "")
	die($LangUI->_('Not authorized!'));

$startday = (isset( $_POST['startday'] ) && is_numeric($_POST['startday'] )) ? $_POST['startday'] : '';

// update the entry to the new value
$sql = "UPDATE $db_table_settings SET setting_value='" . $DB_LINK->addq($startday, get_magic_quotes_gpc())  . "' 
	WHERE setting_name='MealPlanStartDay' AND setting_user = '" . $SMObj->getUserID() . "'";
$result = $DB_LINK->Execute($sql);
DBUtils::checkResult($result, NULL, NULL, $sql);
echo $LangUI->_('Meal Planner Settings Updated') . "<br />";
?>
