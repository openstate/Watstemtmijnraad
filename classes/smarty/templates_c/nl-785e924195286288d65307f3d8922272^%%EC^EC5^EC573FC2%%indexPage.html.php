<?php /* Smarty version 2.6.18, created on 2010-12-14 09:52:50
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/selection/content/indexPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', '/var/www/projects/watstemtmijnraad_hg/pages/admin/selection/content/indexPage.html', 12, false),)), $this); ?>
   

<h2>Selectie</h2>

<?php if ($this->_tpl_vars['global']->user->isSuperAdmin()): ?>
	<p><a class="add" href="/regions/create/">Toevoegen</a></p>
<?php endif; ?>

<h4 class="listTop">Selecteer eerst uw provincie, vervolgens uw gemeente waar u in wilt werken</h4>
<table class="list selection">

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'recur_indexPage.html') : smarty_modifier_cat($_tmp, 'recur_indexPage.html')), 'smarty_include_vars' => array('nodes' => $this->_tpl_vars['regions'],'level' => 1,'parent_path' => '','parent' => '_')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>


</table>