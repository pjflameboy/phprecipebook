<?php
require_once("classes/DBUtils.class.php");

$searchText = isset ($_GET['term'] ) ? $_GET['term'] : ".*";
$searchLimit = 100;//isset ($_GET['limit'] ) ? $_GET['limit'] : 100;

$count = 0;
$sResult = "";

$sql = "SELECT ingredient_name, ingredient_id FROM $db_table_ingredients " . 
	"WHERE ingredient_name LIKE '%" . $DB_LINK->addq($searchText, get_magic_quotes_gpc()) . "%' AND " .
	"ingredient_user = '" . $DB_LINK->addq($SMObj->getUserID(), get_magic_quotes_gpc()) ."' ORDER BY ingredient_name";

$ingredients = $DB_LINK->Execute($sql);

$searchResults = array();

while (!$ingredients->EOF) 
{
	$key = $ingredients->fields['ingredient_name'];
	$value = $ingredients->fields['ingredient_id'];
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

