<?php /* Smarty version 2.6.18, created on 2010-12-10 11:08:56
         compiled from /var/www/projects/watstemtmijnraad_hg/modules/user/pages/login/php/../header//indexPageBase.html */ ?>
<?php echo '<script type="text/javascript">
<!--//--><![CDATA[//><!--
function updateVisibility(form) {
	
}

function clearErrors() {
			document.getElementById(\'_err_username_0\').style.display = \'none\';
		document.getElementById(\'_err_password_0\').style.display = \'none\';
		document.getElementById(\'_err_password_1\').style.display = \'none\';

}

function validate(form) {
	var maySubmit = true;
			if (!form[\'username\'].value!=\'\') { document.getElementById(\'_err_username_0\').style.display = \'\'; maySubmit = false; }
		if (!form[\'password\'].value!=\'\') { document.getElementById(\'_err_password_0\').style.display = \'\'; maySubmit = false; }
		else if (!true) { document.getElementById(\'_err_password_1\').style.display = \'\'; maySubmit = false; }

//	if (!maySubmit)
//		location.hash = \'UserCreate\';
	return maySubmit;
}

//--><!]]>
</script>'; ?>