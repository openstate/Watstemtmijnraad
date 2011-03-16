<?php /* Smarty version 2.6.18, created on 2010-12-15 14:44:27
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/appointments/php/../content/inc_app_regionPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/appointments/php/../content/inc_app_regionPage.html', 7, false),)), $this); ?>

<?php $_from = $this->_tpl_vars['appointments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['politician']):
?>
	<tr class="party_content pol_<?php echo $this->_tpl_vars['class']; ?>
 link">
    	<td></td>
        <td><?php echo $this->_tpl_vars['politician']['formated_name']; ?>
</td>
        <td><?php echo $this->_tpl_vars['politician']['cat_name']; ?>
</td>
        <td><?php if ($this->_tpl_vars['politician']['time_start'] != '-infinity'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['politician']['time_start'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
<?php else: ?>geen<?php endif; ?></td>
        <td><?php if ($this->_tpl_vars['politician']['time_end'] != 'infinity'): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['politician']['time_end'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
<?php else: ?>geen<?php endif; ?></td>
		<td>
			<a href="/politicians/profile/<?php echo $this->_tpl_vars['politician']['id']; ?>
" class="edit"><img src="/images/edit.png" alt="Wijzigen" title="Wijzigen" border="0" /></a>
			<a href="../delete/<?php echo $this->_tpl_vars['politician']['pid']; ?>
?localparty=<?php echo $this->_tpl_vars['party']->id; ?>
"><img src="/images/delete.png" alt="Verwijderen" title="Verwijderen" border="0" /></a>
					</td>
	</tr>
<?php endforeach; endif; unset($_from); ?>