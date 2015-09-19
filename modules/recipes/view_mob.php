<?php
require_once("classes/Fraction.class.php");
require_once("classes/DBUtils.class.php");

$recipe_id = isValidID( $_GET['recipe_id'] ) ? $_GET['recipe_id'] : 0;
$show_ratings = isset ($_GET['show_ratings'] ) ? true : false;
$show_ratings = isset($_GET['show_ratings']) ? isset($_GET['show_ratings']) : $g_rb_show_ratings;

#Construct the Query and do most of the setup first, the print html
$sql = "SELECT $db_table_recipes.*,
        ethnic_desc,
        base_desc,
        course_desc,
        time_desc,
        difficult_desc,
        user_name,
	source_title
FROM $db_table_recipes
LEFT JOIN $db_table_ethnicity ON ethnic_id = recipe_ethnic
LEFT JOIN $db_table_bases ON base_id = recipe_base
LEFT JOIN $db_table_courses ON course_id = recipe_course
LEFT JOIN $db_table_prep_time ON time_id = recipe_prep_time
LEFT JOIN $db_table_difficulty ON difficult_id = recipe_difficulty
LEFT JOIN $db_table_users ON user_id = recipe_user
LEFT JOIN $db_table_sources ON source_id = recipe_source
WHERE recipe_id = ?";

$stmt = $DB_LINK->Prepare($sql);
$recipe = $DB_LINK->Execute($stmt, array($recipe_id));

// Error check
DBUtils::checkResult($recipe, NULL, NULL, $sql);
// Make sure we have some results before continuing
if ($recipe->RecordCount() == 0)
{
	die($LangUI->_('Recipe Does not Exist!'));
}

/*
        If this is a private recipe and the user does not have access to it, then do not show it
*/
if (!$SMObj->checkAccessLevel("EDITOR") && 
	($recipe->fields['recipe_private'] == $DB_LINK->true && $SMObj->getUserID() != $recipe->fields['recipe_user']))
{
	die($LangUI->_('This recipe is private and you do not have permission to view it!'));
}

# fetch the ingredients for the recipe
$sql = "SELECT $db_table_ingredientmaps.*,
        unit_desc,
        ingredient_name
FROM $db_table_ingredientmaps
LEFT JOIN $db_table_units ON unit_id = map_unit
LEFT JOIN $db_table_ingredients ON ingredient_id = map_ingredient
WHERE map_recipe = ? ORDER BY map_order";

$stmt = $DB_LINK->Prepare($sql);
$ingredients = $DB_LINK->Execute($stmt, array($recipe_id));

// Error check
DBUtils::checkResult($ingredients, NULL, NULL, $sql);

# fetch the related ingredients
$sql = "
SELECT related_child, recipe_name, recipe_directions, related_required
FROM $db_table_related_recipes
LEFT JOIN $db_table_recipes ON recipe_id = related_child
WHERE related_parent= ? ORDER BY related_order";

$stmt = $DB_LINK->Prepare($sql);
$related = $DB_LINK->Execute($stmt, array($recipe_id));

// Error check
DBUtils::checkResult($related, NULL, NULL, $sql);

// if no scale is set the read from the database
if (isset($_GET['recipe_scale'])  && $recipe->fields['recipe_serving_size'] != NULL) {
        $recipe_scale=$_GET['recipe_scale'];
        $scale_by = ($recipe_scale/$recipe->fields['recipe_serving_size']);
} else if ($recipe->fields['recipe_serving_size'] != NULL) {
        $recipe_scale=$recipe->fields['recipe_serving_size'];
        $scale_by=1;
} else {
        $recipe_scale="";
        $recipe->fields['recipe_serving_size']="";
        $scale_by=1;
}

$related_names = array();

while (!$related->EOF) {
        $id = $related->fields['related_child'];
        $related_names[$id] = $related->fields;
        $related->MoveNext();
}
// Now start printing out HTML now that we know what we are doing.
?>

<script type="text/javascript">

	function confirmDelete() {
	  return confirm("<?php echo $LangUI->_('Are you sure you wish to delete this recipe?');?>");
	}

	function scaleRecipe()
	{
		var scale = $('#txtRecipeScale').val();
		var url = './index.php?m=recipes&a=view_mob&recipe_id=<?php echo $recipe_id;?>&recipe_scale=' + scale;
		$.mobile.changePage(url, "flip", true, true);
	}
	
	function resetScaleRecipe(scale)
	{
		var url = './index.php?m=recipes&a=view_mob&recipe_id=<?php echo $recipe_id;?>&recipe_scale=' + scale;
		$.mobile.changePage(url, "flip", true, true);
	}
</script>

<div data-role="collapsible" data-collapsed="true">
	<h3><?php echo $recipe->fields['recipe_name'];?> - Attributes</h3>

<table cellspacing="1" cellpadding="2" border="0" class="std" width="100%">
<tr>
        <td nowrap><?php echo $LangUI->_('Recipe Name');?>:</td>
        <td nowrap><b><?php echo $recipe->fields['recipe_name'];?></b></td>
        <td nowrap width=10%><?php echo $LangUI->_('Submitted by');?>:</td>
        <td nowrap><b><?php echo $recipe->fields['user_name'];?></b></td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Source');?>:</td>
        <td nowrap><b><a href="#" onClick=window.open('index.php?m=recipes&a=sources&print=yes&source_id=<?php echo $recipe->fields['recipe_source'];?>','recipe_sources','height=400,width=600,toolbar=1,menubar=0,status=0,scrollbars=1');><?php echo $recipe->fields['source_title'];?></a></td></b></td>
		<td nowrap><?php echo $LangUI->_('Source Description');?>:</td>
		<td nowrap><b><?php echo $recipe->fields['recipe_source_desc'];?></b></td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Ethnicity');?>:</td>
        <td nowrap width="25%"><b><?php echo $recipe->fields['ethnic_desc'];?></b></td>
		<td nowrap><?php echo $LangUI->_('Last Modified');?>:</td>
        <td nowrap><b><?php  $date = DBUtils::formatDate($recipe->fields['recipe_modified']);
                             echo ($date[2]+0) . '/' . ($date[3]+0) . '/' . $date[1];
                       ?>
                   </b></td>

</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Base');?>:</td>
        <td nowrap><b><?php echo $recipe->fields['base_desc'];?></b></td>
        <td colspan=2 align="left"><?php echo $LangUI->_('Comments');?>:</td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Course');?>:</td>
        <td nowrap><b><?php echo $recipe->fields['course_desc'];?></b></td>
        <td colspan=2 rowspan="4" width="100%"><?php echo $recipe->fields['recipe_comments'];?>&nbsp;</td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Difficulty');?>:</td>
        <td nowrap><b><?php echo $recipe->fields['difficult_desc'];?></b></td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Preparation Time');?>:</td>
        <td nowrap><b><?php echo $recipe->fields['time_desc'];?></b></td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Number of Servings');?>:</td>
        <td nowrap><b><?php echo ($recipe->fields['recipe_serving_size']*$scale_by);?></b></td>
</tr>
</table>

<?php
if ($recipe->fields['recipe_picture_type']!=NULL) {
?>
<br/>
<a href="./index.php?m=recipes&a=view_picture_mob&recipe_id=<?php echo $recipe_id;?>">View Picture</a>
<?php }

echo "</div>";
echo "<b>" . $LangUI->_('Ingredients') . ":</b>";

// Save the optional ingredients for later display
$optIng = array();

// Iterate through the ingredients
while (!$ingredients->EOF) {
        $fraction = new Fraction($ingredients->fields['map_quantity']*$scale_by);
        $quant = $fraction->toString();
        if ($ingredients->fields['map_optional']!=$DB_LINK->true) {
                // print out the ingredient
                echo "<br />";
                echo $quant;
                if ($ingredients->fields['unit_desc'] != $LangUI->_('Unit'))
                        echo ' '.$ingredients->fields['unit_desc'].$LangUI->_('(s)');
                echo ' '.$ingredients->fields['map_qualifier'];
                echo ' '.$ingredients->fields['ingredient_name'];
        } else {
                // save this one for later
                $optIng[] = array('id'=>$ingredients->fields['map_ingredient'],
                                                  'name'=>$ingredients->fields['ingredient_name'],
                                                  'unit'=>$ingredients->fields['unit_desc'],
                                                  'unit_id'=>$ingredients->fields['map_unit'],
                                                  'qualifier'=>$ingredients->fields['map_qualifier'],
                                                  'quantity_dec'=>$ingredients->fields['map_quantity'],
                                                  'quantity'=>$quant,
                                                  'int_quant'=>($ingredients->fields['map_quantity']*$scale_by));
        }
        $ingredients->MoveNext();
}
// Print out the optional ingredients if there are any
if (count($optIng))
        echo "<p><b>" . $LangUI->_('Optional Ingredients') . ":</b>";

foreach ($optIng as $ing) {
        echo "<br />\n";
        echo $ing['quantity'];
        if ($ing['unit'] != $LangUI->_('Unit'))
                echo ' '.$ing['unit'].$LangUI->_('(s)');
        echo ' '.$ing['qualifier'];
        echo ' '.$ing['name'];
        echo ' <a href="index.php?m=lists&a=current&mode=add' .
                        '&ingredient_selected_0=yes' .
                        '&ingredient_id_0='.$ing['id'] .
                        '&ingredient_quantity_0=' . $ing['quantity_dec'] .
                        '&ingredient_unit_0=' . $ing['unit_id'] . '">' .
                        $LangUI->_('Add to shopping list').'</a>';
}
?>
<br/><br/>
<b><?php echo $LangUI->_('Directions');?>:</b><br />
<?php if ($recipe->fields['recipe_directions'] != "")
{
	echo str_replace( "\n", '<br />', $recipe->fields['recipe_directions'] );
}?>
<br/>

<?php if (count($related_names) > 0) { ?>
<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
	<li data-role="list-divider">Linked Recipes</li>
	
<?php 
$total_recipes=1;
	foreach($related_names as $k=>$v) {
	// $v contains the array of fields return from the sql query for this related recipe
	?>
		<li><a href="./index.php?m=recipes&a=view_mob&recipe_id=<?php echo $k;?>&recipe_scale=<?php echo $recipe_scale?>">
			<?php echo $v['recipe_name'];?></a>
			<?php if ($v['related_required']==$DB_LINK->false) { ?>
				<div class="ui-li-count">Required</div>
			<?php } else { ?>
				<div class="ui-li-count">Optional</div>
			<?php }?>
		</li>
	<?php } ?>
</ul>
<?php }?>
<?php if ($recipe_scale) { ?>

<div data-role="fieldcontain">
	<label for="recipe_scale"><?php echo $LangUI->_('Scale recipe');?>: </label>
    <input type="text" id="txtRecipeScale" name='recipe_scale' size=2 value='<?php echo $recipe_scale;?>'>
</div>	

<input type="button" id="btnScaleRecipe" data-inline="true" value="<?php echo $LangUI->_('Update Scaling');?>" onclick="scaleRecipe();" class="button">
<input type="button" id="btnResetScaleRecipe" data-inline="true" value="<?php echo $LangUI->_('Reset Scaling');?>" 
	onclick="resetScaleRecipe(<?php echo $recipe->fields['recipe_serving_size'];?>)" class="button">

<?php } ?>

