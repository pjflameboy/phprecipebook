<?php if ($format == "yes") { 
	$pageTitle = $g_rb_project_name; // Default Title
	$recipe_id = (isset($_GET['recipe_id']) && isValidID( $_GET['recipe_id'] )) ? $_GET['recipe_id'] : 0;
	$ingredient_id = (isset($_GET['ingredient_id']) && isValidID( $_GET['ingredient_id'] )) ? $_GET['ingredient_id'] : 0;

	// Set Title for Recipes and Ingredients
	if ($recipe_id != 0) { 
		$sql= "SELECT recipe_name FROM $db_table_recipes WHERE recipe_id=?"; 
		$stmt = $DB_LINK->Prepare($sql);
		$rc = $DB_LINK->Execute($stmt, array($recipe_id)); 
		$pageTitle = $g_rb_project_name . " - " . $LangUI->_("Recipes") . " - " .  $rc->fields['recipe_name'];
	}
	else if ($ingredient_id != 0)
	{
		$sql= "SELECT ingredient_name FROM $db_table_ingredients WHERE ingredient_id=?"; 
		$stmt = $DB_LINK->Prepare($sql);
		$rc = $DB_LINK->Execute($stmt, array($ingredient_id));
		$pageTitle = $g_rb_project_name . " - " . $LangUI->_("Ingredients") . " - " . $rc->fields['ingredient_name'];
	}
	?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $LangUI->getEncoding();?>"/>
	<title><?php echo $pageTitle; ?></title>
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/redmond/jquery-ui.css" type="text/css" media="screen">
	<link rel="stylesheet" href="themes/<?php echo $g_rb_theme;?>/style.css" type="text/css" />
	<link rel="stylesheet" href="libs/jQuery-Menu/fg.menu.css" type="text/css" media="screen"  />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
	<script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>
	<script type="text/javascript" src="libs/jQuery-Menu/fg.menu.js"></script>
	<script type="text/javascript" src="libs/jquery.ui.autocomplete.html.js"></script>
</head>

<?php
require_once("classes/DBUtils.class.php");

// If we are print mode, then do not do most of the header
if ($print == "no") {
	include("includes/menu_items.php");
?>

<body>

<script type="text/javascript">
$(document).ready(function() {
     // BUTTONS
     $('.fg-button').hover(
     function(){ $(this).removeClass('ui-state-default').addClass('ui-state-focus'); },
     function(){ $(this).removeClass('ui-state-focus').addClass('ui-state-default'); }
     );
});
</script>

<div class="topnav">
	<div style="width: 200px; float: left;">
		<img src="./themes/<?php echo $g_rb_theme;?>/images/logo.png"/>
	</div>
	<div class="loginbox <?php echo ($SMObj->isUserLoggedIn() ? "loggedinbox" : "")?>">
		<?php if ($SMObj->isSecureLogin())
		{
			include("includes/login.php");
		} ?>
	</div>
	<br/>
	<div class="menuLevelOneBar <?php echo ($SMObj->isUserLoggedIn() ? "menuLevelOneLoggedIn" : "")?>">
		<?php printMenu($menu_items); ?>
	</div>
</div>
<div style="clear:both;"></div>
<div>
	<div class="sidenav">

	<?php if ($SMObj->isUserLoggedIn()) { ?>
		<b><a href="index.php?m=recipes&amp;a=addedit" >[<?php echo $LangUI->_('Add Recipe'); ?>]</a></b>
	<?php }	else { ?>
		<img src="./themes/<?php echo $g_rb_theme;?>/images/pixel.png" width="122" height="1" border="0" alt="" align="top" />
	<?php } ?>
	
	<br/><b><?php echo $LangUI->_('Course');?>:</b>
	<?php
	if (!$SMObj->isUserLoggedIn())
	{
		// Course with public recipe count
		$sql = "SELECT course_id, course_desc, 
			(SELECT COUNT(*) from $db_table_recipes 
				WHERE recipe_course = course_id and recipe_private=0) as count 
			FROM $db_table_courses ORDER BY course_desc";
	}
	else
	{
		// Courses with this users recipe count
		$sql = "SELECT course_id, course_desc, 
			(SELECT COUNT(*) from $db_table_recipes 
				WHERE recipe_course = course_id and recipe_user='" . $SMObj->getUserID() . "') as count 
			FROM $db_table_courses ORDER BY course_desc";
	}
	$rc = $DB_LINK->Execute( $sql );
	DBUtils::checkResult($rc, NULL, NULL, $sql);
	
	while (!$rc->EOF) {
		echo '<br /><a href="index.php?m=recipes&amp;a=search&amp;search=yes&amp;course_id='.$rc->fields['course_id'].'">'.$rc->fields['course_desc'].'</a>';
		if (isset($rc->fields['count']) && $rc->fields['count'] > 0)
		{
			echo '&nbsp;&nbsp;('. $rc->fields['count'] . ')';
		};
		$rc->MoveNext();
	}
	?>
	<br/><br/><b><?php echo $LangUI->_('Base');?>:</b>
	
	<?php
		if (!$SMObj->isUserLoggedIn())
	{
		// Course with public recipe count
		$sql = "SELECT base_id, base_desc, 
			(SELECT COUNT(*) from $db_table_recipes 
				WHERE recipe_base = base_id and recipe_private=0) as count 
			FROM $db_table_bases ORDER BY base_desc";
	}
	else
	{
		// Courses with this users recipe count
		$sql = "SELECT base_id, base_desc, 
			(SELECT COUNT(*) from $db_table_recipes 
				WHERE recipe_base = base_id and recipe_user='" . $SMObj->getUserID() . "') as count 
			FROM $db_table_bases ORDER BY base_desc";
	}
	//$sql = "SELECT base_id,base_desc FROM $db_table_bases ORDER BY base_desc";
	$rc = $DB_LINK->Execute( $sql );
	DBUtils::checkResult($rc, NULL, NULL, $sql);
	
	while (!$rc->EOF) {
		echo '<br /><a href="index.php?m=recipes&amp;a=search&amp;search=yes&amp;base_id='.$rc->fields['base_id'].'">'.$rc->fields['base_desc'].'</a>';
		if (isset($rc->fields['count']) && $rc->fields['count'] > 0) {
			echo '&nbsp;&nbsp;('. $rc->fields['count'] . ')';
		}
		$rc->MoveNext();
	}
	?>
	<br/><br/><br/>
	<br/><br/><br/>
	<a href="http://<?php echo $g_rb_project_website;?>"><?php echo $g_rb_project_name . " " . $g_rb_project_version;?></a>
	</div>
	
	<div class="mainContent">
		<?php 
		// Print out a submenu for the user to navigate
		printSubMenu($menu_items);
		} ?>
<?php } // end no format
?>

