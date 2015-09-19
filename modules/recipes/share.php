<?php
$recipe_id = (isset($_GET['recipe_id']) && isValidID( $_GET['recipe_id'] )) ? $_GET['recipe_id'] : 0;
?>
<form action="./index.php?m=recipes&a=view&recipe_id=<?php echo $recipe_id?>&dosql=send_message" method="POST">
<input type="hidden" name="recipe_id" value="<?php echo $recipe_id?>"/>
<table>
<tr>
	<td>To</td>
	<td>
		<input name="email_address"/>
	</td>
</tr>
<tr>
	<td>Message</td>
	<td>
		<textarea name="message" rows="15" cols="60"></textarea>
	</td>
</tr>
<tr>
	<td colspan="2" align="right">
		<input type="submit" value="Send"/>
	</td>
</table>
</form>

