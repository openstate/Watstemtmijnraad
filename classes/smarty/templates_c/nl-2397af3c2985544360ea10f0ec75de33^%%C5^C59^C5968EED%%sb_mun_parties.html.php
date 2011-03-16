<?php /* Smarty version 2.6.18, created on 2010-12-09 13:03:56
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../regions/includable/sb_mun_parties.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'truncate', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../regions/includable/sb_mun_parties.html', 11, false),)), $this); ?>
<?php if ($this->_tpl_vars['sb_parties']): ?>
	<?php $this->assign('parties', $this->_tpl_vars['sb_parties']); ?>
<?php endif; ?>

<h3>De gemeenteraad van <strong><?php echo $this->_tpl_vars['region']->name; ?>
</strong></h3>
<?php $this->assign('imageUrl', '/images/parties/'); ?>
<ul>
	<?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['party']):
?>
		<?php if ($this->_tpl_vars['party']->image != NULL): ?>
			<li><a href="<?php echo '/parties/party/'; ?>
<?php echo $this->_tpl_vars['party']->id; ?>
?region=<?php echo $this->_tpl_vars['region']->id; ?>
" class="has-img" title="<?php echo $this->_tpl_vars['party']->name; ?>
"><img src="<?php echo $this->_tpl_vars['imageUrl']; ?>
<?php echo $this->_tpl_vars['party']->image; ?>
" alt="Logo van <?php echo $this->_tpl_vars['party']->name; ?>
" /><?php if ($this->_tpl_vars['party']->short_form): ?><?php echo $this->_tpl_vars['party']->short_form; ?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['party']->name)) ? $this->_run_mod_handler('truncate', true, $_tmp, 30) : smarty_modifier_truncate($_tmp, 30)); ?>
<?php endif; ?></a></li>
		<?php else: ?>
			<li><a href="<?php echo '/parties/party/'; ?>
<?php echo $this->_tpl_vars['party']->id; ?>
?region=<?php echo $this->_tpl_vars['region']->id; ?>
" title="<?php echo $this->_tpl_vars['party']->name; ?>
"><?php if ($this->_tpl_vars['party']->short_form): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['party']->short_form)) ? $this->_run_mod_handler('truncate', true, $_tmp, 30) : smarty_modifier_truncate($_tmp, 30)); ?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['party']->name)) ? $this->_run_mod_handler('truncate', true, $_tmp, 30) : smarty_modifier_truncate($_tmp, 30)); ?>
<?php endif; ?></a></li>
		<?php endif; ?>
	<?php endforeach; else: ?>
		<li><a>Geen partijen gevonden</a></li>
	<?php endif; unset($_from); ?>
</ul>