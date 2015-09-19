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
	<input type="hidden" name="sm_logout" id="sm_logout" value=""/>
    <a href="#" id="logoutButton" style="margin-left: 10px"><?php echo $LangUI->_('Sign Out');?></a> 
    <a href="index.php?m=account&a=addedit&mode=edit" style="margin-left: 10px">Account Settings</a>
</div>

<?php } else { 
	$icon_size = 32
	?>

	<script type="text/javascript">
		$(document).ready(function() {
			$("#smloginField").focus();
		});
	</script>
	<!-- Mobile Login Style -->
   <?php echo $LangUI->_('Username');?>:
   <input type="text" id="smloginField" name="sm_login_id" /><br />
   <?php echo $LangUI->_('Password');?>:
   <input type="password" name="sm_password"/>
   <input type="submit" value="<?php echo $LangUI->_('login');?>" class="button" />

	<br/>
	<a href="index.php?m=account&a=password_reset">Forgot your password?</a><br/>
	<br/>
	<a href="openIDLogin.php?provider=google" rel="external" ><img src="images/google_<?php echo $icon_size;?>.png" style="border: hidden;"/></a>
	<a href="openIDLogin.php?provider=facebook" rel="external"><img src="images/facebook_<?php echo $icon_size;?>.png" style="border: hidden;"/></a>
	</div>
<?php 
} ?>
</form>

