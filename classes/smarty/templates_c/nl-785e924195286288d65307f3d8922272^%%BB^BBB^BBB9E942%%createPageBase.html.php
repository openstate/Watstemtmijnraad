<?php /* Smarty version 2.6.18, created on 2011-01-31 15:10:13
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/user/php/../content//createPageBase.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/user/php/../content//createPageBase.html', 7, false),array('function', 'html_radios', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/user/php/../content//createPageBase.html', 27, false),)), $this); ?>
<a name="BackofficeUserCreate"></a><form action="" name="BackofficeUserCreate" method="post" onsubmit="return formSubmit(this)" enctype="multipart/form-data">
<table class="form">
						
	 
	<tr>
		<th>Gebruikersnaam (E-mail):</th>
		<td><input type="text" name="username" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['username'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="username" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_username_0" style="<?php if (! $this->_tpl_vars['formerrors']['username_0']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span><span class="error" id="_err_username_1" style="<?php if (! $this->_tpl_vars['formerrors']['username_1']): ?>display:none<?php endif; ?>">De gebruikersnaam is niet beschikbaar</span><span class="error" id="_err_username_2" style="<?php if (! $this->_tpl_vars['formerrors']['username_2']): ?>display:none<?php endif; ?>">Ongeldige waarde</span></td>
	</tr>
	<tr>
		<th>Wachtwoord:</th>
		<td><input type="password" name="password" value="" id="password" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_password_1" style="<?php if (! $this->_tpl_vars['formerrors']['password_1']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span></td>
	</tr>
	<tr>
		<th>Wachtwoord opnieuw:</th>
		<td><input type="password" name="password2" value="" id="password2" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_password_0" style="<?php if (! $this->_tpl_vars['formerrors']['password_0']): ?>display:none<?php endif; ?>">De wachtwoorden komen niet overeen</span></td>
	</tr>
	<tr>
		<th>Voornaam:</th>
		<td><input type="text" name="firstname" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['firstname'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="firstname" /> </td>
	</tr>
	<tr>
		<th>Achternaam:</th>
		<td><input type="text" name="lastname" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['lastname'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="lastname" /> </td>
	</tr>
	<tr>
		<th>Geslacht:</th>
		<td><?php echo smarty_function_html_radios(array('name' => 'gender','options' => $this->_tpl_vars['genders'],'selected' => $this->_tpl_vars['formdata']['gender']), $this);?>
 <span class="error" id="_err_gender_0" style="<?php if (! $this->_tpl_vars['formerrors']['gender_0']): ?>display:none<?php endif; ?>">Ongeldige waarde</span></td>
	</tr>
	<tr>
		<td colspan="2" class="no-underline"><div class="container-buttons"><button class="submit" type="submit">Toevoegen</button></div></td>
	</tr>
</table>
</form><script type="text/javascript"><!--
updateVisibility(document.forms['BackofficeUserCreate']) //--></script>