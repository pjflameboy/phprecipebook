<?php
require_once("classes/Units.class.php");
require_once("classes/DBUtils.class.php");

$recipe_id = (isset($_GET['recipe_id']) && isValidID( $_GET['recipe_id'] )) ? $_GET['recipe_id'] : 0;
$total_ingredients = (isset($_GET['total_ingredients']) && isValidID( $_REQUEST['total_ingredients']) ) ? $_REQUEST['total_ingredients'] : 0;
$total_related = (isset($_GET['total_related']) && isValidID($_REQUEST['total_related'])) ? $_REQUEST['total_related'] : 0;
$show_ingredient_ordering = isset($_REQUEST['show_ingredient_order'] ) ? 'yes' : '';
$private = isset($_REQUEST['private']) ? TRUE : FALSE;

// Declarations
$n = 0;
$p = 0;
$ingredients = null;

if ($g_rb_debug) $show_ingredient_ordering = "yes";

// Determine if the user has access to add new recipes, or edit this current one
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

// get the information about the recipe (empty query if new recipe)
if ($recipe_id) 
{
	$sql = "SELECT *
			FROM $db_table_recipes
			WHERE recipe_id = " . $DB_LINK->addq($recipe_id, get_magic_quotes_gpc());
	$rc = $DB_LINK->Execute( $sql );
	$recipe["recipe_name"] = $rc->fields["recipe_name"];
	$recipe["recipe_ethnic"] = $rc->fields["recipe_ethnic"];
	$recipe["recipe_base"] = $rc->fields["recipe_base"];
	$recipe["recipe_course"] = $rc->fields["recipe_course"];
	$recipe["recipe_prep_time"] = $rc->fields["recipe_prep_time"];
	$recipe["recipe_difficulty"] = $rc->fields["recipe_difficulty"];
	$recipe["recipe_directions"] = $rc->fields["recipe_directions"];
	$recipe["recipe_comments"] = $rc->fields["recipe_comments"];
	$recipe["recipe_source"] = $rc->fields["recipe_source"];
	$recipe["recipe_source_desc"] = $rc->fields["recipe_source_desc"];
	$recipe["recipe_serving_size"] = $rc->fields["recipe_serving_size"];
	// For PostgreSQL we pass around the Object ID of the picture
	if ($g_rb_database_type == "postgres")
		$recipe["recipe_picture_oid"] = $rc->fields["recipe_picture"];

	$recipe["recipe_picture_type"] = $rc->fields["recipe_picture_type"];

	if ($rc->fields['recipe_private'] == $DB_LINK->true) $private = true;
	else $private = false;
}
?>

<script type="text/javascript" src="./modules/recipes/addedit.js"></script>
<script type="text/javascript">
	var numberErrorHtml = '<?php echo $LangUI->_('Please enter Numbers');?>';
	var validationErrorsHtml = '<?php echo $LangUI->_('Required fields are missing, please correct and try again');?>';
	var recipeServingSizeErrorHtml = '<li><?php echo $LangUI->_('Enter a valid Serving Size');?></li>';
	var ingredientDupeErrorHtml = '<li><?php echo $LangUI->_('Ingredients cannot be entered more then once per recipe.  Please combine the quantities or create a new recipe and list it as required recipe.');?></li>';
	var relatedRecipeDupeErrorHtml = '<li><?php echo $LangUI->_('The same related recipe cannot be added twice.  Please remove duplicate related recipes.');?></li>';
	$(document).ready(function() {
		<?php if ($recipe_id) {	?>
		$('#availableActions ul').append('<li><a href="./index.php?m=recipes&a=view&recipe_id=<?php echo $recipe_id;?>"><?php echo $LangUI->_('View Recipe');?></a></li>');
		$('#availableActions ul').append('<li><a href="#" onClick="return confirmDelete(); return false;"><?php echo $LangUI->_('Delete Recipe');?></a></li>');
		<?php } ?>
	});

	function confirmDelete() {
		if (confirm("<?php echo $LangUI->_('Are you sure you wish to delete this ingredient?');?>"))
		{
			window.location = 'index.php?m=recipes&a=search&dosql=delete&recipe_id_0=<?php echo $recipe_id;?>&recipe_selected_0=yes';
		}
		return false;
	}
</script>
<div class="clear"/>
<?php if ($recipe_id) { ?>
	<h3>
		<a href="./index.php?m=recipes&amp;a=view&amp;recipe_id=<?php echo $recipe_id;?>"><?php echo $LangUI->_('View Recipe');?></a>
	</h3>
<?php } ?>

<form id="recipeAddEditForm" name="recipe_form" enctype="multipart/form-data" action="./index.php?m=recipes&a=addedit&recipe_id=<?php echo $recipe_id;?>" method="post">
<input type="hidden" id="dosql" name="dosql" value="">

<fieldset>
<legend><?php echo $LangUI->_('Details');?></legend>
<table  cellspacing="1" cellpadding="2" border="0" class="data">
<tr>
	<td><?php echo $LangUI->_('Recipe Name');?>:<?php echo getHelpLink("dish_name");?></td>
	<td><input type="text" size="40" class="required" name="recipe_name" id="recipe_name" value="<?php echo (isset($recipe["recipe_name"]) ? $recipe["recipe_name"] : "");?>"></td>
</tr>
<tr>
	<td><?php echo $LangUI->_('Source');?>:<?php echo getHelpLink("source");?></td>
	<td>
<?php
	$rc = DBUtils::fetchColumn( $db_table_sources, 'source_title', 'source_id', 'source_title' );
	echo $rc->getMenu2('recipe_source', (isset($recipe['recipe_source']) ? $recipe['recipe_source'] : ""), true);
?>
	<a href="./index.php?m=admin&a=sources">Edit</a>
	</td></tr>
<tr>
	<td><?php echo $LangUI->_('Source Description');?>:<?php echo getHelpLink("source_desc");?></td>
	<td><input type="text" name="recipe_source_desc" size="40" value="<?php echo (isset($recipe["recipe_source_desc"]) ? $recipe["recipe_source_desc"] : "");?>"></td>
</tr>
<tr>
	<td><?php echo $LangUI->_('Course');?>:<?php echo getHelpLink("course");?></td>
	<td>
<?php
	$rc = DBUtils::fetchColumn( $db_table_courses, 'course_desc', 'course_id', 'course_desc' );
	echo $rc->getMenu2('recipe_course', (isset($recipe['recipe_course']) ? $recipe['recipe_course'] : ""), false);
?>
	</td>
</tr>

<tr>
	<td><?php echo $LangUI->_('Base');?>:<?php echo getHelpLink("base");?></td>
	<td>
<?php
	$rc = DBUtils::fetchColumn( $db_table_bases, 'base_desc', 'base_id', 'base_desc' );
	echo $rc->getMenu2('recipe_base', (isset($recipe['recipe_base']) ? $recipe['recipe_base'] : ""), false);
?>
	</td>
</tr>
<tr>
	<td><?php echo $LangUI->_('Ethnicity');?>:<?php echo getHelpLink("ethnicity");?></td>
	<td>
<?php
	$rc = DBUtils::fetchColumn( $db_table_ethnicity, 'ethnic_desc', 'ethnic_id', 'ethnic_desc' );
	echo $rc->getMenu2('recipe_ethnic', (isset($recipe['recipe_ethnic']) ? $recipe['recipe_ethnic'] : ""), false);
?>
	</td>
</tr>

<tr>
	<td><?php echo $LangUI->_('Preparation Time');?>:<?php echo getHelpLink("prep_time");?></td>
	<td>
<?php
	$rc = DBUtils::fetchColumn( $db_table_prep_time, 'time_desc', 'time_id', 'time_desc' );
	echo $rc->getMenu2('recipe_prep_time', (isset($recipe['recipe_prep_time']) ? $recipe['recipe_prep_time'] : ""), false);
?>
	</td>
</tr>
<tr>
	<td><?php echo $LangUI->_('Difficulty');?>:<?php echo getHelpLink("difficulty");?></td>
	<td>
<?php
	$rc = DBUtils::fetchColumn( $db_table_difficulty, 'difficult_desc', 'difficult_id', '0' );
	echo $rc->getMenu2('recipe_difficulty', (isset($recipe['recipe_difficulty']) ? $recipe['recipe_difficulty'] : ""), false );
?>
	</td>
</tr>
<tr>
	<td><?php echo $LangUI->_('Number of Servings');?>:<?php echo getHelpLink("servings");?></td>
	<td><input type="text" name="recipe_serving_size" id="recipe_serving_size" class="required number" size="3" value="<?php echo (isset($recipe["recipe_serving_size"]) ? $recipe["recipe_serving_size"] : "");?>"></td>
</tr>
<tr>
	<td><?php echo $LangUI->_('Comments');?>:<?php echo getHelpLink("comments");?></td>
	<td><input type="text" name="recipe_comments" size="60" value="<?php echo (isset($recipe["recipe_comments"]) ? $recipe["recipe_comments"] : "");?>"></td>
</tr>
<tr>
	<td><?php echo $LangUI->_('Picture') . ":" . getHelpLink("picture");?></td>
	<td>
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $g_rb_max_picture_size;?>">
		<input type="hidden" name="recipe_picture_oid" value="<?php echo (isset($recipe['recipe_picture_oid']) ? $recipe['recipe_picture_oid'] : "");?>">
		<input type="hidden" name="recipe_picture_type" value="<?php echo (isset($recipe['recipe_picture_type']) ? $recipe['recipe_picture_type'] : "");?>">
		<input type="file" name="recipe_picture" value="<?php echo (isset($_FILES['recipe_picture']['name']) ? $_FILES['recipe_picture']['name'] : "");?>">
		<br/>
		<?php if (isset($recipe['recipe_picture_type']) && $recipe['recipe_picture_type'] != NULL) { ?>
		<input type="checkbox" id="remove_picture" name="remove_picture" value="yes"><label for="remove_picture"><?php echo $LangUI->_('Remove Picture');?></label>
		<?php } if ($g_rb_image_resize_width != 0) {?>
		<input type="checkbox" id="resize_picture" name="resize_picture" value="yes" ><label for="resize_picture"><?php echo $LangUI->_('Resize Picture');?></label>	
		<?php } ?>
	</td>
</tr>
<?php if (isset($recipe['recipe_picture_type']) && $recipe['recipe_picture_type'] != NULL) { ?>
<tr>
	<td colspan="2">
		<img src="./modules/recipes/view_picture.php?recipe_id=<?php echo $recipe_id ?>"/>
	</td>
</tr>
<?php } ?>
<tr>
	<td colspan="2">
		<input type="checkbox" name="private" value="yes" <?php if ($private) echo 'checked';?>>
		<label for="private"><?php echo $LangUI->_('Mark this recipe as private') . getHelpLink("private");?></label>
	</td>
</tr>
</table>
</fieldset>

<fieldset>
	<legend>
	<?php echo $LangUI->_('Ingredients');?> <?php echo getHelpLink("ingredients");?>
	</legend>
<?php
// When this is an existing recipe list the ingredients
if ($recipe_id) 
{
	$sql = "SELECT $db_table_ingredientmaps.*,
			unit_desc,
			ingredient_name
			FROM $db_table_ingredientmaps
			INNER JOIN $db_table_units ON unit_id = map_unit
			INNER JOIN $db_table_ingredients ON ingredient_id = map_ingredient
			WHERE map_recipe = " . $DB_LINK->addq($recipe_id, get_magic_quotes_gpc()) . " ORDER BY map_order";
	$ingredients = $DB_LINK->Execute($sql);
	// Error check
	if (!$ingredients) {
		echo $LangUI->_('There was an error') . "<br />";
		echo $sql . "<br>";
		echo $DB_LINK->ErrorMsg();
	}
	$n = $ingredients->RecordCount();
	// Select the related recipes as well.
	$sql = "SELECT related_child,related_required,recipe_name FROM $db_table_related_recipes " .
		   " INNER JOIN $db_table_recipes ON recipe_id = related_child " .
		   " WHERE related_parent=" . $DB_LINK->addq($recipe_id, get_magic_quotes_gpc()) . " ORDER BY related_order";
	$related = $DB_LINK->Execute($sql);
	$p = $related->RecordCount();

	// set the totals for rendering
	$total_related=$p;
	$total_ingredients=$n;
}

// Make at least 1 on the screen the start
if ($total_ingredients == 0)
{
	$total_ingredients = 6;
}
if ($total_related == 0)
{
	$total_related = 2;
}

?>

<table id="sortableTable1" cellspacing="1" cellpadding="2" border="0" class="data">
<thead>
<tr>
	<th></th>
	<th></th>
	<th><?php echo $LangUI->_('Quantity');?></th>
	<th><?php echo $LangUI->_('Units');?></th>
	<th><?php echo $LangUI->_('Qualifier');?></th>
	<th><?php echo $LangUI->_('Ingredient') . " - ";?>
	<a href="#" id="addNewIngredientsLink" style="color: #FFFFFF;">[<?php echo $LangUI->_('add new');?>]</a></th>
	<th><?php echo $LangUI->_('Optional');?></th>
</tr>
</thead>
<tbody class="content">
<?php
	$localUnits = Units::getAllUnits();
	// Print out the ingredient fields
	for ($i=0; $i < $total_ingredients; $i++) 
	{
		if ($ingredients != null)
		{
			$ingredient_id= $ingredients->fields['map_ingredient'];
			$ingredient_name = $ingredients->fields['ingredient_name'];
			$ingredient_qual = $ingredients->fields['map_qualifier'];
			$ingredient_quant = $ingredients->fields['map_quantity'];
			$ingredient_unit = $ingredients->fields['map_unit'];
			$ingredient_optional = ($ingredients->fields['map_optional']==$DB_LINK->true) ? 'checked' : '';
		}
		$ingredient_delete = ''; // default starting out value (nothing selected)

		if ($i >= $n) {
			$ingredient_id = 0;
			$ingredient_name = "";
			$ingredient_qual = "";
			$ingredient_quant = "";
			$ingredient_unit = "";
			$ingredient_optional = ""; //just to make sure
		}

		echo "<tr>";
		echo '<td><div class="ui-state-default ui-corner-all" title="Delete"><span class="ui-icon ui-icon-trash"></span></div></td>';
		echo '<td><div class="ui-state-default ui-corner-all" title="Order Ingredient"><span class="ui-icon ui-icon-arrow-4"></span></div></td>';
		echo '<td align=left><input type=text size=4 autocomplete="off" onchange="JavaScript:fractionConvert(this);" id="ingredientQuantity_'.$i.'" name="ingredientQuantity_'.$i.'" value="'.$ingredient_quant.'"></td>';
		echo '<td align=left>';
		echo DBUtils::arrayselect( $localUnits, 'ingredientUnit_'.$i, 'size=1', $ingredient_unit);
		echo "</td>\n";
		echo '<td><input type="text" id="ingredientQualifier_'.$i.'" name="ingredientQualifier_'.$i.'" value="'.$ingredient_qual.'" maxlength=32 size="20"></td>';
		echo '<td align=left>';

		echo "<div class=\"ui-widget\"><input id=\"ingredientAuto_$i\" name=\"ingredientAuto_$i\" value=\"$ingredient_name\"/></div>";
		echo "<input type=\"hidden\" id=\"ingredientId_$i\" name=\"ingredientId_$i\" value=\"$ingredient_id\">\n";
		echo "</td>\n";
		echo '<td align="center"><input type="checkbox" id="ingredientOptional_'.$i.'" name="ingredientOptional_'.$i.'" value="checked" ' . $ingredient_optional . '></td>';
		echo "</tr>\n";
		if ($i < $n)
			$ingredients->MoveNext();
	}
?>
</tbody>
</table>
<br/>
<div>
More Ingredients: <input id="addCount" style="width: 20px;" value="1">
<input type="button" addFor="sortableTable1" class="addItemsButton" value="Show"/>
</div>
</fieldset>

<fieldset>
<legend>
	<?php echo $LangUI->_('Directions');?><?php echo getHelpLink("directions");?>
</legend>
<textarea name="recipe_directions" rows="15" cols="75">
<?php echo (isset($recipe['recipe_directions']) ? $recipe['recipe_directions'] : "");?>
</textarea>
</fieldset>

<fieldset>
<legend>
	<?php echo $LangUI->_('Related Recipe(s)') . " " . getHelpLink("related_recipes");?>
</legend>
<?php
	echo '<table id="sortableTable2" cellspacing="1" cellpadding="2" border="0" class="data">';
	echo '<thead>';
	echo '<tr><th></th><th></th>';
	echo '<th>' . $LangUI->_('Recipe Name') . '</th>';
	echo '<th>' . $LangUI->_('Required') . getHelpLink("related_recipes_required") . '</th>';
	echo '</tr></thead>';
	echo '<tbody class="content">';

	// Loop/Section to add related recipes to this recipe
	for ($i=0; $i<$total_related; $i++) 
	{
		$related_id = "";
		$related_required = "";
		$recipe_name = "";
		
		// Read data from the DB while it is there
		if ($i < $p)
		{
			// Fill in a drop down for a entry that already exists
			$related_id = $related->fields['related_child'];
			$recipe_name = $related->fields['recipe_name'];
			$related_required = ($related->fields['related_required']==$DB_LINK->true) ? 'checked' : '';
			$related->MoveNext();
		}

		echo "<tr>";
		echo '<td><div class="ui-state-default ui-corner-all" title="Delete"><span class="ui-icon ui-icon-trash"></span></div></td>';
		echo '<td><div class="ui-state-default ui-corner-all" title="Order Recipes"><span class="ui-icon ui-icon-arrow-4"></span></div></td>';
		echo "<td>\n";

		echo "<div class=\"ui-widget\"><input id=\"recipeAuto_$i\" name=\"recipeAuto_$i\"value=\"$recipe_name\"/></div>";
		echo "<input type=\"hidden\" id=\"relatedId_$i\" name=\"relatedId_$i\" value=\"$related_id\">\n";
		echo '</td><td align="center">';
		echo '<input type="checkbox" id="relatedRequired_'.$i.'" name="relatedRequired_'.$i.'" value="checked" ' . $related_required . '>';
		echo "</td></tr>\n";
	}
	echo "</tbody></table>\n";

?>
	<br>
	<div>
	More Recipes: <input id="addCount" style="width: 20px;" value="1">
	<input type="button" addFor="sortableTable2" class="addItemsButton" value="Show"/>
	</div>
</fieldset>

<br/><br/>
<input type="button" id="updateRecipeButton" value="<?php echo ($recipe_id ? $LangUI->_('Update Recipe') : $LangUI->_('Add Recipe'));?>" class="button">
<br/>
<br/>
</form>
<div id="addNewIngredientsDialog">
	<div id="addNewIngredientContent"></div>
</div>
<div id="errorDialog" title="Validation Error(s)">
Please correct the following errors:<br />
	<ul id="errorList" class="ui-state-error">

	</ul>
And Re-submit the form to continue.
</div>
