<?php
	// Debuging, this is useful to turn on if you are getting a general error
	global $g_rb_debug;
	$g_rb_debug=FALSE; // prints out all the sql calls
	
	// Pick the theme for the site
	global $g_rb_theme,$g_rb_theme_hoverin, $g_rb_theme_hoverout;
	$g_rb_theme="default";
	
	##################################
	## Directory Settings 	  	##
	##################################
	
	global $g_sm_dir, $g_sm_url, $g_sm_adodb, $g_rb_basedir, $g_rb_baseurl, $g_rb_fullurl, 
		$g_sm_adodb, $g_sm_phpmailer;
	if (isset($_SERVER['PATH_TRANSLATED']))
		$g_rb_basedir = $_SERVER['PATH_TRANSLATED']; //"/var/www/html/dev/src/";
	$g_rb_basedir = str_replace("index.php", "", $g_rb_basedir);
	$g_rb_baseurl = $_SERVER['REQUEST_URI']; //"/dev/src/";
	$g_rb_baseurl = preg_replace("/(.*?)index\.php.*/i", "$1", $_SERVER['REQUEST_URI']);
	$g_rb_fullurl = "http://" . $_SERVER['SERVER_NAME'] . $g_rb_baseurl; 
	$g_sm_dir = $g_rb_basedir . "libs/phpsm/";
	$g_sm_url = $g_rb_baseurl . "libs/phpsm/";
	$g_sm_phpmailer = $g_rb_basedir . "libs/phpmailer/class.phpmailer.php";
	$g_sm_adodb = $g_rb_basedir . "libs/adodb5/adodb.inc.php";

	##################################
	## Database Connection options  ##
	##################################
	/* 
		Select one type: mysql,postgres,oracle..
		see adodb readme files for more options
	*/
	global $g_rb_database_type, $g_rb_database_host, $g_rb_database_name, $g_rb_database_user, $g_rb_database_password;
	$g_rb_database_type = "mysql";
	
	// Example PostgreSQL Settings
	/*$g_rb_database_host = "localhost:5432";
	$g_rb_database_name = "webdb2";
	$g_rb_database_user = "postgres";
	$g_rb_database_password = "";*/
	
	// Example MySQL settings
	$g_rb_database_host = "localhost:/var/lib/mysql/mysql.sock";
	$g_rb_database_name = "recipedb";
	$g_rb_database_user = "root";
	$g_rb_database_password = "";
	
	##################################################
	## E-Mail Settings                              ##
	##################################################
	global $g_email_host, $g_email_port, $g_email_user, $g_email_password,
		$g_email_reply_to, $g_email_from;
	$g_email_host = "mailserver.phprecipebook.com";
	$g_email_port = 587;
	$g_email_user = "manager@phprecipebook.com";
	$g_email_password = "";
	$g_email_reply_to = "manager@phprecipebook.com";
	$g_email_from = "manager@phprecipebook.com";
	$g_email_from_name = "PHPRecipeBook";
	
	############################################
	## Facebook Integration                   ##
	############################################
	global $g_facebook_appId, $g_facebook_secret;
	$g_facebook_appId = null;
  	$g_facebook_secret = null;
	
	##################################################
	## Security Options:				##
	## These options will effect the login system	##
	##################################################

	// Debuging, this is useful to turn on if you are getting a general error
	global $g_sm_debug, $g_sm_session_id;
	$g_sm_debug=$g_rb_debug;
	
	$g_sm_session_id="SMSessionID";	
	
	global $g_sm_open_reg, $g_sm_enable_sec, $g_sm_default_access_level, 
		$g_sm_autologin_user, $g_sm_autologin_passwd, $g_sm_user_privilages,
		$g_sm_admin_email, $g_sm_send_crlf, $g_sm_cleanup_files, 
		$g_sm_superuser_level, $g_sm_access_array, $g_sm_supported_languages,
		$g_sm_supported_countries;
	// Access Levels, do not change these unless you are familiar with the code
	$g_sm_access_array = array(
		 "READER" => 0 , 
		 "AUTHOR" => 30  ,
		 "EDITOR" => 60  ,
		 "ADMINISTRATOR" => 90
	);
	
	$g_sm_open_reg=FALSE;  					// allow anyone to register as a new user
	$g_sm_enable_sec=TRUE; 					// turn on/off the login system
	$g_sm_newuser_access_level=30;			// set the default security level of a user registering on their own:
	// ( 0 - Reader, 30 - Author, 60 - Editor, 90 - Administrator)
	
	$g_sm_superuser_level="ADMINISTRATOR";		// The access level that denotes super users/admins
	$g_sm_newuser_set_password = TRUE;		// This determines if new users can set their passwords, or if it is emailed to them	
	$g_sm_admin_email = $g_email_reply_to;		// set the system email address
	$g_sm_admin_name = $g_email_from_name; 	// name of admin in emails
	$g_sm_email_hosts = $g_email_host; 		// set the smtp hosts to use for mailing (you could use sendmail if you wanted).

	/* 
		If security is disabled, then make sure the following
	   two settings point to a valid user with at least Author access level (Admin would be best)
	*/
	$g_sm_autologin_user="admin";		// The username to automatically login as
	$g_sm_autologin_passwd="passwd";	// The password to login with
	
	global $g_rb_enable_ratings, $g_rb_show_ratings;
	$g_rb_enable_ratings = TRUE;		// Enable or disable the recipe ratings
	$g_rb_show_ratings = FALSE;		// Show the reviews/ratings on the recipe pages
	
	#######################################
	## 	Language Options:				 ##
	## this will set a default language  ##
	## for guests.			    		 ##
	#######################################
	global $g_rb_language;
	$g_rb_language = "en";
	// this is a fall back settings, first the user's preferences and browser settings will be checked.
	
	// List of supported languages
	$g_sm_supported_languages = array(
		'en' => 'English', 
		'it' => 'Italian',
		'fr' => 'French',
		'sv' => 'Swedish',
		'da' => 'Danish',
		'de' => 'German',
		'es' => 'Spanish',
		'nb' => 'Norwegian',
		'nl' => 'Dutch',
		'tr' => 'Turkey',
		'srl' => 'Serbian (Latin)',
		'et' => 'Estonian',
		'pt_BR' => 'Brazilian Portuguese',
		'hu' => 'Hungarian'
	);
	
	// If are in a country not listed, just add it here
	$g_sm_supported_countries = array(
		'us' => 'United States',
		'fr' => 'France',
		'it' => 'Italy',
		'de' => 'Germany',
		'sv' => 'Sweden',
		'da' => 'Denmark',
		'au' => 'Australia'
	);
	
	########################################
	## Misc Options						  ##
	########################################
	global $g_rb_pagerLimit;
	$g_rb_pagerLimit=20;  		// The number of recipes/ingredients to show per page
	global $g_rb_default_module, $g_rb_default_page, $g_rb_default_mobile_page;
	$g_rb_default_module = "recipes";
	$g_rb_default_page = "index";
	$g_rb_default_mobile_page = "index_mob";
	global $g_rb_image_resize_width;
	$g_rb_image_resize_width=110; // set to 0 to not resize
	date_default_timezone_set('America/New_York');
	
	########################################
	##  Constants			      ##
	########################################
	global $g_rb_max_picture_size, $g_rb_project_name, $g_rb_project_version, $g_rb_project_website;
	$g_rb_max_picture_size = "2000000"; // this sets the max upload size of a picture
	$g_rb_project_name = "PHPRecipeBook";
	$g_rb_project_version = "4.09"; // well not quite constant, but oh well.
	$g_rb_project_website ="phprecipebook.sourceforge.net";
	
	########################################
	## Sequence Names, this is only       ##
	## important if using PostgreSQL.     ##
	########################################
	global $g_rb_list_id_seq, $g_rb_recipe_id_seq, $g_rb_ingredient_id_seq;

	$g_rb_list_id_seq = "recipe_list_id_seq";
	$g_rb_recipe_id_seq = "recipe_recipe_id_seq";
	$g_rb_ingredient_id_seq = "recipe_ingredient_id_seq";
	$g_rb_restaurant_id_seq = "recipe_restaurant_id_seq";
	
	########################################
	## The table names		      ##
	##  You can change the table names    ##
	##  and prefixes, if you do though    ##
	##  make sure you change the names    ##
	##  in the database as well	      ##
	########################################
	global $db_prefix;
	$db_prefix = "recipe_"; // change this to change just the prefix, useful for databases with more than one app in them
	global $db_table_ethnicity,$db_table_units,$db_table_locations,$db_table_bases,$db_table_prep_time,
		$db_table_courses,$db_table_difficulty,$db_table_ingredients,$db_table_users,
		$db_table_recipes,$db_table_list_names,$db_table_list_recipes,$db_table_list_ingredients,
		$db_table_related_recipes,$db_table_favorites, $db_table_ratings, $db_table_reviews,
		$db_table_sources;
	$db_table_settings = $db_prefix . "settings";
	$db_table_users = "security_users";
	$db_table_ethnicity = $db_prefix . "ethnicity";
	$db_table_units = $db_prefix . "units";
	$db_table_locations = $db_prefix . "locations";
	$db_table_bases = $db_prefix . "bases";
	$db_table_prep_time = $db_prefix . "prep_time";
	$db_table_courses = $db_prefix . "courses";
	$db_table_difficulty = $db_prefix . "difficulty";
	$db_table_ingredients = $db_prefix . "ingredients";
	$db_table_ingredientmaps = $db_prefix . "ingredient_mapping";
	$db_table_recipes = $db_prefix . "recipes";
	$db_table_list_names = $db_prefix . "list_names";
	$db_table_list_recipes = $db_prefix . "list_recipes";
	$db_table_list_ingredients = $db_prefix . "list_ingredients";
	$db_table_related_recipes = $db_prefix . "related_recipes";
	$db_table_favorites = $db_prefix . "favorites";
	$db_table_stores = $db_prefix . "stores";
	$db_table_reviews = $db_prefix . "reviews";
	$db_table_ratings = $db_prefix . "ratings";
	$db_table_meals = $db_prefix . "meals";
	$db_table_mealplans = $db_prefix . "mealplans";
	$db_table_mealplan_items = $db_prefix . "mealplan_items";
	$db_table_restaurants = $db_prefix . "restaurants";
	$db_table_prices = $db_prefix . "prices";
	$db_table_sources = $db_prefix . "sources";
	$db_table_core_ingredients = $db_prefix . "core_ingredients";
	$db_table_core_weights = $db_prefix . "core_weights";
	global $db_fields;
	
	/*
		Used to refer to field names without knowing their names 
	*/
	$db_fields = array(
		$db_table_ethnicity => array( "ethnic_id", "ethnic_desc" ),
		$db_table_units => array( "unit_id", "unit_desc" ),
		$db_table_bases => array( "base_id", "base_desc" ),
		$db_table_prep_time => array( "time_id", "time_desc" ),
		$db_table_courses => array( "course_id", "course_desc" ),
		$db_table_difficulty => array( "difficult_id", "difficult_desc" ),
		$db_table_locations => array( "location_id", "location_desc"),
		$db_table_prices => array( "price_id", "price_desc"),
		$db_table_meals => array("meal_id", "meal_name"),
		$db_table_sources => array("source_id", "source_title", "source_desc")
	);
	
	// ---------------------------------------//
?>
