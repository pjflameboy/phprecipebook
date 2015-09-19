<?php
require_once("classes/DBUtils.class.php");

$recipe_id = (isValidID($_REQUEST['recipe_id'])) ? $_REQUEST['recipe_id'] : 0;
$review = (isset($_REQUEST['review'])) ? htmlentities($_REQUEST['review'], ENT_QUOTES, $LangUI->getEncoding()) : '';
$rating = (isset($_REQUEST['rating']) && is_numeric($_REQUEST['rating'])) ? $_REQUEST['rating'] : 0;
$userId = $SMObj->getUserID();
$ip = $_SERVER['REMOTE_ADDR'];

if ($SMObj->IsUserLoggedIn() && $review != '') {
	$sql = "INSERT INTO $db_table_reviews (review_recipe, review_comments, review_user) VALUES (?,?,?)";
	$stmt = $DB_LINK->Prepare($sql);
	$rc = $DB_LINK->Execute($stmt, array($recipe_id, $review, $userId));
	DBUtils::checkResult($rc, $LangUI->_('Review submitted successfully'), $LangUI->_('Failed to save review!'), $sql);
}
if ($rating && $ip != '') {
	$sql = "INSERT INTO $db_table_ratings (rating_recipe, rating_score, rating_ip) VALUES (?,?,?)";
	$stmt = $DB_LINK->Prepare($sql);
	$rc = $DB_LINK->Execute($stmt, array($recipe_id, $rating, $ip));
	DBUtils::checkResult($rc, $LangUI->_('Rating submitted successfully'), $LangUI->_('You have already rated this recipe!'), NULL);
}
?>