<?php
require_once("classes/MealPlan.class.php");
// Initalize the POST/GET Vars
$view = isset( $_GET['view'] ) ? $_GET['view'] : 'weekly';
$date = isset( $_GET['date'] ) ? $_GET['date'] : date('m-d-Y');

if ($SMObj->getUserLoginID() == NULL) 
	die($LangUI->_('You must be logged in to use the Meal Planner!'));

// Create a new meal plan object
global $MPObj;
$MPObj = new MealPlan($date);

// Setup the forward and backward links and the title
$forwardLink = NULL;
$backwardLink = NULL;
$title = NULL;
$subtitle = NULL; // title of what day/week/month we are in

if ($view == "daily")
{
	//-------------------------------------------------------------------------------
	// Daily View
	//-------------------------------------------------------------------------------
	$weekDay = date('w',mktime(0,0,0,$MPObj->currentMonth,$MPObj->currentDay,$MPObj->currentYear));
	$dbDate = DBUtils::dbDate($date); // get the date in ISO format so that we have the key
	$MPObj->load($dbDate,$dbDate); //just want this one day
	
	echo '<b>' . $MPObj->daysFull[$weekDay] . '</b>';
	echo '<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">';
	echo $MPObj->getMeals($dbDate,3);
	echo '</ul>';
}
else if ($view == "weekly")
{
	// Weekly view as default
	list($day,$month,$year) = $MPObj->getNextWeek($MPObj->currentDay, $MPObj->currentMonth, $MPObj->currentYear);
	$forwardLink="<a href=\"index.php?m=meals&view=weekly&date=$month-$day-$year\">" . $LangUI->_('Next Week') . "</a>\n";
	list($day,$month,$year) = $MPObj->getPreviousWeek($MPObj->currentDay, $MPObj->currentMonth, $MPObj->currentYear);
	$backwardLink="<a href=\"index.php?m=meals&view=weekly&date=$month-$day-$year\">" . $LangUI->_('Previous Week') . "</a>\n";
	$title = $LangUI->_('Weekly Meal Planner');
	$weekList = $MPObj->getWeekDaysList($MPObj->currentDay, $MPObj->currentMonth, $MPObj->currentYear);
	$subtitle = $LangUI->_('Week of') . " " . $MPObj->monthsFull[($MPObj->currentMonth-1)] . " " . $weekList[0][0] . " " . $MPObj->currentYear;
	?>
	
	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
	<li data-role="list-divider"><?php echo $subtitle;?></li>
	<?php foreach ($weekList as $d) {
		echo '<li>';
		if ($date == $d[1] . '-' . $d[0] . '-' . $d[2])
		{
			echo '<span class="ui-li-count ui-btn-up-c ui-btn-corner-all">TODAY</span>';
		}
		echo '<a  href="index.php?m=meals&view=daily&date=' . $d[1] . '-' . $d[0] . '-' . $d[2] . '">' . $d[1].'-'.$d[0] . '</a>';
		echo '</li>';
	}?>
	</ul>
<?php } ?>
