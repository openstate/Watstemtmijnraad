<?php /* Smarty version 2.6.18, created on 2010-12-09 13:25:41
         compiled from /var/www/projects/watstemtmijnraad_hg/templates/search.html */ ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Wat stemt mijn raad</title>
		<link rel="stylesheet" href="/styles/main?noimg" type="text/css" media="all" />

		<script src="/javascripts/mootools/moo.tools.v1.11.js" type="text/javascript"></script>
		<?php if ($this->_tpl_vars['smartyData']['headerFile'] != ''): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['smartyData']['headerFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
	</head>
	<body>
		<div>
			<div id="content">
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['smartyData']['contentFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</div>
		</div>
	</body>
</html>