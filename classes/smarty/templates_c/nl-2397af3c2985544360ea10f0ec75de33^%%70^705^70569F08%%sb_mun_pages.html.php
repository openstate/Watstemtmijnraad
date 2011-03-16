<?php /* Smarty version 2.6.18, created on 2010-12-09 13:01:59
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/regions/php/../content//../includable/sb_mun_pages.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/regions/php/../content//../includable/sb_mun_pages.html', 6, false),array('modifier', 'default', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/regions/php/../content//../includable/sb_mun_pages.html', 6, false),)), $this); ?>
<?php if ($this->_tpl_vars['region'] && $this->_tpl_vars['pages']): ?>
<div class="mod nav-mun-info">
	<h3>Meer informatie over deze gemeenteraad</h3>
	<ul>
	<?php $_from = $this->_tpl_vars['pages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['page']):
?>
		<li><a href="/page/<?php echo $this->_tpl_vars['region']->id; ?>
/<?php echo $this->_tpl_vars['page']->url; ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['page']->title)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['page']->linkText)) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['page']->title) : smarty_modifier_default($_tmp, @$this->_tpl_vars['page']->title)))) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['page']->url) : smarty_modifier_default($_tmp, @$this->_tpl_vars['page']->url)))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</a></li>
	<?php endforeach; endif; unset($_from); ?>
	</ul>
</div>
<?php endif; ?>