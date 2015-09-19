<?php 
$user = $SMObj->getUserID();

// Pull First Letters
$let = ":";
$sql = "SELECT DISTINCT LOWER(SUBSTRING(recipe_name, 1, 1)) AS A FROM $db_table_recipes";
if (isset($user))
{
	$sql .= " WHERE recipe_user = '$user' ";
}
else
{
	$sql .= " WHERE recipe_private = 0";	
}
$rc = $DB_LINK->Execute( $sql );
DBUtils::checkResult($rc, NULL, NULL, $sql);

while (!$rc->EOF) {
	if (ord($rc->fields[0]) >= 192 and ord($rc->fields[0]) <= 222 and ord($rc->fields[0]) != 215) // "Select lower"
		$rc->fields[0] = chr(ord($rc->fields[0])+32); // above doesn't work with ascii > 192, this fixes it
	$let .= $rc->fields[0]; // it could be "a" or "A", so just go with the only returned item
	$rc->MoveNext();
}
?>
	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
		<li data-role="list-divider">A-Z Recipes</li>
		<li><a href="./index.php?m=recipes&a=list_mob">All</a></li>
		<?php
        for ($a=0; isset($alphabet[$a]); $a++) { // List the alphabet
                $cu = $alphabet[$a];
                $cl = chr( ord( $cu )+32 );
               	if (strpos($let, "$cl") > 0)
               	{
               		echo "<li><a href=\"./index.php?m=recipes&a=list_mob&where=$cu\">$cu</a></li>";
               	}
          }
		?>
	</ul>	

