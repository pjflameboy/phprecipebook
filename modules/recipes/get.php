<?php
require_once("classes/DBUtils.class.php");

$searchText = isset ($_GET['term'] ) ? $_GET['term'] : ".*";
$searchLimit = 100;//isset ($_GET['limit'] ) ? $_GET['limit'] : 100;

$count = 0;
$sResult = "";

$sql = "SELECT recipe_name,recipe_id  FROM $db_table_recipes " .
	"WHERE recipe_name LIKE '%" . $DB_LINK->addq($searchText, get_magic_quotes_gpc()) . "%' AND " .
	"(recipe_private = 0 OR recipe_user = " . $SMObj->getUserID() . ") ORDER By recipe_name";
$recipes = $DB_LINK->Execute($sql);

$searchResults = array();

while (!$recipes->EOF) 
{
	$key = $recipes->fields['recipe_name'];
	$value = $recipes->fields['recipe_id'];
	array_push($searchResults, array("id"=>$value, "label"=>$key, "value" => strip_tags($key)));
	if (count($searchResults) >= $searchLimit) break;
    $recipes->MoveNext();
}

// return a friendly no-found message
if (count($searchResults) == 0)
{
	$key = "No Results for '$searchText' Found";
	$value = "";
	array_push($searchResults, array("id"=>$value, "label"=>$key, "value" => strip_tags($key)));
	$searchResults[] = "";
}

echo array2json($searchResults);

?>