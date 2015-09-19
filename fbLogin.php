<?php

require 'libs/facebook/src/facebook.php';

$facebook = new Facebook(array(
  'appId'  => $g_facebook_appId,
  'secret' => $g_facebook_secret,
));

// See if there is a user from a cookie
$user = $facebook->getUser();
$error = null;

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    $error = '<pre>'.htmlspecialchars(print_r($e, true)).'</pre>';
    $user = null;
  }
}

?>

<?php if ($user) 
{ 
	$_SESSION['openID_provider'] = "facebook";
	$_SESSION['openID_identity'] = $user_profile['email'];
	echo '<html><head><META HTTP-EQUIV="refresh" CONTENT="0;URL=./index.php"></head>';
	echo '<body>';
	//echo htmlspecialchars(print_r($user_profile, true));
	echo '</body></html>';
} else { 
	echo "Login Failed.<br/>";
	echo $error;
} ?>
