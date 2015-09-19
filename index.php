<?php
global $SMObj, $DB_LINK, $LangUI;
// Include the required files
require_once("includes/config_inc.php");
require_once("custom_inc.php"); 			// override the default settings here
require_once("libs/phpsm/core_API.php");
require_once("classes/LangUI.class.php");
require_once("classes/ShoppingList.class.php");
require_once("classes/Recipe.class.php");
require_once("classes/Ingredient.class.php");
require_once("includes/functions.php");
require_once("includes/mobile.php");
require_once("libs/securimage/securimage.php");

$sm_logout = isset( $_POST['sm_logout'] ) ? $_POST['sm_logout'] : '';
$mobileOverride = isset( $_GET['mobile'] ) && $_GET['mobile'] == "no" ? true : false;

// Create the security object and retrieve the database object
$SMObj = newSecurityModel("database"); // is one option
$SMObj->setDataSource($g_rb_database_type,$g_rb_database_host,$g_rb_database_user,$g_rb_database_password,$g_rb_database_name);
$SMObj->setDebug($g_rb_debug);
$SMObj->openDataSource(); // open the connection

$DB_LINK = $SMObj->getDataSource(); // get the database connection object (adodb)


// Global function, isValidID()
function isValidID($id) {
    if (empty($id)) return false;
    elseif ($id < 1) return false;
    elseif (version_compare(phpversion(), "5.2.0", ">=") && !filter_var($id, FILTER_VALIDATE_INT)) 
	return false;
    elseif (!is_numeric($id)) return false;
    else return true;
}

// Global Function, isValidLetter()
function isValidLetter($letter, $exception)
{
    if (empty($letter)) return false;
    elseif (strlen($letter) > 1) return false;
    elseif ($letter == $exception) return true;
    elseif (!preg_match("/[A-Z\s_]/i", $letter) > 0) return false;
    else return true;
}

$LangUI = new LangUI; // handles translations
global $g_browser_lang; // give global access to this information
$g_browser_lang = getBrowserLanguage();
// preg_match for letters, numbers, underscore, and hyphen. Reject anything else
$g_browser_lang = (preg_match('/^(\w|\-)+$/', $g_browser_lang)) ? $g_browser_lang : null;

// Load the language file based on config or logged in user
if ($SMObj->getUserID() != NULL) {
	$userID = $SMObj->getUserID();
	$details = $SMObj->getUserDetails($userID);
	include "lang/".$details['language'].".php";
} else if (isset($g_browser_lang)) {
	//we found a browser match load it if it exists
	if (file_exists('lang/'.$g_browser_lang.'.php'))
		include "lang/".$g_browser_lang.".php";
	else
		include "lang/".$g_rb_language.".php";
} else {
	//nothing matched, load the default
	include "lang/".$g_rb_language.".php";
}

// langArray is set in en.php, it.php...etc..
$LangUI->setLangArray( $langArray );
$LangUI->setEncoding($langEncoding);
$SMObj->setTranslationObject($LangUI); // pass that info on to the security manager

if ($sm_logout == "yes")
{
	$SMObj->logout();
}
else if (!$SMObj->isSecureLogin() && $SMObj->getUserLoginID() == "")
{
	// auto login
	if (!$SMObj->login())
	{
		$SMObj->addErrorMsg($SMObj->_('Login Failed!'));
	}
}
else if (isset($_SESSION['openID_identity']) && isset($_SESSION['openID_provider']))
{
	// try to login using OpenID
	if (!$SMObj->openIDLogin($_SESSION['openID_provider'], $_SESSION['openID_identity']))
	{
		$SMObj->addErrorMsg($SMObj->_('Login Failed!  Please try again.'));
		$_SESSION['openID_identity'] = null; // not a valid identity
	}
}
else if ($SMObj->getUserLoginID() == "")
{
	$sm_login_id = isset( $_POST['sm_login_id'] ) ? $_POST['sm_login_id'] : ''; 
	$sm_password = isset( $_POST['sm_password'] ) ? $_POST['sm_password'] : '';
	if ($sm_login_id != "") {
		// try login if they are passing us a login ID
		if (!$SMObj->login($sm_login_id,$sm_password)) {
			$SMObj->addErrorMsg($SMObj->_('Login Failed! Please try again.'));
		}
	}
}

// End of Login/Session stuff, now on to displaying the page //
// m = the module, cf modules directory, eg 'search'
// preg_match for letters, numbers, underscore, and hyphen. Reject anything else
$m = (isset($_GET['m']) && preg_match('/^(\w|\-)+$/', $_GET['m'])) ? $_GET['m'] : $g_rb_default_module;

// a = action, default is the index page of the module
// preg_match for letters, numbers, underscore, and hyphen. Reject anything else
$a = (isset($_GET['a']) && preg_match('/^(\w|\-)+$/', $_GET['a'])) ? $_GET['a'] : "";

if ($mobileOverride)
{
	$IsMobileBrowser = false;
	$mobile = false;
}
else
{
	//$IsMobileBrowser = true;
	$mobile = preg_match('/_mob$/', $a);
}

if ($a == "")
{
	if ($IsMobileBrowser)
	{
		$a = $g_rb_default_mobile_page;
		$mobile = true;
	}
	else
	{
		$a = $g_rb_default_page;
	}
}

// print = format for printing (minimal formating)
$print = isset( $_REQUEST['print'] ) ? $_REQUEST['print'] : 'no';
$format = isset( $_REQUEST['format'] ) ? $_REQUEST['format'] : 'yes';

// Load the header stuff
if ($mobile) {
	require "themes/$g_rb_theme/header_mob.php";
} else {
	require "themes/$g_rb_theme/header.php";
}


// preg_match for letters, numbers, underscore, and hyphen. Reject anything else
if (!empty($_REQUEST['dosql']) && preg_match('/^(\w|\-)+$/', $_REQUEST['dosql'])) {
	include "modules/$m/".$_REQUEST['dosql'].".php";
}

$msg = $SMObj->getErrorMsg();

if ($msg != '') {
	echo $msg . "<p>";
	$SMObj->setErrorMsg(''); //reset it, we have read it.
}

// Load the module that is requested
if (file_exists("modules/$m/$a.php")) {
	include "modules/$m/$a.php";
} else {
	include "modules/$g_rb_default_module/$g_rb_default_page.php";
}

if ($format == "yes")
{
	if ($mobile)
	{
		// And the default mobile footer to close things
		require "themes/$g_rb_theme/footer_mob.php";
	}
	else
	{
		// And the default footer to close things
		require "themes/$g_rb_theme/footer.php";
	}
}

// Save the session infor and clean things up
saveSecurityModel($SMObj);
$LangUI->cleanUp();
?>
