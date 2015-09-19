<?php
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "new";
$user_id = isset ($_REQUEST['user_id']) && isValidID($_REQUEST['user_id']) ? $_REQUEST['user_id'] : $SMObj->getUserID();

$editMode = false;

if ($mode == "edit") $editMode = true;
else $editMode = false;

if ($editMode == "add"
	&& !$SMObj->isOpenRegistration() && 
	!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel())) 
{
	die($LangUI->_('This system is not in open registration mode, only the administrator can add users'));
}
	
if (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel()) && $SMObj->getUserID() != $user_id)
	die($LangUI->_('You must be an administrator in order to edit other users!'));

if ($editMode) {
	$user_details = $SMObj->getUserDetails($user_id);
	// If the user visits a provider fill in the Identity if they came back with one
	if (isset($_SESSION['openID_AddIdentity']))
	{
		$user_details['user_identity'] = $_SESSION['openID_AddIdentity'];
		$user_details['user_provider'] = $_SESSION['openID_AddProvider'];
		
		// Got the values, clear so we don't re-use.
		$_SESSION['openID_AddIdentity'] = null;
		$_SESSION['openID_AddProvider'] = null;
	}
} else {
	$user_details['email'] = isset($_SESSION['openID_email']) ? $_SESSION['openID_email'] : "";
	$user_details['login'] = isset($_SESSION['openID_login']) ? $_SESSION['openID_login'] : "";
	$user_details['name'] = isset($_SESSION['openID_name']) ? $_SESSION['openID_name'] : "";
	$user_details['access_level'] = "";
}
?>
<script language="javascript">
jQuery(document).ready(function(){
	$('#facebookLoginLink').click(function() {
			$("#openID_identity").val("<Facebook Login>");
			$("#openID_identity").attr("disabled", "true");
			$("#openID_provider").val("facebook");
			return false;
	});
	
	$('#clearOpenID').click(function() {
		$("#openID_provider").val("");
		$("#openID_identity").val("");
	});
});

function IsValidNewAccountData() {
	f = document.SMNewUserFrm;
	msg = '';

	<?php if (!isset($_SESSION['openID_identity']) && ($SMObj->getNewUserSetPasswd() || $editMode)) { ?>
	if (f.sm_password.value.length != 0 && f.sm_password.value.length < 3) {
		msg += '<?php echo $LangUI->_('Please enter a valid')." ".$LangUI->_('User login password');?>.\n';
	}

	if (f.sm_password.value != f.sm_password2.value) {
		msg += '<?php echo $LangUI->_('Please re-enter your passwords as they do not match');?>.\n';
	}
	<?php } ?>
	
	if (msg) {
		alert( msg );
		return false;
	} else {
		return true;
	}
}
</script>

<table cellspacing="0" cellpadding="1" border="0" width="100%">
<tr>
	<td align="left" class="title">
		<?php 
			if ($editMode)
				echo $LangUI->_('Edit User');
			else 
				echo $LangUI->_('New User Registration');
			?></td>
</tr>
</table>

<p><?php 
		if ($editMode)
			echo $LangUI->_('Only put a value in the password field if you wish to changed it');
		else 
			echo $LangUI->_('Please fill in the following information.  All fields are required.');
	?></p>

<table cellspacing="0" cellpadding="3" border="0" width="700px" class="std">
<form name="SMNewUserFrm" onsubmit="return IsValidNewAccountData()" action="./index.php?m=account&a=addedit&mode=<?php echo $mode;?>&user_id=<?php echo $user_id;?>" method="POST">
<input type="hidden" id="dosql" name="dosql" value="update">
<input type="hidden" name="sm_mode" value="<?php echo $mode;?>">
<input type="hidden" name="sm_userId" value="<?php echo $user_id;?>">
<?php if (!$editMode) { ?>
<tr >
	<th align="right" valign="top"><?php echo $LangUI->_('Setup Login Using:');?></td>
	<th align="left">
		<a href="./openIDLogin.php?provider=google&mode=create"><img src="images/google_16.png" alt="Google" style="border: hidden; margin-right: 4px; "/>Google</a><br/>
		<a href="#" id="facebookLoginLink" ><img src="images/facebook_16.png" alt="Facebook" style="border: hidden; margin-right: 4px;"/>Facebook</a>
		<input type="hidden" name="openID_identity" value="<?php echo isset($_SESSION['openID_identity']) ? $_SESSION['openID_identity'] : "";?>"/>
		<input type="hidden" name="openID_provider" value="<?php echo isset($_SESSION['openID_provider']) ? $_SESSION['openID_provider'] : "";?>"/>
	</th>
</tr>
<?php } ?>
<tr>
	<td align="right"><?php echo $LangUI->_('Login ID');?>:</td>
	<?php if ($editMode) { ?>
		<input type="hidden" name="sm_login" value="<?php echo $user_details['login']; ?>" required>
		<td><?php echo $user_details['login'];?></td>
	<?php } else { ?>
		<td><input type="text" name="sm_login" value="<?php echo isset($user_details['login']) ? $user_details['login'] : ""?>" required></td>
	<?php } ?>
</tr>
<tr>
	<td align="right"><?php echo $LangUI->_('Name');?>:</td>
	<td><input type="text" name="sm_name" value="<?php echo isset($user_details['name']) ? $user_details['name'] : "";?>" required></td>
</tr>
<tr>
	<td align="right"><?php echo $LangUI->_('Email Address');?>:</td>
	<td><input type="email" name="sm_email" value="<?php echo isset($user_details['email']) ? $user_details['email'] : "";?>" required></td>
</tr>
<tr>
	<td align="right"><?php echo $LangUI->_('Language');?>:</td>
	<td>
<?php
	$lang='';
 	if ($editMode)
		$lang = $user_details['language'];
	$arr = $SMObj->getSupportedLanguages();
	echo DBUtils::arraySelect( $arr, 'sm_language', 'size="1"', $lang );
?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $LangUI->_('Country');?>:</td>
	<td>
<?php
	$lang='';
	$country = "";
 	if ($editMode)
		$country = $user_details['country'];
	$arr = $SMObj->getSupportedCountries();
	echo DBUtils::arraySelect( $arr, 'sm_country', 'size="1"', $country );
?>
	</td>
</tr>
<?php if ($SMObj->checkAccessLevel($SMObj->getSuperUserLevel())) { ?>
<tr>
	<td align="right">
		<?php echo $LangUI->_('Access Level');?>:</td>
	<td>
		<?php

			$arr = $SMObj->getRevAccessArray();
			echo DBUtils::arraySelect( $arr, 'sm_access_level', 'size="1"', $SMObj->getAccessLevelRounded($user_details['access_level']));
		?>
	</td>
</tr>
<?php } ?>
<?php if (!$editMode) { ?>
<tr>
	<td align="right">Init w/ Sample Ingredients</td>
	<td><input type="checkbox" name="create_ingredients" value="true" checked/></td>
</tr>
<?php } ?>
<?php if ($editMode && (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel()))) { ?>
<tr>
	<td align="right"><?php echo $LangUI->_('Old Password');?>:</td>
	<td><input type="password" name="sm_old_password" required></td>
</tr>
<?php } ?>
<?php if (($SMObj->getNewUserSetPasswd() && !isset($_SESSION['openID_identity'])) || $editMode || $SMObj->checkAccessLevel($SMObj->getSuperUserLevel())) {?>
<tr>
	<td align="right"><?php echo $LangUI->_('Password');?>:</td>
	<td><input type="password" id="sm_password" name="sm_password" required></td>
</tr>
<tr>
	<td align="right"><?php echo $LangUI->_('Confirm password');?>:</td>
	<td><input type="password" id="sm_password2" name="sm_password2" required></td>
</tr>
<?php }
if ($editMode)
{?>
<tr>
	<td align="right" valign="top">Open ID Identity</td>
	<td>
		<a href="./openIDLogin.php?provider=google&mode=edit&user_id=<?php echo $user_id;?>"><img src="images/google_16.png" alt="Google" style="border: hidden; margin-right: 4px; "/>Google</a>
		<a href="#" id="facebookLoginLink"><img src="images/facebook_16.png" alt="Facebook" style="border: hidden; margin-right: 4px;"/>Facebook</a>
		<input type="checkbox" id="clearOpenID"> Disable Open ID Login
		<input type="text" id="openID_identity" name="openID_identity" size=60 
			value="<?php echo $user_details['user_identity']?>">
		<input type="hidden" id="openID_provider" name="openID_provider" value="<?php echo $user_details['user_provider'];?>"/>
		<br/><i>example: https://www.google.com/accounts/o8/id?id=IDString</i>
	</td>
</tr>
	<?php } 
	if (!$editMode) { ?>
<tr>
	<td align="right" valign="top">Enter Secure Code</td>
	<td><input type="text" name="captcha_code" size="10" maxlength="6" required/></td>
</tr>
<tr>
	<td colspan="2">
	<div style="width: 250px; margin-left: 21em;">
		<img id="captcha" src="libs/securimage/securimage_show.php" alt="CAPTCHA Image" />
		<a href="#" onclick="document.getElementById('captcha').src = 'libs/securimage/securimage_show.php?' + Math.random(); return false">[ Get Different Image ]</a>
		</div>
		<br/>
	</td>
</tr>
	<?php } ?>
<tr>
	<td align="center" colspan="2">
		<input type="submit" name="sm_submit" value="<?php 
		if ($editMode) 
			echo $LangUI->_('Update');
		else 
			echo $LangUI->_('Register');
		?>" class="button">
	<?php if ($editMode && $SMObj->checkAccessLevel($SMObj->getSuperUserLevel())) {?>
		<input type="submit" name="sm_delete" value="<?php echo $LangUI->_('Delete');?>" class="button">
	<?php } ?>
	</td>
</tr>
<input type="hidden" name="sm_submit_form" value="yes">
</form>
</table>