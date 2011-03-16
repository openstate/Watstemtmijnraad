<?php /* Smarty version 2.6.18, created on 2011-01-13 08:55:08
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/style/content/formPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/pages/admin/style/content/formPage.html', 12, false),)), $this); ?>



<form action="" name="<?php echo $this->_tpl_vars['form']['name']; ?>
" method="post" onsubmit="return formSubmit(this)" enctype="multipart/form-data">
	<h2>Opmaak wijzigen</h2>
	
	<img class="preview" id="preview" src="/style/previewImage/?color1=<?php echo $this->_tpl_vars['formdata']['color1']; ?>
&color2=<?php echo $this->_tpl_vars['formdata']['color2']; ?>
&color3=<?php echo $this->_tpl_vars['formdata']['color3']; ?>
&color4=<?php echo $this->_tpl_vars['formdata']['color4']; ?>
" />
	
	<table class="form">
		<tr>
			<th>Kleur 1</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['color1'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?>
				<input type="text" name="color1" value="<?php echo $this->_tpl_vars['formdata']['color1']; ?>
" id="color1" class="vld_required defErrorHandler" onkeyup="revalidate(this.form)" />
				<input type="text" id="color1preview" style="background-color: #<?php echo $this->_tpl_vars['formdata']['color1']; ?>
" class="preview" disabled="disabled" />
				<img id="myRainbow1" src="/images/icon_colordropper.png" alt="[r1]" />
				<span class="error" id="_err_color1_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['color1_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span>
			<?php endif; ?></td>
		</tr>
		<tr>
			<th>Kleur 2</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['color2'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?>
				<input type="text" name="color2" value="<?php echo $this->_tpl_vars['formdata']['color2']; ?>
" id="color2" class="vld_required defErrorHandler" onkeyup="revalidate(this.form)" />
				<input type="text" id="color2preview" style="background-color: #<?php echo $this->_tpl_vars['formdata']['color2']; ?>
" class="preview" disabled="disabled" />
				<img id="myRainbow2" src="/images/icon_colordropper.png" alt="[r2]" />
				<span class="error" id="_err_color2_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['color2_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span>
			<?php endif; ?></td>
		</tr>
		<tr>
			<th>Kleur 3</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['color3'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?>
				<input type="text" name="color3" value="<?php echo $this->_tpl_vars['formdata']['color3']; ?>
" id="color3" class="vld_required defErrorHandler" onkeyup="revalidate(this.form)" />
				<input type="text" id="color3preview" style="background-color: #<?php echo $this->_tpl_vars['formdata']['color3']; ?>
" class="preview" disabled="disabled" />
				<img id="myRainbow3" src="/images/icon_colordropper.png" alt="[r3]" />
				<span class="error" id="_err_color3_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['color3_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span>
			<?php endif; ?></td>
		</tr>
		<tr>
			<th>Kleur 4</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['color4'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?>
				<input type="text" name="color4" value="<?php echo $this->_tpl_vars['formdata']['color4']; ?>
" id="color4" class="vld_required defErrorHandler" onkeyup="revalidate(this.form)" />
				<input type="text" id="color4preview" style="background-color: #<?php echo $this->_tpl_vars['formdata']['color4']; ?>
" class="preview" disabled="disabled" />
				<img id="myRainbow4" src="/images/icon_colordropper.png" alt="[r4]" />
				<span class="error" id="_err_color4_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['color4_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span>
			<?php endif; ?></td>
		</tr>
														<?php if (! $this->_tpl_vars['form']['freeze']): ?>
		<tr>
			<th style="vertical-align:top;">Logo</th>
			<td><?php if ($this->_tpl_vars['formdata']['logo']): ?><img style="border: 1px solid black; margin-bottom: 5px" src="/files/<?php echo $this->_tpl_vars['formdata']['logo']; ?>
"/><br/><?php endif; ?><input type="file" name="logo" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['logo'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="logo" /><br/><?php if (isset ( $this->_tpl_vars['formdata']['logo'] ) && $this->_tpl_vars['formdata']['logo'] != 'wsmr.gif'): ?><input type="checkbox" name="removeLogo">Logo verwijderen</input><?php endif; ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td colspan="2" class="no-underline"><div class="container-buttons"><button class="submit" type="submit" name="submit"><?php echo $this->_tpl_vars['form']['submitText']; ?>
</button></div></td>
		</tr>
	</table>
</form>