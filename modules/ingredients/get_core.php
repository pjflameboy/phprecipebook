<?php
require_once("classes/DBUtils.class.php");

if (!$SMObj->isUserLoggedIn())
	die($LangUI->_('You must be logged into perform the requested action.'));

$searchText = isset ($_GET['term'] ) ? $_GET['term'] : ".*";
$searchLimit = 100;//isset ($_GET['limit'] ) ? $_GET['limit'] : 100;

$count = 0;
$sResult = "";

$sql = "SELECT C.Id as core_id, C.description FROM $db_table_core_ingredients C WHERE C.description LIKE '%" . $DB_LINK->addq($searchText, get_magic_quotes_gpc()) . "%' 
	   AND NOT EXISTS (SELECT ingredient_core FROM recipe_ingredients WHERE ingredient_user = " . $SMObj->getUserID() . " AND C.Id = ingredient_core);";
$ingredients = $DB_LINK->Execute($sql);

$searchResults = array();

while (!$ingredients->EOF) 
{
	$key = $ingredients->fields['description'];
	$value = $ingredients->fields['core_id'];
	array_push($searchResults, array("id"=>$value, "label"=>$key, "value" => strip_tags($key)));
	if (count($searchResults) >= $searchLimit) break;
    $ingredients->MoveNext();
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

