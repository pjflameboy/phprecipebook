<?php
require_once("classes/Ingredient.class.php");

$ingredient_id = isValidID( $_GET['ingredient_id'] ) ? $_GET['ingredient_id'] : 0;
$coreIngredient_id = isValidID( $_POST['coreIngredient_id'] ) ? $_POST['coreIngredient_id'] : 0;
$ingredient_name = isset( $_POST['ingredient_name'] ) ?
	htmlentities( stripslashes( $_POST['ingredient_name'] ), ENT_QUOTES, $LangUI->getEncoding() ) : '';
$ingredient_desc = isset( $_POST['ingredient_desc'] ) ?
	htmlentities( stripslashes( $_POST['ingredient_desc'] ), ENT_QUOTES, $LangUI->getEncoding()) : '';
$ingredient_loc = ($_POST['ingredient_loc'] != "") ? $_POST['ingredient_loc'] : 'NULL';
$ingredient_unit = ($_POST['ingredient_unit'] != "") ? $_POST['ingredient_unit'] : 'NULL';
$ingredient_solid = ($_POST['ingredient_solid'] == "TRUE") ? "TRUE" : "FALSE";

if (!$SMObj->isUserLoggedIn())
	die($LangUI->_('You must be logged into perform the requested action.'));

// Load the Ingredient into an ingredient object
$ingObj = new Ingredient();
$ingObj->setIngredient($ingredient_id,
					   $coreIngredient_id,
					   $ingredient_name,
					   $ingredient_desc,
					   $ingredient_unit,
					   $ingredient_loc,
					   $ingredient_solid,
					   $SMObj->getUserID());

// Add or Update the ingredient in the database
$ingObj->addUpdate();

?>

