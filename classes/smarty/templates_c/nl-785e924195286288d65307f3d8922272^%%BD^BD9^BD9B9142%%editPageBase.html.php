<?php /* Smarty version 2.6.18, created on 2011-01-31 15:01:02
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../header//editPageBase.html */ ?>
<?php echo '<script type="text/javascript">
<!--
function updateVisibility(form) {
	
}

function clearErrors() {
			document.getElementById(\'_err_region_name_0\').style.display = \'none\';
		document.getElementById(\'_err_level_0\').style.display = \'none\';
		document.getElementById(\'_err_level_0\').style.display = \'none\';

}

function validate(form) {
	var maySubmit = true;
			if (!form[\'region_name\'].value!=\'\') { document.getElementById(\'_err_region_name_0\').style.display = \'\'; maySubmit = false; }
		if (!form[\'level\'].value!=\'\') { document.getElementById(\'_err_level_0\').style.display = \'\'; maySubmit = false; }
		if (!form[\'level\'].value!=\'\') { document.getElementById(\'_err_level_0\').style.display = \'\'; maySubmit = false; }

//	if (!maySubmit)
//		location.hash = \'RegionEdit\';
	return maySubmit;
}

// -->
</script>'; ?>