<?php /* Smarty version 2.6.18, created on 2011-01-13 08:55:08
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/style/header/formPage.html */ ?>
<script src="/javascripts/mooRainbow.js" type="text/javascript"></script>
<link rel="stylesheet" href="/stylesheets/mooRainbow.css" type="text/css" />
<script type="text/javascript">
<!--//--><![CDATA[//><!--
<?php echo '


window.addEvent(\'domready\', function() {
	var r1 = new MooRainbow(\'myRainbow1\', {
		\'id\': \'mr1\',
		\'startColor\': [0, 0, 0],
		\'wheel\': true,
		\'onComplete\': function(color) {
			$(\'color1\').value = color.hex.substring(1);
			$(\'color1preview\').style.background = color.hex;

			update_thumb($(\'color1\').value, $(\'color2\').value, $(\'color3\').value, $(\'color4\').value);
		}
	});

	var r2 = new MooRainbow(\'myRainbow2\', {
		\'id\': \'mr2\',
		\'startColor\': [0, 0, 0],
		\'wheel\': true,
		\'onComplete\': function(color) {
			$(\'color2\').value = color.hex.substring(1);
			$(\'color2preview\').style.background = color.hex;

			update_thumb($(\'color1\').value, $(\'color2\').value, $(\'color3\').value, $(\'color4\').value);
		}
	});

	var r3 = new MooRainbow(\'myRainbow3\', {
		\'id\': \'mr3\',
		\'startColor\': [0, 0, 0],
		\'wheel\': true,
		\'onComplete\': function(color) {
			$(\'color3\').value = color.hex.substring(1);
			$(\'color3preview\').style.background = color.hex;

			update_thumb($(\'color1\').value, $(\'color2\').value, $(\'color3\').value, $(\'color4\').value);
		}
	});

	var r4 = new MooRainbow(\'myRainbow4\', {
		\'id\': \'mr4\',
		\'startColor\': [0, 0, 0],
		\'wheel\': true,
		\'onComplete\': function(color) {
			$(\'color4\').value = color.hex.substring(1);
			$(\'color4preview\').style.background = color.hex;

			update_thumb($(\'color1\').value, $(\'color2\').value, $(\'color3\').value, $(\'color4\').value);
		}
	});

	r1.manualSet(\''; ?>
#<?php echo $this->_tpl_vars['formdata']['color1']; ?>
<?php echo '\', \'hex\');
	r2.manualSet(\''; ?>
#<?php echo $this->_tpl_vars['formdata']['color2']; ?>
<?php echo '\', \'hex\');
	r3.manualSet(\''; ?>
#<?php echo $this->_tpl_vars['formdata']['color3']; ?>
<?php echo '\', \'hex\');
	r4.manualSet(\''; ?>
#<?php echo $this->_tpl_vars['formdata']['color4']; ?>
<?php echo '\', \'hex\');

});
'; ?>

//--><!]]>
</script>