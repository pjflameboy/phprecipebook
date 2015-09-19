<?php
$ingredient_id = isValidID( $_GET['ingredient_id'] ) ? $_GET['ingredient_id'] : 0;
$ingObj = new Ingredient();
$ingObj->id = $ingredient_id;
$ingObj->loadIngredient();

// Now start printing out HTML now that we know what we are doing.
?>
<script type="text/javascript">
$(document).ready(function() { 
	<?php if ($SMObj->isUserLoggedIn()) { ?>
	$('#availableActions ul').append('<li><a href="./index.php?m=ingredients&a=addedit&ingredient_id=<?php echo $ingredient_id . '">' . $LangUI->_('Edit Ingredient');?></a></li>');
	$('#availableActions ul').append('<li><a href="#" onClick="return confirmDelete(); return false;"><?php echo $LangUI->_('Delete Ingredient');?></a></li>');
	<?php } ?>
	$('#availableActions ul').append('<li><a href="index.php?m=ingredients&a=related&ingredient_id=<?php echo $ingredient_id . "\">" . $LangUI->_('Find recipes using ingredient');?></a></li>');
	$('#availableActions ul').append('<li><a href="index.php?m=lists&a=current&mode=add&ingredient_selected_0=yes&ingredient_id_0=<?php echo $ingredient_id . '&ingredient_unit_0='. $ingObj->unit . '">' . $LangUI->_('Add to shopping list');?></a></li>');
	
});

function confirmDelete() {
	if (confirm("<?php echo $LangUI->_('Are you sure you wish to delete this ingredient?');?>"))
	{
		window.location = 'index.php?m=ingredients&dosql=delete&ingredient_id_0=<?php echo $ingredient_id?>&ingredient_selected_0=yes';
	}
	return false;
}
</script>
<h2>
<?php echo $LangUI->_('View Ingredient');?></td>
</h2>
<table cellspacing="1" cellpadding="2" border="0" class="std" width="40%">
<tr>
        <td nowrap width=20%><?php echo $LangUI->_('Name');?>:</td>
        <td nowrap><b><?php echo $ingObj->name;?></b></td>
</tr>
<?php if (isset($ingObj->coreDescription)) { ?>
<tr>
        <td nowrap width=20%><?php echo $LangUI->_('USDA Name');?>:</td>
        <td nowrap><b><?php echo $ingObj->coreDescription;?></b></td>
</tr
<?php } ?>
<tr>
        <td nowrap width=20%><?php echo $LangUI->_('Description');?>:</td>
        <td nowrap><b><?php echo  $ingObj->description;?></b></td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Form');?>:</td>
        <td nowrap><b>
        <?php
        if ($ingObj->solid)
                echo $LangUI->_('Solid');
        else
                echo $LangUI->_('Liquid');
        ?></b></td>
</tr>
<tr>
        <td nowrap><?php echo $LangUI->_('Location');?>:</td>
        <td nowrap><b><?php echo $ingObj->locationDescription;?></b></td>
</tr>
</table>
