ALTER TABLE recipe_ingredients DROP COLUMN ingredient_price;
ALTER TABLE recipe_ingredients drop index ingredient_name;
ALTER TABLE recipe_ingredients add unique index(ingredient_name, ingredient_user);

