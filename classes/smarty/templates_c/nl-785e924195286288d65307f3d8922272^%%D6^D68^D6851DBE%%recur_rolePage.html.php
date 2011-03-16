<?php /* Smarty version 2.6.18, created on 2011-01-31 15:11:12
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/user/php/../content/recur_rolePage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/user/php/../content/recur_rolePage.html', 8, false),array('modifier', 'cat', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/user/php/../content/recur_rolePage.html', 28, false),)), $this); ?>


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
							
				<input class="checkbox" type="checkbox" name="regions[]" value="<?php echo $this->_tpl_vars['region']['id']; ?>
" <?php if (isset ( $this->_tpl_vars['selectedRegions'][$this->_tpl_vars['region']['id']] )): ?>checked="checked" <?php endif; ?>/><label><?php echo $this->_tpl_vars['region']['name']; ?>
</label>
				</td><td class="options">


				</td><td>

		</td>
		</tr>
		
		<?php if ($this->_tpl_vars['region']['children']): ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'recur_rolePage.html') : smarty_modifier_cat($_tmp, 'recur_rolePage.html')), 'smarty_include_vars' => array('nodes' => $this->_tpl_vars['region']['children'],'level' => ($this->_tpl_vars['level']+1),'child' => $this->_tpl_vars['region']['id'],'parent_path' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['parent_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, ' allpar_') : smarty_modifier_cat($_tmp, ' allpar_')))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['region']['id']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['region']['id'])),'parent' => $this->_tpl_vars['region']['id'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>
	<?php endif; ?>
<?php endforeach; endif; unset($_from); ?>