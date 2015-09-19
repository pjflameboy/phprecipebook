
<script type="text/javascript">
$(document).ready(function() {
		$("#logoutButton").click(function() {
				$("#sm_logout").val("yes");
				$("#loginOutForm").submit();
		});
});
</script>
<form id="loginOutForm" name="loginForm" action="./index.php" method="post">
<?php if ($SMObj->getUserLoginID()) { ?>
<div class="sidebox">
	<?php echo $LangUI->_('Welcome') . " " . $SMObj->getUserName() . ": ";?>
	<input type="hidden" name="sm_logout" id="sm_logout" value=""/>
    <a href="#" id="logoutButton" style="margin-left: 10px"><?php echo $LangUI->_('Sign Out');?></a> 
    <a href="index.php?m=account&a=addedit&mode=edit" style="margin-left: 10px">Account Settings</a>
</div>

<?php } else { 
	$icon_size = 16;
?>

	<script type="text/javascript">
		$(document).ready(function() {
			$("#smloginField").focus();
		});
	</script>

	<!-- Desktop Login Style -->
	<div style="float: left;">
		<?php echo $LangUI->_('Username');?>:
		<input type="text" id="smloginField" name="sm_login_id" /><br />
		<?php echo $LangUI->_('Password');?>:
		<input type="password" name="sm_password"/>
		<input type="submit" value="<?php echo $LangUI->_('login');?>" class="button" />
	</div>

	<div style="float: right">
	<?php
		// only show the register link if allowed to, use admin link if admin
		if ($SMObj->isOpenRegistration())
			echo '<a href="index.php?m=account&a=addedit" rel="external">'.$LangUI->_('Create an Account').'</a>';
		?><br/>
		<a href="index.php?m=account&a=password_reset">Forgot your password?</a><br/>
		<div style="margin-top: 4px;">
		<a href="openIDLogin.php?provider=google"><img src="images/google_<?php echo $icon_size;?>.png" style="border: hidden;"/></a>
		<?php if (isset($g_facebook_appId)) { ?>
		<a href="index.php?m=account&a=fblogin"><img src="images/facebook_<?php echo $icon_size;?>.png" style="border: hidden;"/></a>
		<?php } ?>
		</div>
	</div>
<?php 
} ?>
</form>
