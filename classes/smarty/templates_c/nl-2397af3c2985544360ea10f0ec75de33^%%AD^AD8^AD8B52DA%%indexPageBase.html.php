<?php /* Smarty version 2.6.18, created on 2011-02-09 13:54:46
         compiled from /var/www/projects/watstemtmijnraad_hg/modules/user/pages/login/php/../content//indexPageBase.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/modules/user/pages/login/php/../content//indexPageBase.html', 5, false),)), $this); ?>
 
<a name="UserCreate"></a>
<form action="" name="UserCreate" method="post" onsubmit="return validate(this);" enctype="multipart/form-data">
		<strong class="label">E-mailadres:</strong>
		<input class="login field text" type="text" name="username" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['username'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="username" maxlength="40"/>
		<span class="error" id="_err_username_0" style="<?php if (! $this->_tpl_vars['formerrors']['username_0']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span>
		<br class="clear" />

		<strong class="label">Wachtwoord:</strong>
		<input class="login field password" type="password" name="password" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['password'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="password" maxlength="40" />
		<span class="error" id="_err_password_0" style="<?php if (! $this->_tpl_vars['formerrors']['password_0']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span>
		<span class="error" id="_err_password_1" style="<?php if (! $this->_tpl_vars['formerrors']['password_1']): ?>display:none<?php endif; ?>"><br />Geen geldig e-mailadres en/of wachtwoord</span>
		<br class="clear" />

		<input class="inputNoBorder" type="checkbox" name="cookie" id="cookie"<?php if ($this->_tpl_vars['formdata']['cookie']): ?> checked="checked"<?php endif; ?> /><strong class="checkbox">Onthouden</strong>
		<br class="clear" />
		
		<div class="container-buttons"><button class="submit" type="submit">Log in</button></div>
		<input type="hidden" name="destination" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['destination'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="destination" />
</form>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
	updateVisibility(document.forms['UserCreate'])
//--><!]]>
</script>