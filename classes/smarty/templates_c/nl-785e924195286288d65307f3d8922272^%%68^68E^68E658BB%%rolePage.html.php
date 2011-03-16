<?php /* Smarty version 2.6.18, created on 2011-01-31 15:11:12
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/user/header/rolePage.html */ ?>
<?php echo '
<style type="text/css">
label {
	display: block;
	margin: 5px;
}

input {
	margin-right: 5px;
}
.collapsed {
	display: none;
}
</style>

'; ?>


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