<?php /* Smarty version 2.6.18, created on 2011-01-31 15:01:07
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../content//indexPageBase.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../content//indexPageBase.html', 8, false),array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../content//indexPageBase.html', 9, false),array('modifier', 'nl2br', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/regions/php/../content//indexPageBase.html', 9, false),)), $this); ?>
<a name="RegionList"></a><form action="" name="RegionList">
<table class="list">
				
		
		
		<tr><th><a href="?sortcol=name&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'name' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'name'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>">Naam</a></th><th><a href="?sortcol=level&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'level' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'level'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>"><?php if (! $this->_tpl_vars['hasSubs']): ?>Niveau<?php endif; ?></a></th><th><a href="?sortcol=parent&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'parent' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'parent'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>"><?php if (! $this->_tpl_vars['hasSubs']): ?>Is subniveau van<?php endif; ?></a></th><th>Opties</th></tr>
			<?php $_from = $this->_tpl_vars['formdata']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['dataloop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['dataloop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['datarow']):
        $this->_foreach['dataloop']['iteration']++;
?>
<tr class="link<?php echo smarty_function_cycle(array('values' => ', alt'), $this);?>
">
	<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['datarow']['region_name'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
	<td><?php echo $this->_tpl_vars['datarow']['level_name']; ?>
</td>
	<td><?php if ($this->_tpl_vars['datarow']['parent_name'] == ''): ?>Geen<?php else: ?><?php echo $this->_tpl_vars['datarow']['parent_name']; ?>
<?php endif; ?></td>
	<td><a class="edit" href="edit/<?php echo $this->_tpl_vars['datarow']['id']; ?>
"><img src="/images/edit.png" alt="Edit" title="Edit" border="0" /></a>
		<?php if ($this->_tpl_vars['datarow']['subs'] == 0): ?><a href="delete/<?php echo $this->_tpl_vars['datarow']['id']; ?>
" onclick="return confirm('Weet u zeker dat u de regio \'<?php echo $this->_tpl_vars['datarow']['region_name']; ?>
\' wilt verwijderen?');"><img src="/images/delete.png" alt="Verwijderen" title="Verwijderen" border="0" /></a><?php endif; ?>
					
				</td></tr>

<?php endforeach; endif; unset($_from); ?>

			</table>
</form>