<?php 
$format = isset( $_REQUEST['format'] ) ? $_REQUEST['format'] : 'yes';
if ($format == "yes") {?>
	<br/>
	</div><!-- /content -->
	<div data-role="footer">
		<div style="text-align: center;">
		<?php if ($SMObj->checkAccessLevel("AUTHOR")) { ?>
			<a href="index.php?m=recipes&amp;a=addedit" data-role="button" data-icon="plus" rel="external"><?php echo $LangUI->_('Add Recipe'); ?></a>
		<?php } ?>
		</div>
	</div><!-- /header -->
<?php }?>
</div><!-- /page -->

</body>
</html>