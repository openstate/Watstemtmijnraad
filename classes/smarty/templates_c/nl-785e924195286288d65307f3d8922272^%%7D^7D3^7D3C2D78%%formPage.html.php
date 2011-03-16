<?php /* Smarty version 2.6.18, created on 2011-01-11 11:04:16
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/politicians/content/formPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/politicians/content/formPage.html', 15, false),array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/politicians/content/formPage.html', 25, false),)), $this); ?>


<form action="" name="<?php echo $this->_tpl_vars['form']['name']; ?>
" method="post" onsubmit="return formSubmit(this)" enctype="multipart/form-data">
	<h2><?php echo $this->_tpl_vars['form']['header']; ?>
</h2>
	<p><?php echo $this->_tpl_vars['form']['note']; ?>
</p>
	<?php if ($this->_tpl_vars['confirm_deleting']): ?>
		<?php if ($this->_tpl_vars['del_votes_count']): ?><p><span style="color: red">Waarschuwing:</span> er worden <b><?php echo $this->_tpl_vars['del_votes_count']; ?>
</b> stemmen verwijderd!</p><?php endif; ?>
		<?php if ($this->_tpl_vars['del_appointments']): ?><p><span style="color: red">Waarschuwing:</span> er worden volgende aanstellingen verwijderd:</p>
		<div style="background:#eeeeee;">
			<table style="width: 100%">
				<tr><th>Partij</th><th>Regio</th><th>Aanvangsdatum</th><th>Einddatum</th><th>Link</th></tr>
				<?php $_from = $this->_tpl_vars['del_appointments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['app']):
?>
				<tr><td><?php echo $this->_tpl_vars['app']->party_name; ?>
</td>
					<td><?php echo $this->_tpl_vars['app']->region_name; ?>
</td>
					<td><?php if ($this->_tpl_vars['app']->time_start == @NEG_INFINITY): ?>Geen<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['app']->time_start)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
<?php endif; ?></td>
					<td><?php if ($this->_tpl_vars['app']->time_end == @POS_INFINITY): ?>Geen<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['app']->time_end)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
<?php endif; ?></td>
					<td><A href="/appointments/<?php echo $this->_tpl_vars['app']->politician; ?>
?all" title="Naar appointment: <?php echo $this->_tpl_vars['app']->id; ?>
"><img src="/images/page_white_go.png" border="0" alt="Go to <?php echo $this->_tpl_vars['app']->id; ?>
"></a></tr></tr>
				<?php endforeach; endif; unset($_from); ?>
			</table>
		</div><br><?php endif; ?>
	<?php endif; ?>
	<table class="form">
		<tr>
			<th>Titels</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['title'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="title" value="<?php echo $this->_tpl_vars['formdata']['title']; ?>
" onkeyup="revalidate(this.form)" /><?php endif; ?></td>
		</tr>
		<tr>
			<th>Voorletters</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['first_name'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="first_name" value="<?php echo $this->_tpl_vars['formdata']['first_name']; ?>
" onkeyup="revalidate(this.form)" /><?php endif; ?></td>
		</tr>
		<tr>
			<th>Achternaam</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['last_name'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="last_name" class="vld_required defErrorHandler" value="<?php echo $this->_tpl_vars['formdata']['last_name']; ?>
" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_last_name_required" style="<?php if (! $this->_tpl_vars['formerrors']['last_name_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span><?php endif; ?></td>
		</tr>
		<tr>
			<th>Geslacht</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php if ($this->_tpl_vars['formdata']['gender'] == 1): ?>Man<?php else: ?>Vrouw<?php endif; ?><?php else: ?><input class="radio" type="radio" name="gender" value="1"<?php if ($this->_tpl_vars['formdata']['gender'] !== 0): ?> checked="checked"<?php endif; ?>/>Man <input class="radio" type="radio" name="gender" value="0"<?php if ($this->_tpl_vars['formdata']['gender'] === 0): ?> checked="checked"<?php endif; ?>/>Vrouw<?php endif; ?></td>
		</tr>
		<tr>
			<th>Email</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['email'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="email" class="vld_email_optional defErrorHandler" value="<?php echo $this->_tpl_vars['formdata']['email']; ?>
" onkeyup="revalidate(this.form)"/> <span class="error" id="_err_email_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['email_invalid']): ?>display:none<?php endif; ?>">Ongeldig e-mailadres</span><?php endif; ?></td>
		</tr>
		<tr>
			<th>Extern ID</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['extern_id'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="extern_id" class="vld_optional defErrorHandler" value="<?php echo $this->_tpl_vars['formdata']['extern_id']; ?>
"/><?php endif; ?></td>
		</tr>
		
		<tbody style="background-color: #eee;">
			<tr><td colspan="2" align="center">Open social ID's</td></tr>
			<tr><th style="text-align:center;">Network name</th><th style="text-align:center;">Open social ID</th></tr>
			<?php $_from = $this->_tpl_vars['formdata']['opensocial_ids']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['opensoc']):
?>
				<tr>
					<td><input type="text" name="opensocial_names[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['opensocial_names'][$this->_tpl_vars['k']])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
"></td>
					<td><input type="text" name="opensocial_ids[]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['opensoc'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
">
						<img src="/images/delete.png" style="cursor: pointer;" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);" />
					</td>
				</tr>
			<?php endforeach; endif; unset($_from); ?>
			<tr>
				<td><input type="text" name="opensocial_names[]" value=""></td>
				<td><input type="text" name="opensocial_ids[]" value=""></td>
			</tr>
		</tbody>
		
		<tr>
			<td colspan="2" class="no-underline"><div class="container-buttons"><button class="submit" type="submit" name="submit"><?php echo $this->_tpl_vars['form']['submitText']; ?>
</button></div></td>
		</tr>
	</table>
</form>