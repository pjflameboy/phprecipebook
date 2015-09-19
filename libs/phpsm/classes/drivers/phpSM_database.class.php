<?php
include_once(SECURITYMANAGER_ADODB);

class SecurityManager_database extends SecurityManager {
	// the databse connection information
	var $_databaseType = NULL;
	var $_databaseHost = NULL;
	var $_databaseUser = NULL;
	var $_databasePasswd = NULL;
	var $_databaseName = NULL;
	var $_databaseLink = NULL;
	var $_databaseGroupSeq = NULL;
	
	// The tables we query
	var $_db_table_prefix = "security_";
	var $_db_table_users = "users";
	var $_db_table_members = "members";
	var $_db_table_groups = "groups";
	var $_db_table_openid = "openid";
	var $_db_table_providers = "providers";
	
	/*
		setDataSource: sets any parameters needed in order to read user and group
			information for user management
	*/
	function setDataSource($dbtype,$host,$user,$passwd,$dbname) {
		$this->_databaseType=$dbtype;
		$this->_databaseHost=$host;
		$this->_databaseUser=$user;
		$this->_databasePasswd=$passwd;
		$this->_databaseName=$dbname;
		if ($dbtype == "postgres") {
			$this->_databaseGroupSeq="SELECT currval('security_group_id_seq')";
		} else if ($dbtype == "mysql") {
			$this->_databaseGroupSeq="SELECT LAST_INSERT_ID()";
		} // if you have another type of db then add its sequence query here
	}
	
	/*
		getDataSource: convience function so that the database connection can be reused
	*/
	function getDataSource() {
		return ($this->_databaseLink);
	}
	
	/*
		openDataSource: opens the data source to read the user and group information.
	*/
	function openDataSource() {
		$this->_databaseLink = ADONewConnection($this->_databaseType);
		$this->_databaseLink->debug = $this->_debug;
		$this->_databaseLink->PConnect($this->_databaseHost, $this->_databaseUser, $this->_databasePasswd, $this->_databaseName);
	}
	
	/*
		closeDataSource: closes any open files or connections used to gather information about
			users and groups
	*/
	function closeDataSource() {}
	
	function printDBError($sql='') {
		if ($this->_debug)
		{
			echo '<b>'. $this->_databaseLink->ErrorMsg().'</font><p>';
			echo 'SQL: ' . $sql . '<br />';
		}
		else
		{
			echo '<b>';
			echo $this->_databaseLink->ErrorMsg();
			echo "<br/>";
			echo $this->_("An SQL error occured. Please contact the administrator or switch to debug mode for more details.");
			echo "</b>\n</p>";
		}
	}
	
	/*****************************************************
	* Open ID Functions
	*****************************************************/
	function getProviders()
	{
		$providers=array();
		$sql = "SELECT provider_id,provider_name FROM "  . $this->_db_table_prefix . $this->_db_table_providers;
		$rc = $this->_databaseLink->Execute( $sql );
		// error check
		if (!$rc) {
			$this->printDBError();
			return NULL;
		}
		
		while (!$rc->EOF) {
			$id = $rc->fields['provider_id'];
			$providers[$id] = $rc->fields['provider_name'];
			$rc->MoveNext();
		}
		
		return $providers;
	}
	
	function addProviderForUser($userId, $provider, $identity)
	{
		$providerId = null;
		$providers = $this->getProviders();
		while (list( $k, $v ) = each( $providers)) {
			if ($v == $provider)
			{
				$providerId = $k;
			}
		}
		if ($providerId != null)
		{
			$sql = "INSERT INTO " . $this->_db_table_prefix . $this->_db_table_openid . " (
				login_id,
				provider_id,
				user_identity)
				VALUES (
				$userId,
				$providerId,
				'".$this->_databaseLink->addq($identity, get_magic_quotes_gpc())."')";
			$rc = $this->_databaseLink->Execute( $sql );
			// error check
			if (!$rc) {
				$this->printDBError();
				return NULL;
			}	
		}
	}
	
	function removeAllProvidersForUser($userId)
	{
		$sql = "DELETE FROM " .  $this->_db_table_prefix . $this->_db_table_openid . 
		" WHERE login_id='".$userId."'";
		$rc = $this->_databaseLink->Execute($sql);
		if (!$rc) {
			$this->printDBError();
			return false;
		}
		return true;
	}


	/*******************************************************
	** User Function
	*******************************************************/
	
	/*
		login: basic login, given a username and password this function will attempt to get the information about
			the user, if information is found then it is saved and the user is considered 'logged in', if not it fails
			(returns false on failure, returns true on success).
	*/
	function login($login='',$password='') {
		if ($login=="" && $login=="") {
			$login = $this->_autoLoginUser;
			$password = $this->_autoLoginPasswd;
		}
		$sql = "SELECT * FROM "  . $this->_db_table_prefix . $this->_db_table_users . 
			" WHERE user_login = '$login' AND user_password = '" . md5($password) . "'";
		$rc = $this->_databaseLink->Execute( $sql );
		// store the user info
		if ($rc->RecordCount()==1) {
			$this->_userID = $rc->fields['user_id'];
			$this->_userLoginID = $rc->fields['user_login'];
			$this->_userName = $rc->fields['user_name'];
			$this->_userLanguage = $rc->fields['user_language'];
			$this->_userCountry = $rc->fields['user_country'];
			$this->_userAccessLevel = $rc->fields['user_access_level'];
			$this->_userDateCreated = $rc->fields['user_date_created'];
			$this->_userLastLogin = $rc->fields['user_last_login'];
			$this->_userEmail = $rc->fields['user_email'];

			// record when the user has logged in
			$sql = "UPDATE " . $this->_db_table_prefix . $this->_db_table_users .
			   " SET user_last_login=". $this->_databaseLink->DBDate(time()) .
			   " WHERE user_id='" . $this->_userID . "'";
			$rc = $this->_databaseLink->Execute( $sql );
			// error check
			if (!$rc) {
			  $this->printDBError();
			  return NULL;
			}
			
			return true;
		}
		return false;
	}
	
	function openIDLogin($provider, $identity)
	{
		$sql = "SELECT * FROM "  . $this->_db_table_prefix . $this->_db_table_users . " user" .
			" INNER JOIN security_openid oid ON oid.login_id = user.user_id " .
			" INNER JOIN security_providers p ON p.provider_id = oid.provider_id " . 
			" WHERE oid.user_identity = '".$this->_databaseLink->addq($identity, get_magic_quotes_gpc()) . "'" . 
			" AND p.provider_name = '" . $this->_databaseLink->addq($provider, get_magic_quotes_gpc()) . "'";
		$rc = $this->_databaseLink->Execute( $sql );
		// store the user info
		if ($rc->RecordCount()==1) {
			$this->_userID = $rc->fields['user_id'];
			$this->_userLoginID = $rc->fields['user_login'];
			$this->_userName = $rc->fields['user_name'];
			$this->_userLanguage = $rc->fields['user_language'];
			$this->_userCountry = $rc->fields['user_country'];
			$this->_userAccessLevel = $rc->fields['user_access_level'];
			$this->_userDateCreated = $rc->fields['user_date_created'];
			$this->_userLastLogin = $rc->fields['user_last_login'];
			$this->_userEmail = $rc->fields['user_email'];

			// record when the user has logged in
			$sql = "UPDATE " . $this->_db_table_prefix . $this->_db_table_users .
			   " SET user_last_login=". $this->_databaseLink->DBDate(time()) .
			   " WHERE user_id='" . $this->_userID . "'";
			$rc = $this->_databaseLink->Execute( $sql );
			// error check
			if (!$rc) {
			  $this->printDBError();
			  return NULL;
			}
			
			return true;
		}
		return false;
	}
	
	/*
		getUserDetails: Gets a users details and returns them in an associative array
	*/
	function getUserDetails($userId) {
		// do the query to get the user info
		$details = array();
		$sql = "SELECT * FROM " . $this->_db_table_prefix . $this->_db_table_users . " WHERE user_id='".$userId."'";
		$rc = $this->_databaseLink->Execute($sql);
		// error check
		if (!$rc) {
			$this->printDBError();
			return NULL;
		}
		// read the values
		if ($rc->RecordCount()>0) {
			$details['id'] = $rc->fields['user_id'];
			$details['login'] = $rc->fields['user_login'];
			$details['name'] = $rc->fields['user_name'];
			$details['access_level'] = $rc->fields['user_access_level'];
			$details['language'] = $rc->fields['user_language'];
			$details['country'] = $rc->fields['user_country'];
			$details['date_created'] = $rc->UserDate($rc->fields['user_date_created'],'m/d/Y');
			$details['last_login'] = $rc->UserDate($rc->fields['user_last_login'],'m/d/Y');
			$details['email'] = $rc->fields['user_email'];
		}
		
		// Select the first openID identity - change this later to find all.
		$sql = "SELECT user_identity, provider_name FROM " . $this->_db_table_prefix . $this->_db_table_openid . " I" .
			" INNER JOIN security_providers P ON P.provider_id = I.provider_id WHERE I.login_id = $userId";
		$rc = $this->_databaseLink->Execute($sql);
		
		if ($rc->RecordCount()>0) {
			$details['user_identity'] = $rc->fields['user_identity'];
			$details['user_provider'] = $rc->fields['provider_name'];
		}
		else
		{
			$details['user_identity'] = "";
			$details['user_provider'] = "";
		}
		
		return ($details);
	}

	/*
		getUserPassword: The user password should not be stored in session information, so it must be retrieved separatly when
			it needs to be compared.
	*/
	function getUserPassword($userId) {
		$sql = "SELECT user_password FROM " . $this->_db_table_prefix . $this->_db_table_users . " WHERE user_id='".$userId."'";
		$rc = $this->_databaseLink->Execute($sql);	
		// error check
		if (!$rc) {
			$this->printDBError();
			return NULL;
		}
		return $rc->fields['user_password'];
	}
	
	/*
		getUsers: Gets a list of users also with details
	*/
	function getUsers() {
		$users = array();
		/* you could just point to getUserDetails for each iteration, but with a database
			that would be kind of expensive.*/
		$sql = "SELECT * FROM " . $this->_db_table_prefix . $this->_db_table_users . " ORDER BY user_name";
		$rc = $this->_databaseLink->Execute($sql);
		// error check
		if (!$rc) {
			$this->printDBError();
			return NULL;
		}
		
		while (!$rc->EOF) {
			$details = array();
			// get the info
			$details['id'] = $rc->fields['user_id'];
			$details['login'] = $rc->fields['user_login'];
			$details['name'] = $rc->fields['user_name'];
			$details['access_level'] = $rc->fields['user_access_level'];
			$details['language'] = $rc->fields['user_language'];
			$details['country'] = $rc->fields['user_country'];
			$details['date_created'] = $rc->UserDate($rc->fields['user_date_created'],'m/d/Y');
			$details['last_login'] = $rc->UserDate($rc->fields['user_last_login'],'m/d/Y');
			$details['email'] = $rc->fields['user_email'];
			// now save the info
			$id = $rc->fields['user_id'];
			$users[$id] = $details;
			$rc->MoveNext();
		}
		return ($users);
	}
	
	/*
		Reset the password of a user and e-mail it
	*/
	function resetPassword($email)
	{
		$sql = "SELECT user_login, user_name, user_id FROM " . $this->_db_table_prefix . $this->_db_table_users . " WHERE user_email = '" .
			$this->_databaseLink->addq($email, get_magic_quotes_gpc()) . "'";
			
		$rc = $this->_databaseLink->Execute( $sql );
		if ($rc->RecordCount() == 0) {
			echo $this->_( 'Error: email address is not registered.' );
		} else {
			$newPassword = $this->createRandomPassword();
			$sql = "UPDATE " . $this->_db_table_prefix . $this->_db_table_users .
			   " SET user_password='". md5($newPassword) . "' WHERE user_id='" . $rc->fields['user_id'] . "'";
			$result = $this->_databaseLink->Execute($sql);
			if (!$result) {
				$this->printDBError();
				return false;
			}
			
			// mail out the password
			$subject = $this->_('PHPRecipeBook Password');
			$message = $this->_('Your password to login is included in this email below') . ":\n";
			$message .= $this->_('Login ID') . ":" . $rc->fields['user_login'] . "\n";
			$message .= $this->_('Password') . ":" . $newPassword . "\n";
			if ($this->sendEmail($email, $rc->fields['user_name'], $subject, $message))
			{
				echo $this->_( 'Password successfully reset, please check your email for new password.' );
			}
		}
	}
	/*
		addNewUser: Adds a new user and sets all information, this method does not check how has access to add new users
			that access level checking is left up to the form
	*/
	function addNewUser($login,$name,$password,$email,$language,$country,$provider,$identity,$access_level) {

		if (empty($login)) die($this->_("Login ID required to create a new user"));
		if (empty($name)) die($this->_("Name required to create a new user"));
		if (empty($password)) die($this->_("Password required to create a new user"));
		if (empty($email)) die($this->_("Email required to create a new user"));
		
		// new user, first check if login name exists
		$sql = "SELECT user_login FROM " . $this->_db_table_prefix . $this->_db_table_users . " WHERE user_login = '$login' OR user_email='$email'";
		$rc = $this->_databaseLink->Execute( $sql );
		if ($rc->RecordCount()) {
			echo $this->_( 'Login or eMail already exists.  Please try another login name / email or try password reset' );
		} else {
			// add the user
			$sql = "INSERT INTO " . $this->_db_table_prefix . $this->_db_table_users . " (
					user_login,
					user_password,
					user_name,
					user_email,
					user_language,
					user_country,
					user_date_created,
					user_access_level) 
				VALUES (
					'".$this->_databaseLink->addq($login, get_magic_quotes_gpc())."',
					'" . md5($password) . "',
					'".$this->_databaseLink->addq($name, get_magic_quotes_gpc())."',
					'".$this->_databaseLink->addq($email, get_magic_quotes_gpc())."',
					'".$this->_databaseLink->addq($language, get_magic_quotes_gpc())."',
					'".$this->_databaseLink->addq($country, get_magic_quotes_gpc())."'," . 
					$this->_databaseLink->DBDate(time()) . "," .
					$access_level . ")";

			$rc = $this->_databaseLink->Execute( $sql );
			// Check if it was successful
			if (!$rc) {
				$this->printDBError($sql);
				return 0;
			}
			
			$newUserId = 0;
			// Get the User ID
			$sql = "SELECT LAST_INSERT_ID()";
			$rc = $this->_databaseLink->Execute($sql);
			if ($rc) {
				$newUserId = $rc->fields[0];
			} else {
				$this->printDBError($sql);
				return 0;
			}
				
			if (isset($provider) && strlen($provider) > 0 && isset($identity) && strlen($identity) > 0)
			{
	
				$this->addProviderForUser($newUserId, $provider, $identity);
			}
			
			// everything was successfull.
			echo $this->_('Welcome') ." $name ".$this->_('You can now log in');
			return $newUserId;
		}
	}
	
	/*
		modifyUser: Modifies an existing user
	*/
	function modifyUser($userId,$name,$password,$email,$language,$country,$provider,$identity,$access_level) {
		// we are doing an update
		$sql = "UPDATE " . $this->_db_table_prefix . $this->_db_table_users .
			   " SET user_name='".$this->_databaseLink->addq($name, get_magic_quotes_gpc())."',
					user_language='".$this->_databaseLink->addq($language, get_magic_quotes_gpc())."',
					user_country='".$this->_databaseLink->addq($country, get_magic_quotes_gpc())."',
					user_email='".$this->_databaseLink->addq($email, get_magic_quotes_gpc())."'";
		if ($access_level != "")
			$sql .= ",user_access_level=$access_level";
		if ($password!="")
			$sql .= ",user_password='". md5($password) . "'";

		$sql .= " WHERE user_id='" . $userId . "'";
		$result = $this->_databaseLink->Execute($sql);
		if (!$result) {
			$this->printDBError();
			return false;
		}
		
		// Remove the currently set Provider
		$this->removeAllProvidersForUser($userId);
		if (isset($provider) && strlen($provider) > 0 && isset($identity) && strlen($identity) > 0)
		{
			// Add a new one
			$this->addProviderForUser($userId, $provider, $identity);
		}
		
		echo $this->_('User Updated') . ": " . $name . "<br />";
		return true;
	}
	
	
	/*
		deleteUser: deletes a user.
	*/
	function deleteUser($userId) {
		
		$this->removeAllProvidersForUser($userId);
		
		$sql = "DELETE FROM " .  $this->_db_table_prefix . $this->_db_table_users . 
			" WHERE user_id=".$userId;
		$rc = $this->_databaseLink->Execute($sql);
		if (!$rc) {
			$this->printDBError();
			return false;
		}
		
		echo $this->_('User Deleted') . "<br/>";
		return true;
	}
}

?>
