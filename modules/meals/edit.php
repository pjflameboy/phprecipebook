<?php
require_once("classes/MealPlan.class.php");
require_once("classes/DBUtils.class.php");
// Initalize the Vars
$date = isset( $_GET['date'] ) ? $_GET['date'] : date('m-d-Y');
$view = isset($_GET['view']) ? $_GET['view'] : "daily";
$dbDate = DBUtils::dbDate($date); // get the date in ISO format so that we have the key

if ($SMObj->getUserLoginID() == NULL) 
	die($LangUI->_('You must be logged in to use the Meal Planner!'));
	
// Create a new meal plan object
$MPObj = new MealPlan($date);
$MPObj->load($dbDate,$dbDate); //just want this one day
$minshow = 4;  // Min number of empty fields to show
$defaultServings = 2; // The default number of servings

// Read in a list of Meals and recipes
$rc = DBUtils::fetchColumn( $db_table_meals, 'meal_name', 'meal_id', 'meal_id' );
$mealList = DBUtils::createList($rc, 'meal_id', 'meal_name');

?>
<script type="text/javascript">
	$(document).ready(function() {
			$(".ui-widget").find("input[id^='recipeAuto_']").each(function()
			{
					$(this).autocomplete({
						source: "index.php?m=recipes&a=get&format=no",
						minLength: 1,
						select: function(event, ui) {
							var $target = $(event.target);
							var recipeIdName = getOtherFromName($target.attr("id"), "recipeId");
							var mealType = getOtherFromName($target.attr("id"), "meal_id");
							$(recipeIdName).val(ui.item.id);
							$(mealType).attr("required", "");
					 	}
					});
			});
			
			$('#saveMealPlanButton').click(function() {
					if ($('#EditMealPlanDayForm').valid())
					{
						$('#EditMealPlanDayForm').submit();
					}
			});
	});
	
	function getOtherFromName(nodeName, otherName)
	{
		var splitName = nodeName.split("_");
		return ("#" + otherName + "_" + splitName[1]);
	}
	
	
</script>

<form id="EditMealPlanDayForm" action="index.php?m=meals&dosql=update&view=<?php echo $view;?>&date=<?php echo $date;?>" method="post">
<table cellspacing="1" cellpadding="4" border="0" width=100% class="data">
<tr>
	<th align="center"><?php echo $LangUI->_('Delete');?></th>
	<th align="center"><?php echo $LangUI->_('Select a Meal');?></th>
	<th align="center"><?php echo $LangUI->_('Servings');?></th>
	<th align="center"><?php echo $LangUI->_('Repeat for');?></th>
	<th align="center"><?php echo $LangUI->_('Recipe');?></th>
</tr>
<?php
// Print out all the existing meals, and some new ones
for ($i = 0; $i < 
	(isset($MPObj->mealplanItems[$dbDate]) ? count($MPObj->mealplanItems[$dbDate]) : 0) + 
	$minshow; $i++) {
	if ($i < (isset($MPObj->mealplanItems[$dbDate]) ? count($MPObj->mealplanItems[$dbDate]) : 0)) {
		// If it is an existing meal item, then set it
		$meal = $MPObj->mealplanItems[$dbDate][$i]['meal'];
		$servings = $MPObj->mealplanItems[$dbDate][$i]['servings'];
		$recipe_id = $MPObj->mealplanItems[$dbDate][$i]['id'];
		$recipe_name = $MPObj->mealplanItems[$dbDate][$i]['name'];
	} else {
		// It is a new one, give it blank values
		$meal=NULL;
		$servings=$defaultServings;
		$recipe_id=NULL;
		$recipe_name=NULL;
	}
	echo "<tr>\n";
	echo '<td align="center">';
	echo '<input type="checkbox" name="delete_'.$i.'" value="yes"></td>';
	echo '<td align="center">';
	echo DBUtils::arrayselect( $mealList, 'meal_id_'.$i, 'size=1', $meal, true);
	echo "</td><td align=\"center\">\n";
	echo '<input type="text" autocomplete="off" name="servings_'.$i.'" value=' . $servings . ' size=3>';
	echo '</td><td align="center">';
	echo '<input type="text" autocomplete="off" name="repeat_'.$i.'" value=1 size=3> ' . $LangUI->_('Day(s)');
	echo '</td><td align="center">';
	echo "<div class=\"ui-widget\"><input id=\"recipeAuto_$i\" name=\"recipeAuto_$i\"value=\"$recipe_name\"/></div>";
	echo "<input type=\"hidden\" id=\"recipeId_$i\" name=\"recipeId_$i\" value=\"$recipe_id\">\n";
	echo "</td></tr>\n";
}
?>
</table>
<p>
<input type="button" id="saveMealPlanButton" value="<?php echo $LangUI->_('Save');?>" style="float: right;" class="button">
</form>
