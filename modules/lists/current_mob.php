<script type="text/javascript">
	function removeItem(id)
	{
		var $target = $("#" + id);
		var decor = $target.css('text-decoration');
		var bg = $target.css('background', 'white');
		if (decor == "none" || decor == "")
		{
			$target.css('text-decoration', 'line-through');
			$target.css('background', '#E0E0E0');
			$target.css('border-color', '#C2C2C2');
		}
		else
		{
			$target.css('text-decoration', '');
			$target.css('background', '');
		}
	}
</script>
<br/>		
<ul data-role="listview" id="currentList" data-inset="false" data-theme="e" data-dividertheme="b">

<?php 
$listObj = new ShoppingList();
$listObj->loadCurrentListId();
$listObj->loadItems(true);

// Cycle through the ingredients now (if any)		
$locationName = "";
foreach ($listObj->ingredients as $ingObj) {
	if ($ingObj->locationDescription != "" && $ingObj->locationDescription != $locationName)
	{
		echo "<li data-role=\"list-divider\">$ingObj->locationDescription</li>";
		$locationName = $ingObj->locationDescription;
	}?>
	<li id="listIngredient_<?php echo $ingObj->id;?>" onclick="removeItem('listIngredient_<?php echo $ingObj->id;?>');">
		<div><?php echo $ingObj->quantity  . " " . $ingObj->unitDescription . " - " . $ingObj->name;?></div>
	</li>
<?php } ?>
</ul>