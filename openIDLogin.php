<?php
require("libs/lightopenid/openid.php");

$provider = isset( $_GET['provider'] ) ? $_GET['provider'] : 'google'; // I like google
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$user_id = isset($_GET['user_id']) && isValidID($_GET['user_id']) ? $_GET['user_id'] : null;
$redirectTo = "index.php";

try {
    $openid = new LightOpenID($_SERVER['SERVER_NAME']);
    
    if(!$openid->mode) {
    	$openid->required = array('namePerson/friendly', 'namePerson/first', 'namePerson/last', 'contact/email', 'pref/language');
		if ($provider == "google")
		{
			$openid->identity = 'https://www.google.com/accounts/o8/id';
    	}
		header('Location: ' . $openid->authUrl());
    }

    if($openid->mode == 'cancel') 
    {
        echo 'Login Canceled!';
    } else {
		session_start();
		$_SESSION['openID_AddIdentity'] = null; // clear out just in case.
		
		if ($openid->validate())
		{
			$attribs = $openid->getAttributes();
			if ($mode == "create")
			{
				$_SESSION['openID_provider'] = $provider;
				$_SESSION['openID_identity'] = $openid->identity;
				$_SESSION['openID_name'] = $attribs['namePerson/first'] . " " . $attribs['namePerson/last'];
				$_SESSION['openID_email'] = isset($attribs['contact/email']) ? $attribs['contact/email'] : null;
				$_SESSION['openID_language'] = isset($attribs['pref/language']) ? $attribs['pref/language'] : "English";
				// Get the Login Name
				if (isset($attribs['namePerson/friendly'])) {
					$_SESSION['openID_login'] = $attribs['namePerson/friendly'];
				}
				else if (isset($attribs['contact/email']) && strpos($attribs['contact/email'], "@") !== false)
				{
					$parts = split('@', $attribs['contact/email']);
					echo print_r($parts);
					if (count($parts) > 1)
					{
						$_SESSION['openID_logon'] = $parts[0];
					}
				}
				$redirectTo = "index.php";
				$redirectTo = "index.php?m=account&a=addedit";
			}
			else if ($mode == "edit" && $user_id != null)
			{
				$_SESSION['openID_AddIdentity'] = $openid->identity;
				$_SESSION['openID_AddProvider'] = $provider;
				$redirectTo = "index.php?m=account&a=addedit&mode=edit&user_id=$user_id";
			}
			else
			{
				$_SESSION['openID_provider'] = $provider;
				$_SESSION['openID_identity'] = $openid->identity;
			}
			
			echo '<html><head><META HTTP-EQUIV="refresh" CONTENT="0;URL=' . $redirectTo . '"></head>';
			echo '<body>';
			//echo print_r($attribs);
			echo '</body></html>';
			
		}
    }
} catch(ErrorException $e) {
    echo $e->getMessage();
}

/* Private copy of IsValidID 
   TODO: refactor and move to functions
*/
function isValidID($id) {
    if (empty($id)) return false;
    elseif ($id < 1) return false;
    elseif (version_compare(phpversion(), "5.2.0", ">=") && !filter_var($id, FILTER_VALIDATE_INT)) 
	return false;
    elseif (!is_numeric($id)) return false;
    else return true;
}

?>
