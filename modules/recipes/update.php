<?php
require_once("classes/Recipe.class.php");
require_once("classes/Ingredient.class.php");
require_once("classes/DBUtils.class.php");

$recipe_id = isValidID( $_GET['recipe_id'] ) ? $_GET['recipe_id'] : 0;
$recipe_name = isset( $_POST['recipe_name'] ) ?
	htmlspecialchars( stripslashes( $_POST['recipe_name'] ), ENT_QUOTES, $LangUI->getEncoding() ) : '';
$recipe_ethnic = isset( $_POST['recipe_ethnic'] ) ? $_POST['recipe_ethnic'] : 0;
$recipe_base = isValidID( $_POST['recipe_base'] ) ? $_POST['recipe_base'] : 0;
$recipe_course = isValidID( $_POST['recipe_course'] ) ? $_POST['recipe_course'] : 0;
$recipe_prep_time = isValidID( $_POST['recipe_prep_time'] ) ? $_POST['recipe_prep_time'] : 0;
$recipe_difficulty = isValidID( $_POST['recipe_difficulty'] ) ? $_POST['recipe_difficulty'] : 0;
$recipe_directions = isset( $_POST['recipe_directions'] ) ?
	htmlspecialchars( stripslashes( $_POST['recipe_directions'] ), ENT_QUOTES, $LangUI->getEncoding() ) : '';
$recipe_comments = isset( $_POST['recipe_comments'] ) ?
	htmlspecialchars( stripslashes( $_POST['recipe_comments'] ), ENT_QUOTES, $LangUI->getEncoding() ) : '';
$recipe_source = isset( $_POST['recipe_source'] ) ? $_POST['recipe_source'] : '';
$recipe_source_desc = isset( $_POST['recipe_source_desc'] ) ?
	htmlspecialchars( stripslashes( $_POST['recipe_source_desc'] ), ENT_QUOTES, $LangUI->getEncoding() ) : '';
$recipe_serving_size = ($_POST['recipe_serving_size'] != "" ) ? $_POST['recipe_serving_size'] : 'NULL';
$recipe_private = isset($_POST['private']) ? 'TRUE' : 'FALSE';
$recipe_picture_oid = isset($_POST['recipe_picture_oid']) ? $_POST['recipe_picture_oid'] : 'NULL'; // to keep postgres clean
$recipe_picture_type = isset($_FILES['recipe_picture']['type']) ? $_FILES['recipe_picture']['type'] : '';
$remove_picture = isset($_POST['remove_picture']) ? $_POST['remove_picture'] : '';
$resize_picture = isset($_POST['resize_picture']) ? $_POST['resize_picture'] : 'no';

if (!$SMObj->isUserLoggedIn())
	die($LangUI->_('You must be logged into perform the requested action.'));

if ($recipe_id && !$SMObj->checkAccessLevel("EDITOR")) {
	// Figure out who the owner of this recipe is, Editors can edit anyones recipes
	// The owner of a recipe does not change when someone edits it.
	$sql = "SELECT recipe_user FROM $db_table_recipes WHERE recipe_id = " . $DB_LINK->addq($recipe_id, get_magic_quotes_gpc());
	$rc = $DB_LINK->Execute($sql);
	// If the recipe is owned by someone else then do not allow editing
	if ($rc->fields['recipe_user'] != "" && $rc->fields['recipe_user'] != $SMObj->getUserID())
		die($LangUI->_('You are not the owner of this recipe, you are not allowed to edit it'));
}

$count = 0;
$i = 0;
$ingArray = array();

while (isset($_POST['ingredientId_'.$i]))
{
	// Set if the ingredient is optional or not
	if (isset($_POST['ingredientOptional_'.$i])) $optional = "TRUE";
	else $optional="FALSE";

	// Now add the updated/new recipes in.
	if (isset($_POST['ingredientId_'.$i]) && 
		($_POST['ingredientQuantity_'.$i] > 0) && 
	    isset($_POST['ingredientUnit_'.$i]) && 
	    $_POST['ingredientId_'.$i] != "") 
	{
		$ingObj = new Ingredient();
		$ingObj->setIngredientMap($_POST['ingredientId_'.$i],
									$recipe_id,
									$_POST['ingredientQualifier_'.$i],
									$_POST['ingredientQuantity_'.$i],
									$_POST['ingredientUnit_'.$i],
									$optional,
									$count);
		$count++; // keep track of which number we are on (for ordering)
		$ingArray[] = $ingObj; //Add the object to the list
	}
	$i++;
}

/*
	Handle adding and editing of recipes
*/
$recipeObj = new Recipe($recipe_id,
						$recipe_name,
						$recipe_ethnic,
						$recipe_base,
						$recipe_course,
						$recipe_prep_time,
						$recipe_difficulty,
						$recipe_directions,
						$recipe_comments,
						$recipe_serving_size,
						$recipe_source,
						$recipe_source_desc,
						$SMObj->getUserID(),
						$recipe_private,
						$_FILES['recipe_picture'],
						$recipe_picture_type,
						$recipe_picture_oid);
// Add or update the recipe
$recipeObj->addUpdate();
// Handle the picture
if ($remove_picture=="yes") {
	$recipeObj->deletePicture();
} 
else 
{
	$recipeObj->updatePicture($resize_picture);
}

if ($recipe_id) {
	// Clear out the old ingredients, this could be done by an update if desired.
	$sql = "DELETE FROM $db_table_ingredientmaps WHERE map_recipe=" . $DB_LINK->addq($recipe_id, get_magic_quotes_gpc());
	$result = $DB_LINK->Execute($sql);
	// Also clear out the related_recipes
	$sql = "DELETE FROM $db_table_related_recipes WHERE related_parent=" . $DB_LINK->addq($recipe_id, get_magic_quotes_gpc());
	$result = $DB_LINK->Execute($sql);
}

$recipe_id = $recipeObj->getID();

/*
	Add the ingredients into the database. The order field is needed because mysql does not consistently put them in or retrieve them
		in a specific order.
*/
foreach ($ingArray as $ing) {
	$ing->setID($recipe_id);
	$ing->insertMap();
}

// Add all the related recipes in.
$i = 0;
while (isset($_POST['relatedId_'.$i]) && isValidID($_POST['relatedId_'.$i]))
{
	if (isset($_POST['relatedRequired_'.$i]))
	{
		$required = $DB_LINK->true;
	}
	else
	{
		$required = $DB_LINK->false;
	}

	$sql="INSERT INTO $db_table_related_recipes (related_parent, related_child, related_required, related_order) VALUES (" . $DB_LINK->addq($recipe_id, get_magic_quotes_gpc()) . ", " .
		$DB_LINK->addq($_POST['relatedId_'.$i], get_magic_quotes_gpc()) . ", '" . $required . "', " . $i . ")";
	$rc = $DB_LINK->Execute($sql);
	DBUtils::checkResult($rc, NULL, NULL, $sql);
	$i++;
}
?>

