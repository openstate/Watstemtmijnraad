<?php /* Smarty version 2.6.18, created on 2011-01-31 15:04:48
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../content//createPageBase.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../content//createPageBase.html', 7, false),array('function', 'html_options', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../content//createPageBase.html', 16, false),)), $this); ?>
<a name="RegionCreate"></a><form action="" name="RegionCreate" method="post" onsubmit="return formSubmit(this)" enctype="multipart/form-data">
<table class="form">
						
				
		<tr>
				<th>Naam</th>
				<td><input type="text" name="region_name" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['region_name'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="region_name" onkeyup="revalidate(this.form)" /> <span class="error" id="_err_region_name_0" style="<?php if (! $this->_tpl_vars['formerrors']['region_name_0']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span></td>
			</tr>
        <tr>
				<th>Hidden</th>
				<td><input type="checkbox" name="hidden" value="1" /></td>
			</tr>
		<?php if (! $this->_tpl_vars['hasSubs']): ?>
		<tr>
				<th><?php if (! $this->_tpl_vars['hasSubs']): ?>Is subniveau van<?php endif; ?></th>
				<td><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['parents'],'selected' => $this->_tpl_vars['formdata']['parent'],'name' => 'parent','id' => 'parent','onchange' => "javascript:setLevel(this.value);"), $this);?>
 </td>
			</tr>		
		
		<tr>
				<th>Niveau</th>
				<td><span id="level_name">Internationaal</span> </td>
			</tr>
		<tr>
				<th />
				<td><input type="hidden" name="level" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['level'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="level" /> <span class="error" id="_err_level_0" style="<?php if (! $this->_tpl_vars['formerrors']['level_0']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span></td>
			</tr>
		<?php else: ?>
		<tr>
				<th>Opmerking</th>
				<td>U kunt het niveau van deze regio niet aanpassen omdat er nog regio's onder hangen </td>
			</tr>
		<tr>
				<th><?php if (! $this->_tpl_vars['hasSubs']): ?>Is subniveau van<?php endif; ?></th>
				<td><input type="hidden" name="parent" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['parent'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="parent" /> </td>
			</tr>			
		<tr>
				<th />
				<td><input type="hidden" name="level" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['level'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
" id="level" /> <span class="error" id="_err_level_0" style="<?php if (! $this->_tpl_vars['formerrors']['level_0']): ?>display:none<?php endif; ?>">Dit veld is verplicht</span></td>
			</tr>
		<?php endif; ?>		
		
					
		<tr>
				<td colspan="2" class="no-underline"><div class="container-buttons"><button class="submit" type="submit">Toevoegen</button></div></td>
			</tr>
			</table>
</form><script type="text/javascript"><!--
updateVisibility(document.forms['RegionCreate']) //--></script>