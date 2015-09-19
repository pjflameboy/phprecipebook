<?php
$sm_userId = isset( $_REQUEST['sm_userId'] ) ? $_REQUEST['sm_userId'] : '';
$sm_login = isset( $_REQUEST['sm_login'] ) ? $_REQUEST['sm_login'] : '';
$sm_password = isset( $_REQUEST['sm_password'] ) ? $_REQUEST['sm_password'] : '';
$sm_old_password = isset( $_REQUEST['sm_old_password'] ) ? $_REQUEST['sm_old_password'] : '';
$sm_name = isset( $_REQUEST['sm_name'] ) ? $_REQUEST['sm_name'] : '';
$sm_email = isset( $_REQUEST['sm_email'] ) ? $_REQUEST['sm_email'] : '';
$sm_language = isset( $_REQUEST['sm_language'] ) ? $_REQUEST['sm_language'] : 'en';
$sm_country = isset( $_REQUEST['sm_country'] ) ? $_REQUEST['sm_country'] : 'us';
$sm_access_level = isset( $_REQUEST['sm_access_level'] ) ? $_REQUEST['sm_access_level'] : $SMObj->getNewUserAccessLevel(); // sets the default user level
$sm_mode = isset( $_REQUEST['sm_mode'] ) ? $_REQUEST['sm_mode'] : "new";
$sm_delete = isset( $_REQUEST['sm_delete'] ) ? "yes" : "no";
$sm_submit_form = isset( $_REQUEST['sm_submit_form'] ) ? "yes" : "no";
$sm_provider = isset($_REQUEST["openID_provider"]) ? $_REQUEST["openID_provider"] : "";
$sm_identity = isset($_REQUEST["openID_identity"]) ? $_REQUEST["openID_identity"] : "";
$captcha_code = isset( $_REQUEST['captcha_code'] ) ? $_REQUEST['captcha_code'] : '';
$create_ingredients = isset( $_REQUEST['create_ingredients'] ) ? $_REQUEST['create_ingredients'] : '';
// Email will be identity for FaceBook
if ($sm_provider == "facebook")
{
	$sm_identity = $sm_email;
}

if ($sm_mode == "new") {
	// make sure we are admin or it is an open reg system
	if (!$SMObj->isOpenRegistration() && !$SMObj->checkAccessLevel($SMObj->getSuperUserLevel()))
	{
		die($LangUI->_('This system is not in open registration mode, only the administrator can add users'));
	}
	
	// If it is an admin adding the value then let the type be set
	if ($SMObj->checkAccessLevel($SMObj->getSuperUserLevel()))
		$new_access_level = $sm_access_level;
	else 
		$new_access_level = $SMObj->getNewUserAccessLevel();
	
	$securimage = new Securimage();
	if ($securimage->check($captcha_code) == false) {
		// the code was incorrect
		// you should handle the error so that the form processor doesn't continue
		 
		// or you can use the following code if there is no validation or you do not know how
		echo $LangUI->_('The security code entered was incorrect please try again') . "<br /><br />";
		exit;
	}

	if (!$SMObj->getNewUserSetPasswd() && (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel()))) {
		// we need to set the password and mail it to the user
		$sm_password = $SMObj->createRandomPassword();
	}
	
	// create the user
	$newUserId = $SMObj->addNewUser($sm_login,$sm_name,$sm_password,$sm_email,$sm_language,$sm_country,$sm_provider, $sm_identity,$new_access_level);
	if ($newUserId > 0) {
		// Handle the password emailing, if admin is not creating user
		if (!$SMObj->getNewUserSetPasswd() && (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel())))	{
			// mail out the password
			$subject = $LangUI->_('PHPRecipeBook Password');
			$message = $LangUI->_('Your password to login is included in this email below') . ":\n";
			$message .= $LangUI->_('Login ID') . ":" . $sm_login . "\n";
			$message .= $LangUI->_('Password') . ":" . $sm_password . "\n";
			$SMObj->sendEmail($sm_email, $sm_name, $subject, $message);
		}
		if ($create_ingredients == "true") {
			$sql = "INSERT INTO recipe_ingredients (ingredient_name, ingredient_desc, ingredient_location, ingredient_unit, ingredient_solid, ingredient_system, ingredient_user) 
				SELECT ingredient_name, ingredient_desc, ingredient_location, ingredient_unit, ingredient_solid, ingredient_system, $newUserId 
				FROM   recipe_ingredients
				WHERE  ingredient_user = 1";
				
			$rc = $DB_LINK->Execute( $sql );
			DBUtils::checkResult($rc, NULL, $LangUI->_('There was an error copying the ingredients'), $sql);
		}
	}

} else if ($sm_mode == "edit") {
	if ($sm_delete == "no") {
		if (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel()) && $SMObj->getUserLoginID() != $sm_login)
			die($LangUI->_('You must be an administrator in order to edit other users!'));
		
		// only the admin can change access levels and groups
		if (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel())) {
			$sm_access_level="";
		}
		
		// If a user is changing the password, make sure the know the old one first
		if ($sm_password != "" && 
			(($SMObj->getUserPassword($sm_userId) != md5($sm_old_password)) && 
			(!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel())))) {
				die($LangUI->_('Old password does not match currently set password!'));
		}
		// all good, go modify the user
		$SMObj->modifyUser($sm_userId,$sm_name,$sm_password,$sm_email,$sm_language,$sm_country,$sm_provider,$sm_identity,$sm_access_level);
		
	} else {
		if (!$SMObj->checkAccessLevel($SMObj->getSuperUserLevel())) 
			die($LangUI->_('You must be an administrator in order to delete users!'));
		
		// delete the user and it's associations with groups
		$SMObj->deleteUser($sm_userId);
	}
}
?>