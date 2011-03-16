<?php /* Smarty version 2.6.18, created on 2010-12-14 09:52:50
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/selection/php/../content/recur_indexPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/selection/php/../content/recur_indexPage.html', 8, false),array('modifier', 'escape', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/selection/php/../content/recur_indexPage.html', 23, false),array('modifier', 'cat', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/selection/php/../content/recur_indexPage.html', 36, false),)), $this); ?>


<?php $_from = $this->_tpl_vars['nodes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['region']):
?>
	<?php if ($this->_tpl_vars['region']['show']): ?>
		<tr id="region_<?php echo $this->_tpl_vars['region']['id']; ?>
" class="<?php if ($this->_tpl_vars['level'] > 3): ?>collapsed<?php endif; ?> <?php echo $this->_tpl_vars['parent_path']; ?>
 par_<?php echo $this->_tpl_vars['parent']; ?>
">
		<td class="level<?php echo $this->_tpl_vars['region']['level']; ?>
">
			<?php if (( $this->_tpl_vars['level'] >= 3 ) && ( count($this->_tpl_vars['region']['children']) > 0 )): ?>
				<span>
					<a class="party_folding" href="javascript: void(0);" onclick="return toggleRegion(<?php echo $this->_tpl_vars['region']['id']; ?>
);">
						<img src="/images/expand.gif" id="image_<?php echo $this->_tpl_vars['region']['id']; ?>
" class="cross_image" width="16" height="16" />
					</a>
				</span>
			<?php else: ?>
				<span></span>
			<?php endif; ?>
							
			<?php if ($this->_tpl_vars['region']['show_link']): ?>
				<a href="/selection/region/<?php echo $this->_tpl_vars['region']['id']; ?>
"><?php echo $this->_tpl_vars['region']['name']; ?>
</a>
				</td><td class="options">
				<a class="edit" href="/regions/edit/<?php echo $this->_tpl_vars['region']['id']; ?>
"><img src="/images/edit.png" alt="Edit" title="Edit" border="0" /></a>
				<?php if ($this->_tpl_vars['global']->user->isSuperAdmin()): ?>
					<a class="delete" href="/regions/delete/<?php echo $this->_tpl_vars['region']['id']; ?>
" onclick="return confirm('Weet u zeker dat u de regio <?php echo ((is_array($_tmp=$this->_tpl_vars['region']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
 wilt verwijderen?');">
						<img src="/images/delete.png" alt="Verwijderen" title="Verwijderen" border="0" />
					</a>
				<?php endif; ?>
			<?php else: ?>
				<span></span>
				<?php echo $this->_tpl_vars['region']['name']; ?>

				</td><td>
			<?php endif; ?>
		</td>
		</tr>
		
		<?php if ($this->_tpl_vars['region']['children']): ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'recur_indexPage.html') : smarty_modifier_cat($_tmp, 'recur_indexPage.html')), 'smarty_include_vars' => array('nodes' => $this->_tpl_vars['region']['children'],'level' => ($this->_tpl_vars['level']+1),'child' => $this->_tpl_vars['region']['id'],'parent_path' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['parent_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, ' allpar_') : smarty_modifier_cat($_tmp, ' allpar_')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['region']['id']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['region']['id'])),'parent' => $this->_tpl_vars['region']['id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>