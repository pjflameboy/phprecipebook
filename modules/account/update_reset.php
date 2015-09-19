<?php

$email = isset( $_REQUEST['email'] ) ? $_REQUEST['email'] : '';
$captcha_code = isset( $_REQUEST['captcha_code'] ) ? $_REQUEST['captcha_code'] : '';

$securimage = new Securimage();
echo "<br/><br/>";
if ($securimage->check($captcha_code) == false) {
	// the code was incorrect
	// you should handle the error so that the form processor doesn't continue
	 
	// or you can use the following code if there is no validation or you do not know how
	echo $LangUI->_('The security code entered was incorrect please try again') . "<br /><br />";
	exit;
}

$SMObj->resetPassword($email);
?>
