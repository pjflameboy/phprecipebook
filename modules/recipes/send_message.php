<?php
require_once('libs/phpmailer/class.phpmailer.php');

$recipe_id = (isset($_POST['recipe_id']) && isValidID( $_POST['recipe_id'] )) ? $_POST['recipe_id'] : 0;
$sendToAddress = isset($_POST['email_address']) ? $_POST['email_address'] : "";
$message = isset($_POST['message']) ? $_POST['message'] : "";

if ($recipe_id == 0 || $sendToAddress == "")
	die($LangUI->_('e-Mail address to send to and recipe ID are required to send a message'));
		
// Only trust logged in users to email
if (!$SMObj->isUserLoggedIn())
	die($LangUI->_('You must be logged into perform the requested action.'));

$htmlData = get_data($g_rb_fullurl . "index.php?m=recipes&a=view&print=yes&format=no&recipe_id=" . $recipe_id);
$htmlData = "<fieldset>" . $message . "</fieldset>" . $htmlData . 
	'<p><a href="' . $g_rb_fullurl . 'index.php?m=recipes&a=view&recipe_id=' . $recipe_id . '">Visit Recipe Website</a></p>';

function get_data($url)
{
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

$mail             = new PHPMailer();
$mail->IsSMTP(); // telling the class to use SMTP
//$mail->SMTPDebug  = 2;       // enables SMTP debug information (for testing)
$mail->SMTPAuth   = true;      // enable SMTP authentication

$mail->Host       = $g_email_host; // sets the SMTP server
$mail->Port       = $g_email_port; // set the SMTP port for the GMAIL server
$mail->Username   = $g_email_user; // SMTP account username
$mail->Password   = $g_email_password;   // SMTP account password

$mail->SetFrom($g_email_from, $g_email_from_name);
$mail->AddReplyTo($g_email_reply_to, $g_email_reply_to_name);
$mail->Subject    = "Recipe Shared by " . $SMObj->getUserName();
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($htmlData);

$mail->AddAddress($sendToAddress, "");

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
?>
