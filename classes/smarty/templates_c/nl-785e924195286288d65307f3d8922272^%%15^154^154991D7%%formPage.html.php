<?php /* Smarty version 2.6.18, created on 2011-01-13 10:17:33
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/pages/content/formPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/pages/admin/pages/content/formPage.html', 10, false),)), $this); ?>



<form action="<?php if ($this->_tpl_vars['target']): ?><?php echo $this->_tpl_vars['target']; ?>
<?php endif; ?>" name="<?php echo $this->_tpl_vars['form']['name']; ?>
" method="post" onsubmit="return formSubmit(this)" enctype="multipart/form-data">
	<h2><?php echo $this->_tpl_vars['form']['header']; ?>
</h2>
	<p><?php echo $this->_tpl_vars['form']['note']; ?>
</p>
	<table class="form">
		<tr>
			<th>URL</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['url'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><?php if ($this->_tpl_vars['special']): ?><input type="hidden" value="<?php echo $this->_tpl_vars['formdata']['url']; ?>
" name="url"/><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['url'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="url" value="<?php echo $this->_tpl_vars['formdata']['url']; ?>
" id="url" class="vld_required defErrorHandler" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_url_required" style="<?php if (! $this->_tpl_vars['formerrors']['url_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span> <span class="error" id="_err_url_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['url_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span> <span class="error" id="_err_url_exists" style="<?php if (! $this->_tpl_vars['formerrors']['url_exists']): ?>display:none<?php endif; ?>">De url bestaat al</span><?php endif; ?><?php endif; ?></td>
		</tr>
		<tr>
			<th>Titel</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['title'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="title" value="<?php echo $this->_tpl_vars['formdata']['title']; ?>
" id="title" class="vld_required defErrorHandler" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_title_required" style="<?php if (! $this->_tpl_vars['formerrors']['title_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span><?php endif; ?></td>
		</tr>
		<?php if ($this->_tpl_vars['formdata']['region']): ?>
		<tr>
			<th>Linktekst</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['linkText'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="linkText" value="<?php echo $this->_tpl_vars['formdata']['linkText']; ?>
" id="linkText" class="defErrorHandler" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_linkText_required" style="<?php if (! $this->_tpl_vars['formerrors']['linkText_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span><?php endif; ?></td>
		</tr>
		<tr>
			<th>Toon in menu</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php if ($this->_tpl_vars['formdata']['showInMenu']): ?>Ja<?php else: ?>Nee<?php endif; ?><?php else: ?><input type="checkbox" name="showInMenu" value="1" <?php if ($this->_tpl_vars['formdata']['showInMenu']): ?>checked="checked"<?php endif; ?> id="showInMenu" class="defErrorHandler" onkeyup="revalidate(this.form)" /><?php endif; ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th>Inhoud</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo $this->_tpl_vars['formdata']['content']; ?>
<?php else: ?><textarea name="content" id="text_content" class="richtext" cols="50" rows="10" onkeyup="revalidate(this.form)"><?php echo $this->_tpl_vars['formdata']['content']; ?>
</textarea> <span class="error" id="_err_content_required" style="<?php if (! $this->_tpl_vars['formerrors']['content_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span><?php endif; ?></td>
		</tr>
		<tr>
			<th/>
			<td class="no-underline"><div class="container-buttons"><button class="submit" type="submit" name="submit"><?php echo $this->_tpl_vars['form']['submitText']; ?>
</button></div></td>
		</tr>
	</table>
</form>