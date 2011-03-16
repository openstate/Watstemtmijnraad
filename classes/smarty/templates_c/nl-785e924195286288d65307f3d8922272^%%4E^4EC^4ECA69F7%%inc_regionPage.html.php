<?php /* Smarty version 2.6.18, created on 2010-12-15 14:44:27
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/appointments/php/../content/inc_regionPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/appointments/php/../content/inc_regionPage.html', 4, false),array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/appointments/php/../content/inc_regionPage.html', 11, false),array('modifier', 'nl2br', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/appointments/php/../content/inc_regionPage.html', 11, false),array('modifier', 'cat', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/appointments/php/../content/inc_regionPage.html', 23, false),)), $this); ?>

		
		<?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['party_id'] => $this->_tpl_vars['party']):
?>
			<?php $this->assign('ct_children1', count($this->_tpl_vars['appointments'][$this->_tpl_vars['party_id']]['current'])); ?>
			<?php $this->assign('ct_children2', count($this->_tpl_vars['appointments'][$this->_tpl_vars['party_id']]['expired'])); ?>
			<?php $this->assign('ct_children', ($this->_tpl_vars['ct_children1']+$this->_tpl_vars['ct_children2'])); ?>
		
		<tbody class="party_row party_<?php echo $this->_tpl_vars['class']; ?>
">
            <tr class="alt">
                <td <?php if ($this->_tpl_vars['ct_children']): ?>class="cross_image" onclick="return toggleParty(this)"<?php endif; ?>></td>
                <td <?php if ($this->_tpl_vars['ct_children']): ?>onclick="return toggleParty(this)"<?php endif; ?>><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['party']->party_name)) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
 <?php if ($this->_tpl_vars['global']->user->isSuperAdmin()): ?> <a href="/party/edit/<?php echo $this->_tpl_vars['party']->party; ?>
">wijzigen</a> <a href="/party/delete/<?php echo $this->_tpl_vars['party']->party; ?>
">verwijderen</a><?php endif; ?></td>
                <td><?php if ($this->_tpl_vars['ct_children'] == 0): ?> Nog geen politici <?php else: ?><?php if ($this->_tpl_vars['ct_children1'] == 0): ?>Geen<?php else: ?><?php echo $this->_tpl_vars['ct_children1']; ?>
<?php endif; ?> actieve <?php if ($this->_tpl_vars['ct_children2'] > 0): ?>en <?php echo $this->_tpl_vars['ct_children2']; ?>
 inactieve <?php endif; ?>politici<?php endif; ?></td>
                <td></td>
                <td></td>
                <td><a class="add" href="../createParty/?localparty=<?php echo $this->_tpl_vars['party']->id; ?>
">Toevoegen</a></td>
            </tr>

            <?php if ($this->_tpl_vars['ct_children'] > 0): ?>
                <tr class="party_content">
                    <th></th><th>Politicus</th><th>Categorie</th><th>Aanvang</th><th>Einde</th><th>Acties</th>
                </tr>
                
            	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'inc_app_regionPage.html') : smarty_modifier_cat($_tmp, 'inc_app_regionPage.html')), 'smarty_include_vars' => array('appointments' => $this->_tpl_vars['appointments'][$this->_tpl_vars['party_id']]['current'],'party_class' => $this->_tpl_vars['class'],'class' => 'current')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            
            	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'inc_app_regionPage.html') : smarty_modifier_cat($_tmp, 'inc_app_regionPage.html')), 'smarty_include_vars' => array('appointments' => $this->_tpl_vars['appointments'][$this->_tpl_vars['party_id']]['expired'],'party_class' => $this->_tpl_vars['class'],'class' => 'expired')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            	
            	<tr class="party_content">
            		<td colspan="6" style="text-align: center;">
            			<a class="atvie_toggle" href="javascript: void(0);">Toon inactive aanstellingen</a>
						<a class="expired_toggle" href="javascript: void(0);" style="display: none;">Verberg inactive aanstellingen</a>
            		</td>
            	</tr>
            <?php endif; ?>
        </tbody>
        
		<?php endforeach; endif; unset($_from); ?>