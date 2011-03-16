<?php /* Smarty version 2.6.18, created on 2010-12-30 09:55:45
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/formPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/formPage.html', 18, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/formPage.html', 27, false),array('function', 'html_options', '/var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/formPage.html', 18, false),array('function', 'html_select_date', '/var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/formPage.html', 27, false),)), $this); ?>



<h2><?php echo $this->_tpl_vars['form']['header']; ?>
</h2>
<p><?php echo $this->_tpl_vars['form']['note']; ?>
</p>
<p><span class="error" id="_err_party_overlap" style="<?php if (! $this->_tpl_vars['formerrors']['party_overlap']): ?>display:none<?php endif; ?>">Kan de periode niet wijzigen, in dezelfde tijd werkt deze politicus for <?php echo $this->_tpl_vars['error_party']->name; ?>
. De politicus kan niet voor twee partijen tegelijk in dezelfde regio werken.</span></p>
<form action="" name="<?php echo $this->_tpl_vars['form']['name']; ?>
" method="post" onsubmit="return formSubmit(this)">

	<?php if ($this->_tpl_vars['formerrors']['votes_deleted']): ?>
		<p><span style="color: red">Waarschuwing:</span> er <?php if ($this->_tpl_vars['lost_votes'] == 1): ?>wordt <b><?php echo $this->_tpl_vars['lost_votes']; ?>
</b> stem<?php else: ?>worden <b><?php echo $this->_tpl_vars['lost_votes']; ?>
</b> stemmen<?php endif; ?> verwijderd!</p>
		<input type="hidden" name="commit" value="true" />
	<?php endif; ?>
	
	<table class="form">
		<?php if ($this->_tpl_vars['form']['showPolitician']): ?>
		<tr>
			<th>Politicus</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['politician_name'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp, 2, 'UTF-8') : htmlspecialchars($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['politicians'],'id' => 'politician','name' => 'politician','selected' => $this->_tpl_vars['formdata']['politician'],'class' => 'vld_required_select defErrorHandler'), $this);?>
 <a href="/appointments/createPolitician/?localparty=<?php echo $_GET['localparty']; ?>
" title="Politicus toevoegen"><img style="border: 0px" alt="Politicus toevoegen" src="/images/add.png"/></a><span class="error" id="_err_politician_required" style="<?php if (! $this->_tpl_vars['formerrors']['politician_required']): ?>display:none<?php endif; ?>">Ongeldige waarde geselecteerd</span><?php endif; ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th>Categorie</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['category_name'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp, 2, 'UTF-8') : htmlspecialchars($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><?php echo smarty_function_html_options(array('name' => 'category','options' => $this->_tpl_vars['categories'],'selected' => $this->_tpl_vars['formdata']['category']), $this);?>
 <span class="error" id="_err_category_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['category_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span><?php endif; ?></td>
		</tr>
		<tr>
			<th>Aanvangsdatum</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php if ($this->_tpl_vars['formdata']['time_start'] == '--'): ?>Geen<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['time_start'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
<?php endif; ?><?php else: ?><?php echo smarty_function_html_select_date(array('time' => $this->_tpl_vars['formdata']['time_start'],'field_order' => 'DMY','prefix' => 'TS_','day_empty' => "",'month_empty' => "",'year_empty' => "",'start_year' => -10,'end_year' => "+10"), $this);?>
 <span class="error" id="_err_time_start_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['time_start_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span><?php endif; ?></td>
		</tr>
		<tr>
			<th>Einddatum</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php if ($this->_tpl_vars['formdata']['time_end'] == '--'): ?>Geen<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['time_end'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
<?php endif; ?><?php else: ?><?php echo smarty_function_html_select_date(array('time' => $this->_tpl_vars['formdata']['time_end'],'field_order' => 'DMY','prefix' => 'TE_','day_empty' => "",'month_empty' => "",'year_empty' => "",'start_year' => -10,'end_year' => "+10"), $this);?>
 <span class="error" id="_err_time_end_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['time_end_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</span><span class="error" id="_err_time_negative" style="<?php if (! $this->_tpl_vars['formerrors']['time_negative']): ?>display:none<?php endif; ?>">De einddatum moet groter zijn dan aanvangsdatum</span><?php endif; ?></td>
		</tr>
		<tr>
			<th>Omschrijving</th>
			<td><?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['description'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp, 2, 'UTF-8') : htmlspecialchars($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="description" class="large" value="<?php echo $this->_tpl_vars['formdata']['description']; ?>
"/><?php endif; ?></td>
		</tr>
		<tr>
			<td colspan="2" class="no-underline"><div class="container-buttons"><button class="submit" type="submit" name="cancel">Terug</button> <button class="submit" type="submit" name="submit"><?php echo $this->_tpl_vars['form']['submitText']; ?>
</button></div></td>
		</tr>
	</table>
</form>