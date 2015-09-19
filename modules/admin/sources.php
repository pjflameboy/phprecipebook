<?php
require_once("classes/DBUtils.class.php");
if (!$SMObj->isUserLoggedIn())
	die($LangUI->_('You must be logged into perform the requested action.'));
?>

<script type="text/javascript">
$(document).ready(function() {
	$("#addSourceForm").validate();	
});

function addEntry()
{
	if ($("#addSourceForm").valid())
	{
		$("#addSourceForm").submit();
	}	
}
</script>
<table cellspacing="0" cellpadding="1" border="0" width="100%">
<tr>
	<td align="left" class="title"><?php echo $LangUI->_('Add/Edit Sources');?></td>
</tr>
</table>

<?php
$counter=0;
$sql = "SELECT source_id,source_title,source_desc FROM $db_table_sources WHERE source_user=? ORDER BY source_title";
$stmt = $DB_LINK->Prepare($sql);
$rc = $DB_LINK->Execute( $stmt, array($SMObj->getUserID()));
DBUtils::checkResult($rc, NULL, NULL, $sql);
?>
<form action="./index.php?m=admin&a=sources&dosql=update_sources" method="POST">
<input type="hidden" name="mode" value="update">
<table cellspacing="1" cellpadding="2" border="0" class="data">
<tr>
	<th><?php echo $LangUI->_('Delete');?></th>
	<th><?php echo $LangUI->_('Title');?></th>
	<th><?php echo $LangUI->_('Description');?></th>
</tr>
<?php while (!$rc->EOF) { ?>
<tr>
	<td valign="top">
		<input type="hidden" name="entry_<?php echo $counter;?>" value="<?php echo $rc->fields[0];?>">
		<input type="checkbox" name="delete_<?php echo $counter;?>" value="yes">
	</td>
	<td valign="top">
		<input type="textbox" size="40" name="desc_<?php echo $counter . '" value="' . $rc->fields[1];?>">
	</td>
	<td>
		<textarea cols="60" rows="5" name="text_<?php echo $counter;?>"><?php echo $rc->fields[2];?></textarea>
	</td>
</tr>
<?php
	$rc->MoveNext();
	$counter++;
}
?>
<tr>
	<td colspan=3>
		<input type="hidden" name="total_entries" value="<?php echo $counter;?>">
		<input type="submit" value="<?php echo $LangUI->_('Update');?>" class="button">
	</td>
</tr>
</table>
</form>

<P>
<form action="./index.php?m=admin&a=sources&dosql=update_sources" id="addSourceForm" name="addForm" method="POST">
<table cellspacing="1" cellpadding="2" border="0" class="data">
<tr>
	<th colspan="2"><?php echo $LangUI->_('Create new entry');?></th>
</tr>
<tr>
	<td><?php echo $LangUI->_('Title');?></td>
	<td>
		<input type="hidden" name="edit_table" value="<?php echo $edit_table;?>">
		<input type="hidden" name="mode" value="add">
		<input type="textbox" class="required" name="new_desc">
	</td>
</tr>
<tr>
	<td colspan="2"><textarea cols="60" rows="5"  name="new_text"></textarea></td>
</tr>
<tr>
	<td colspan=2>
		<input type="button" value="<?php echo $LangUI->_('Add Entry');?>" onClick="addEntry();" class="button">
	</td>
</tr>

</table>
</form>

