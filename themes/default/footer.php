	</div> <!-- Main content -->
</div> <!-- parent -->
<?php if ($format == "yes") { ?>
<script type="text/javascript">
$(document).ready(function() { 
	// Setup the menu last to give views the chance to add to the menu
	$('#actionMenu').NavMenu({
			content: $('#availableActions').html(), // grab content from this page
			showSpeed: 400
	});
});
</script>
<?php } ?>
<?php $DB_LINK->Close();?>
</body>
</html>
