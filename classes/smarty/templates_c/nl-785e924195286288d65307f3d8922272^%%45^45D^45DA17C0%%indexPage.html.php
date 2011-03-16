<?php /* Smarty version 2.6.18, created on 2010-12-14 09:52:50
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/selection/header/indexPage.html */ ?>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
<?php echo '

function toggleRegion(id) {
	if(jQuery(\'#region_\' + id).hasClass(\'child_open\')) {
		jQuery(\'#region_\' + id + \', .allpar_\' + id).removeClass(\'child_open\');
		jQuery(\'.allpar_\' + id).addClass(\'collapsed\');
		jQuery(\'#image_\' + id + \', .allpar_\' + id + \' .cross_image\').attr(\'src\', \'/images/expand.gif\');
	} else {
		jQuery(\'#region_\' + id).addClass(\'child_open\');
		jQuery(\'.par_\' + id).removeClass(\'collapsed\');
		jQuery(\'#image_\' +  id).attr(\'src\', \'/images/collapse.gif\');
	}
}

'; ?>

//--><!]]>
</script>

<?php echo '
<style type="text/css">

.collapsed {
	display: none;
}
</style>
'; ?>

