<table cellspacing="0" cellpadding="1" border="0" width="100%">
<tr>
	<td align="left" class="title"><?php echo $LangUI->_('Administer Users');?></td>
</tr>
</table>
<P>

<?php
// The listing of the users
if ($SMObj->checkAccessLevel($SMObj->getSuperUserLevel())) {
	$users = $SMObj->getUsers();
	?>
	<table cellspacing="1" cellpadding="2" border="0" class="data">
	<tr>
		<th></th>
		<th>User ID</th>
		<th>Name</th>
	</tr>
	<?php
	foreach ($users as $user)
	{
		echo "<tr>";
		echo '<td><a href="index.php?m=account&a=addedit&mode=edit&user_id=' . $user['id'] . '">select</a></td>';
		echo "<td>" . $user['login'] . "</td>";
		echo "<td>" . $user['name'] . "</td>";
		echo "</tr>\n";
	}
	?>
	</table>
	<a href="index.php?m=account&a=addedit&mode=new">Add New User</a>
<?php 
} else {
	// They are not an admin, so give them an error message
	echo $LangUI->_('You do not have permission to edit users') . "<br />";
}
?>
