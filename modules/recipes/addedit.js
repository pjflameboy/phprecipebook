
var ingredientDupeErrorHtml;// = "<li><?php echo $LangUI->_('Ingredients cannot be entered more then once per recipe.  Please combine the quantities or create a new recipe and list it as required recipe.');?></li>";
var relatedRecipeDupeErrorHtml; //= "<li><?php echo $LangUI->_('The same related recipe cannot be added twice.  Please remove duplicate related recipes.');?></li>"

$(document).ready(function() {
	$("#recipeAddEditForm").validate();
	
	$("#recipe_name").focus();
	$("#errorDialog").dialog({
		autoOpen: false,
		buttons: { "Ok": function() { $(this).dialog("close"); }},
		modal: true,
		width: 400
	});
	$("#addNewIngredientsDialog").dialog({
		autoOpen: false,
		title: "Add new ingredients",
		modal: true,
		width: 400,
		buttons: { "Close": function() { $(this).dialog('close'); } }
	});
	
	$('#addNewIngredientsLink').click(function() {
		$.get('index.php?m=ingredients&a=addedit&print=yes&format=no', function(data) {
			$('#addNewIngredientContent').html(data);
			$('#addNewIngredientsDialog').dialog('open');
		});
		return false;	
	});
	
	
	$(function() {
		$("#sortableTable1 tbody.content").sortable();
	});

	$(function() {
		$("#sortableTable2 tbody.content").sortable();
	});
	


	$(".ui-icon.ui-icon-trash").click(setupDeleteAction);
	$(".addItemsButton").click(addRows);
	$("#updateRecipeButton").click(updateRecipe);
	
	setupAutocompleteAction();
	setupHover();

	function addRows(event)
	{
		var $target = $(event.target);
		var targetName = "#" + $target.attr("addFor");
		var $targetRow = $(targetName).find("tr").last();
		var $targetBody = $(targetName).find("tbody");
		var $parentTable = $targetBody.parents(".data");
		var rowCount = $target.parent().find("#addCount").val();
		for (rowCount; rowCount > 0; rowCount--)
		{
			var $cloneRow = $targetRow.clone();
			$cloneRow.find(":input").each(function()
			{
				$(this).val("");
			});
			$targetBody.append($cloneRow);
		}
		reNumberTable($parentTable.attr("id"));
		setupHover();
	}
	
	function setupAutocompleteAction()
	{
		$(".ui-widget").find("input[id^='ingredientAuto_']").each(function()
		{
			$(this).autocomplete({
				source: "index.php?m=ingredients&a=get&format=no",
				minLength: 1,
				html: true,
				select: function(event, ui) {
					var $target = $(event.target);
					var ingredientIdName = getOtherFromName($target.attr("id"), "ingredientId");
					$(ingredientIdName).val(ui.item.id);
				}
			});
		});
		$(".ui-widget").find("input[id^='recipeAuto_']").each(function()
		{
			$(this).autocomplete({
				source: "index.php?m=recipes&a=get&format=no",
				minLength: 1,
				html: true,
				select: function(event, ui) {
					var $target = $(event.target);
					var recipeIdName = getOtherFromName($target.attr("id"), "relatedId");
					$(recipeIdName).val(ui.item.id);
				}
			});
		});
	}
	
	function setupHover()
	{
		$('.ui-state-default').hover(
			function(){ $(this).addClass('ui-state-hover'); }, 
			function(){ $(this).removeClass('ui-state-hover'); }
		);

		$('.ui-state-default').click(function(){
			$(this).toggleClass('ui-state-active');
		});
	}

	function setupDeleteAction(event)
	{
		var $target = $(event.target);
		var $parentTable = $target.parents(".data");
		$target.parent().parent().parent().remove();
		reNumberTable($parentTable.attr("id"));
	}

	function reNumberTable(tableId)
	{
		var i = -1;
		var tableSelector = "#" + tableId;
		$(tableSelector).find(".ui-icon.ui-icon-trash").click(setupDeleteAction);

		$(tableSelector).find("tr").each(function () 
		{
			$(this).find(":input").each(function()
			{
				var nodeName = $(this).attr('name');
				var splitName = nodeName.split("_");
				nodeName = splitName[0] + "_" + i;
				$(this).attr('name', nodeName);
				$(this).attr('id', nodeName);
			});
			i++;
		});
		
		setupAutocompleteAction();
	}
	
	function getOtherFromName(nodeName, otherName)
	{
		var splitName = nodeName.split("_");
		return ("#" + otherName + "_" + splitName[1]);
	}
	
	function updateRecipe(event)
	{
		reNumberTable("sortableTable1");
		reNumberTable("sortableTable2");
		submitIt();
	}
});


/*
	Do all the validation before submitting
*/
function submitIt() {

	var foundError = false;
	var uniqueRelatedArray = new Array();
	var uniqueIngredientArray = new Array();
	var foundIngredientDupe = false;
	var foundRelatedDupe = false;
	
	if ($("#recipeAddEditForm").valid())
	{
		var $errorDialogList = $("#errorList");
		$errorDialogList.empty();
		
		
		var $ingRows = $("#sortableTable1").find("tr");
		$ingRows.each(function()
		{
			var $currentRow = $(this);
			$inputItems = $(this).find(":input");
			$inputItems.each(function()
			{
				var inputName = $(this).attr('name');
				var inputValue = $(this).val();
				var splitParts = inputName.split('_');
				$(this).removeClass("ui-state-error");
				
				if (inputName.indexOf("ingredientId_") > -1 &&
				   (inputValue != null && inputValue != undefined && inputValue != ""))
				{
					var quantName = "#ingredientQuantity_" + splitParts[1];
					var quantity = $(quantName).val();
					if (quantity != "" && quantity != null && quantity != undefined)
					{
						if (jQuery.inArray(inputValue, uniqueIngredientArray) > -1)
						{
							if (!foundIngredientDupe)
							{
								// only add error once to list
								$errorDialogList.append(ingredientDupeErrorHtml);
							}
							foundError = true;
							foundIngredientDupe = true;
							// hight each error.
							$(this).addClass("ui-state-error");
						}
						else
						{
	
							uniqueIngredientArray.push(inputValue);
						}
					}
				}
			});
		});
		
		var $relatedRows = $("#sortableTable2").find("tr");
		$relatedRows.each(function()
		{
			var $currentRow = $(this);
			$inputItems = $(this).find(":input");
			$inputItems.each(function()
			{
				var inputName = $(this).attr('name');
				var inputValue = $(this).val();
				var splitParts = inputName.split('_');
				$(this).removeClass("ui-state-error");
				
				if (inputName.indexOf("relatedId_") > -1 &&
				   (inputValue != null && inputValue != undefined && inputValue != ""))
				{
					if (jQuery.inArray(inputValue, uniqueRelatedArray) > -1)
					{
						if (!foundRelatedDupe)
						{
							// only add error once to list
							$errorDialogList.append(relatedRecipeDupeErrorHtml);
						}
						foundError = true;
						foundRelatedDupe = true;
						// hilight each error.
						$(this).addClass("ui-state-error");
					}
					else
					{
	
						uniqueRelatedArray.push(inputValue);
					}
				}
			});
		});
		
		if (!foundError)
		{		
			// submit
			$("#dosql").val("update");
			this.document.forms.recipe_form.submit();
			return true;
		}
		else
		{
			$("#errorDialog").dialog('open'); 
			return false;
		}
	} else {
		alert(validationErrorsHtml);	
	}
}

// called by onchange on the input box converts on the fly any faction's and reject's non numbers
function fractionConvert(id)
{
        var teststring = id.value;
        var a=teststring.indexOf(",");      // change "," to "." (in all languages)
        if ( a != -1 ) {                   //FIXME: bug - still displays "." for all languages
                id.value=teststring=teststring.substring(0,a)+"."+teststring.substring(a+1,teststring.length)
        }
        
        if (isNaN(teststring))
        {
		if (teststring.indexOf("/")>0) {
                        if (teststring.indexOf(" ")>0) {
                                n = teststring.substring(0,teststring.indexOf(" ")+1);
                                f = teststring.substring(teststring.indexOf(" ")+1);
                        } else {
                                n = teststring.substring(0,teststring.indexOf("/")-1);
                                f = teststring.substring(teststring.indexOf("/")-1);
                        }//if(teststring.indexOf(" "))
                        if (isNaN(n)){alert(numberErrorHtml);return;}//Make shure we have a number
                        var newArray = f.split("/");
                        if (isNaN(newArray[0])){alert(numberErrorHtml);return;}//Make shure we have a number
                        if (isNaN(newArray[1])){alert(numberErrorHtml);return;}
                        id.value = eval((n*1)+(newArray[0]/newArray[1]));//write the new value to the calling box
                } else {
                        alert(numberErrorHtml)
                }
        }
}

