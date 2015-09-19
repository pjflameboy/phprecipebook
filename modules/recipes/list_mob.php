<?php 
$where = (isset($_GET['where']) && isValidLetter($_GET['where'], "%" )) ? $_GET['where'] : '';
$search = ($where == "") ? "All" : $_GET['where'];

$sql = "SELECT recipe_id,recipe_name FROM $db_table_recipes WHERE recipe_name LIKE '".$DB_LINK->addq($where, get_magic_quotes_gpc())."%' OR recipe_name LIKE '"
    . strtolower($DB_LINK->addq($where, get_magic_quotes_gpc())) . "%' ORDER BY recipe_name";

$rc = $DB_LINK->Execute( $sql );

?>
<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
	<li data-role="list-divider">Recipes (<?php echo $search;?>)</li>
<?php 
while (!$rc->EOF) {
	echo '<li><a href="./index.php?m=recipes&amp;a=view_mob&recipe_id='.$rc->fields['recipe_id'].'">'.$rc->fields['recipe_name'].'</a></li>';
	$rc->MoveNext();
}
?>
</ul>