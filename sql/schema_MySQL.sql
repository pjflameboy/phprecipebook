CREATE TABLE security_users (
	user_id INT NOT NULL AUTO_INCREMENT,
	user_login VARCHAR(32) NOT NULL UNIQUE,
	user_password VARCHAR(64) NOT NULL DEFAULT '',
	user_name VARCHAR(64) NOT NULL DEFAULT '',
	user_access_level INTEGER NOT NULL DEFAULT '0',
	user_language VARCHAR(8) DEFAULT 'en' NOT NULL,
	user_country VARCHAR(8) DEFAULT 'us' NOT NULL,
	user_date_created DATE,
	user_last_login DATE,
	user_email VARCHAR(64) NOT NULL UNIQUE,
	PRIMARY KEY (user_id));
	
CREATE TABLE security_providers (
	provider_id INT NOT NULL AUTO_INCREMENT,
	provider_name VARCHAR(64) NOT NULL UNIQUE,
	PRIMARY KEY (provider_id)
);
	
CREATE TABLE security_openid (
	login_id INT NOT NULL REFERENCES security_users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	provider_id INT NOT NULL REFERENCES security_providers(provider_id) ON DELETE CASCADE ON UPDATE CASCADE,
	user_identity VARCHAR(255) NOT NULL
);

CREATE TABLE recipe_settings ( 
	setting_name VARCHAR(32),
	setting_value VARCHAR(64),
	setting_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (setting_name, setting_user)
);

CREATE TABLE recipe_stores ( 
	store_id INT NOT NULL AUTO_INCREMENT,
	store_name VARCHAR(32) NOT NULL DEFAULT '',
	store_layout TEXT,
	store_user VARCHAR(32) NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (store_id));
	
CREATE TABLE recipe_ethnicity (
	ethnic_id INT NOT NULL AUTO_INCREMENT,
	ethnic_desc CHAR(64) NOT NULL,
	PRIMARY KEY(ethnic_id));

CREATE TABLE recipe_units (
	unit_id INT NOT NULL,
	unit_desc VARCHAR(64) NOT NULL,
	unit_abbr VARCHAR(8) NOT NULL,
	unit_system INT NOT NULL,
	unit_order INT NOT NULL,
	PRIMARY KEY(unit_id));

CREATE TABLE recipe_locations (
	location_id INT NOT NULL AUTO_INCREMENT,
	location_desc VARCHAR(64) NOT NULL,
	PRIMARY KEY(location_id));

CREATE TABLE recipe_bases (
	base_id INT NOT NULL AUTO_INCREMENT,
	base_desc VARCHAR(64) NOT NULL,
	PRIMARY KEY(base_id) );

CREATE TABLE recipe_prep_time (
	time_id INT NOT NULL AUTO_INCREMENT,
	time_desc VARCHAR(64) NOT NULL,
	PRIMARY KEY(time_id) );

CREATE TABLE recipe_courses (
	course_id INT NOT NULL AUTO_INCREMENT,
	course_desc VARCHAR(64) NOT NULL,
	PRIMARY KEY(course_id) );

CREATE TABLE recipe_difficulty (
	difficult_id INT NOT NULL AUTO_INCREMENT,
	difficult_desc VARCHAR(64),
	PRIMARY KEY(difficult_id));

CREATE TABLE recipe_core_ingredients (
	id INT NOT NULL,
	group_id INT NOT NULL,
	description VARCHAR(200) NOT NULL,
	short_description VARCHAR(60) NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE recipe_core_weights (
	id INT NOT NULL REFERENCES recipe_core_ingredients(id) ON DELETE SET NULL,
	sequence INT NOT NULL,
	amount INT,
	measure VARCHAR(80),
	weight INT,
	PRIMARY KEY (id, sequence)
);
CREATE TABLE recipe_ingredients (
	ingredient_id INT NOT NULL AUTO_INCREMENT,
	ingredient_core INTEGER REFERENCES recipe_core_ingredients(id) ON DELETE SET NULL,
	ingredient_name VARCHAR(120) NOT NULL,
	ingredient_desc MEDIUMTEXT,
	ingredient_location INT REFERENCES recipe_locations(location_id) ON DELETE SET NULL,
	ingredient_unit INTEGER REFERENCES recipe_units(unit_id) ON DELETE SET NULL,
	ingredient_solid BOOL,
	ingredient_system VARCHAR(8) DEFAULT 'usa',
	ingredient_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (ingredient_id));
ALTER TABLE recipe_ingredients add unique index(ingredient_name, ingredient_user);

CREATE TABLE recipe_sources (
	source_id INT NOT NULL AUTO_INCREMENT,
	source_title VARCHAR(64),
	source_desc MEDIUMTEXT,
	source_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (source_id));

CREATE TABLE recipe_recipes (
	recipe_id INT NOT NULL AUTO_INCREMENT,
	recipe_name VARCHAR(128) NOT NULL,
	recipe_ethnic INT REFERENCES recipe_ethnicity(ethnic_id) ON DELETE SET NULL,
	recipe_base INT REFERENCES recipe_base(base_id) ON DELETE SET NULL,
	recipe_course INT REFERENCES recipe_course(course_id) ON DELETE SET NULL,
	recipe_prep_time INT REFERENCES recipe_prep_time(time_id) ON DELETE SET NULL,
	recipe_difficulty INT REFERENCES recipe_difficulty(difficult_id) ON DELETE SET NULL,
	recipe_serving_size INT,
	recipe_directions LONGTEXT,
	recipe_comments MEDIUMTEXT,
	recipe_source INT REFERENCES recipes_sources(source_id) ON DELETE SET NULL,
	recipe_source_desc VARCHAR(200),
	recipe_modified DATE,
	recipe_picture MEDIUMBLOB,
	recipe_picture_type VARCHAR(32),
	recipe_private BOOL NOT NULL,
	recipe_system VARCHAR(16) DEFAULT 'usa' NOT NULL,
	recipe_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (recipe_id));

CREATE TABLE recipe_ingredient_mapping (
	map_recipe INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	map_ingredient INT NOT NULL REFERENCES recipe_ingredients(ingredient_id) ON DELETE CASCADE,
	map_quantity FLOAT NOT NULL,
	map_unit INT REFERENCES recipe_units(unit_id) ON DELETE SET NULL,
	map_qualifier VARCHAR(32),
	map_optional BOOL,
	map_order INT NOT NULL,
	PRIMARY KEY (map_ingredient,map_recipe));

CREATE TABLE recipe_list_names (
	list_id INT NOT NULL AUTO_INCREMENT,
	list_name VARCHAR(64) NOT NULL,
	list_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (list_id));

CREATE TABLE recipe_list_recipes (
	list_rp_id INT NOT NULL REFERENCES recipe_list_names(list_id) ON DELETE CASCADE,
	list_rp_recipe INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	list_rp_scale FLOAT DEFAULT 0.0,
	PRIMARY KEY (list_rp_id,list_rp_recipe));
	
CREATE TABLE recipe_list_ingredients (
	list_ing_id INT NOT NULL REFERENCES recipe_list_names(list_id) ON DELETE CASCADE,
	list_ing_ingredient INT NOT NULL REFERENCES recipe_ingredients(ingredient_id) ON DELETE CASCADE,
	list_ing_unit INT NOT NULL REFERENCES recipe_units(unit_id) ON DELETE SET NULL,
	list_ing_qualifier VARCHAR(32),
	list_ing_quantity FLOAT NOT NULL,
	list_ing_order INT,
	PRIMARY KEY (list_ing_id,list_ing_ingredient));

CREATE TABLE recipe_related_recipes (
	related_parent INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	related_child INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	related_required BOOL,
	related_order INT,
	PRIMARY KEY (related_parent, related_child));
	
CREATE TABLE recipe_favorites (
	favorite_recipe INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	favorite_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (favorite_user, favorite_recipe));
	
CREATE TABLE recipe_meals (
  	meal_id INT NOT NULL AUTO_INCREMENT,
	meal_name VARCHAR(64) NOT NULL UNIQUE,
	PRIMARY KEY (meal_id));

CREATE TABLE recipe_mealplans (
	mplan_date DATE NOT NULL,
	mplan_meal INT NOT NULL REFERENCES recipe_meals(meal_id) ON DELETE CASCADE,
	mplan_recipe INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	mplan_servings INT NOT NULL DEFAULT 0,
	mplan_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (mplan_date,mplan_meal,mplan_recipe,mplan_user));

CREATE TABLE recipe_reviews (
	review_recipe INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	review_comments VARCHAR(255) NOT NULL,
	review_date TIMESTAMP,
	review_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (review_recipe,review_comments,review_user));

CREATE TABLE recipe_ratings (
	rating_recipe INT NOT NULL REFERENCES recipe_recipes(recipe_id) ON DELETE CASCADE,
	rating_score INT NOT NULL DEFAULT 0,
	rating_ip VARCHAR(32) NOT NULL,
	PRIMARY KEY (rating_recipe, rating_ip));
	
CREATE TABLE recipe_prices (
	price_id INT NOT NULL AUTO_INCREMENT,
	price_desc VARCHAR(16),
	PRIMARY KEY (price_id));
	
CREATE TABLE recipe_restaurants (
	restaurant_id INT NOT NULL AUTO_INCREMENT,
	restaurant_name VARCHAR(64) NOT NULL,
	restaurant_address VARCHAR(128),
	restaurant_city VARCHAR(64),
	restaurant_state VARCHAR(2),
	restaurant_zip VARCHAR(16),
	restaurant_country VARCHAR(64),
	restaurant_phone VARCHAR(128),
	restaurant_hours TEXT,
	restaurant_picture MEDIUMBLOB,
	restaurant_picture_type VARCHAR(64),
	restaurant_menu_text TEXT,
	restaurant_comments TEXT,
	restaurant_price INT REFERENCES recipe_prices(price_id) ON DELETE SET NULL,
	restaurant_delivery BOOL,
	restaurant_carry_out BOOL,
	restaurant_dine_in BOOL,
	restaurant_credit BOOL,
	restaurant_website VARCHAR(254),
	restaurant_user INT NULL REFERENCES security_users(user_id) ON DELETE SET DEFAULT ON UPDATE CASCADE,
	PRIMARY KEY (restaurant_id));
	
INSERT INTO recipe_settings values ('MealPlanStartDay', '0', 1);
INSERT INTO security_users (user_login,user_password,user_name,user_access_level,user_country,user_email) VALUES ('admin', '76a2173be6393254e72ffa4d6df1030a', 'Administrator', '99','us','user@localhost');
INSERT INTO recipe_stores (store_name, store_layout, store_user) VALUES('default', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43', 1);
