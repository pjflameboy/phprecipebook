<div id="ingredientAddEditContainer">
<?php
require_once("classes/Units.class.php");
require_once("classes/DBUtils.class.php");

$ingredient_id = (isset($_GET['ingredient_id']) && isValidID($_GET['ingredient_id'])) ? $_GET['ingredient_id'] : 0;

if (!$SMObj->isUserLoggedIn())
	die($LangUI->_('You must be logged into perform the requested action.'));

if ($ingredient_id && !$SMObj->checkAccessLevel("EDITOR")) {
	// Figure out who the owner of this ingredient is, Editors can edit anyones recipes
	// The owner of a ingredient does not change when someone edits it.
	$sql = "SELECT ingredient_user FROM $db_table_ingredients WHERE ingredient_id = " . $DB_LINK->addq($ingredient_id, get_magic_quotes_gpc());
	$rc = $DB_LINK->Execute($sql);
	// If the recipe is owned by someone else then do not allow editing
	if ($rc->fields['ingredient_user'] != "" && $rc->fields['ingredient_user'] != $SMObj->getUserID())
		die($LangUI->_('You are not the owner of this ingredient, you are not allowed to edit it'));
}

// get the information about the Ingredient (empty query if new Ingredient)
if ($ingredient_id) {
	$sql = "SELECT *
			FROM $db_table_ingredients
			LEFT JOIN $db_table_units ON ingredient_unit = unit_id
			WHERE ingredient_id = " . $DB_LINK->addq($ingredient_id, get_magic_quotes_gpc());
	$ingredients = $DB_LINK->Execute( $sql );
	DBUtils::checkResult($ingredients, NULL, NULL, $sql);
}

?>

<script language="javascript">
	$(document).ready(function() {
		$('#ingredient_form').validate();
			
		$("#ingredient_name").autocomplete({
			source: "index.php?m=ingredients&a=get_core&format=no",
			minLength: 1,
			select: function(event, ui) {
				if (ui.item.id)
				{
					console.log('set it');
					$("#coreIngredient_id").val(ui.item.id);
					$("#coreIndredient_Name").text("USDA Name: " + ui.item.label);
				} else {
					console.log('clear it');
					$("#ingredient_name").val('');
					$("#coreIngredient_id").val('');
					$("#coreIndredient_Name").text('');
				}
			}
		});
		
		<?php if ($ingredient_id) { ?>
		$('#availableActions ul').append('<li><a href="index.php?m=ingredients&a=view&ingredient_id=<?php echo $ingredient_id . '">' . $LangUI->_('View Ingredient');?></a></li>');
		$('#availableActions ul').append('<li><a href="#" onClick="return confirmDelete(); return false;"><?php echo $LangUI->_('Delete Ingredient');?></a></li>');
		<?php } ?>
	});
	
	function confirmDelete() {
		if (confirm("<?php echo $LangUI->_('Are you sure you wish to delete this ingredient?');?>"))
		{
			window.location = 'index.php?m=ingredients&dosql=delete&ingredient_id_0=<?php echo $ingredient_id?>&ingredient_selected_0=yes';
		}
		return false;
	}

	function SaveIngredient() {
		if ($('#ingredient_name').val().indexOf("No Results for") > -1) 
		{
			alert("Please confirm your Ingredient name is correct");
			return;
		}
		if ($("#ingredientForm").valid()) {
			var formAction = './index.php?m=ingredients&a=addedit&format=<?php echo $format; ?>&ingredient_id=<?php echo $ingredient_id;?>&print=<?php echo $print;?>';
			// submit
			<?php if ($print == "yes") { ?>
			$.post(
				formAction,
				$("#ingredientForm").serialize(),
				function(data) {
					$('#ingredientAddEditContainer').html(data);
				});
			<?php } else {?>
				$("#ingredientForm").attr('action', formAction);
				$("#ingredientForm").submit();
			<?php } ?>
			return true;
		}
	}
</script>
<br/>
<form name="ingredient_form" id="ingredientForm" action="" method="post">
<input type="hidden" name="dosql" value="update">

<table cellspacing="1" cellpadding="2" border="0" class="data">
<tr>
	<th colspan = "2">
		<?php
			if ($ingredient_id) {
				echo $LangUI->_('Edit Ingredient');
			} else {
				echo $LangUI->_('Add Ingredient');
			}
		?>
	</th>
</tr>
<?php
	$units = Units::getAllUnits(); // Load the units

	$rc_locations = DBUtils::fetchColumn( $db_table_locations, 'location_desc', 'location_id', 'location_desc' );
	$locations = DBUtils::createList($rc_locations, 'location_id', 'location_desc');

	$liqsol = array(
			 "FALSE" => $LangUI->_('Liquid'),
			 "TRUE" => $LangUI->_('Solid')
	);

	$ingredient_name = "";
	$ingredient_desc = "";
	$ingredient_unit = "";
	$ingredient_loc = "";
	$ingredient_solid = "";
	$coreingredient_id = "";
	$coreingredient_Name = "";

	if ($ingredient_id)
	{
		$ingredient_name = $ingredients->fields['ingredient_name'];
		$ingredient_desc = $ingredients->fields['ingredient_desc'];
		$ingredient_unit = $ingredients->fields['ingredient_unit'];
		$ingredient_loc = $ingredients->fields['ingredient_location'];
		$ingredient_solid = $ingredients->fields['ingredient_solid'];
		if ($ingredient_solid == $DB_LINK->false)
		{
			$ingredient_solid="FALSE";
		}
		else
		{
			$ingredient_solid="TRUE";
		}
	}
	?>
	<tr>
		<td><?php echo $LangUI->_('Name');?></td>
		<td>
			<div class="ui-widget">
				<input id="ingredient_name" name="ingredient_name" class="required" value="<?php echo $ingredient_name;?>"/>
			</div>
			<div id = "coreIndredient_Name"><?php echo $coreingredient_Name;?></div>
			<input type="hidden" id="coreIngredient_id" name="coreIngredient_id" value="<?php echo $coreingredient_id;?>">
		</td>
	</tr>
	<tr>
		<td><?php echo $LangUI->_('Description');?></td>
		<td>
			<div class="ui-widget">
			<input type="text" name="ingredient_desc" autocomplete="off" value="<?php echo $ingredient_desc;?>" size="30">
			</div>
		</td>
	</tr>
	<tr>
		<td><?php echo $LangUI->_('Default Measurement');?></td>
		<td>
		<?php echo DBUtils::arrayselect( $units, 'ingredient_unit', 'size=1', $ingredient_unit); ?>
		</td>
	</tr>
	<tr>
		<td><?php echo $LangUI->_('Liquid/Solid');?></td>
		<td>
			<?php echo DBUtils::arrayselect( $liqsol, 'ingredient_solid', 'size=1', $ingredient_solid );?>
		</td>
	</tr>
	<tr>
		<td><?php echo $LangUI->_('Location in Store');?></td>
		<td>
			<?php echo DBUtils::arrayselect( $locations, 'ingredient_loc', 'size=1', $ingredient_loc); ?>
		</td>
	</tr>
</table>
<br/>
<input type="button" value="<?php echo ($ingredient_id ? $LangUI->_('Update Ingredient') : $LangUI->_('Add Ingredient'));?>" class="button" onclick="SaveIngredient()">
<br />
</form>
</div>
