<?php /* Smarty version 2.6.18, created on 2011-01-31 15:01:07
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/regions/content/indexPage.html */ ?>
<h2>Regio's</h2>

<?php if ($this->_tpl_vars['error']): ?><p class="error"><?php echo $this->_tpl_vars['error']; ?>
</p><?php endif; ?>


<p><a class="add" href="create/">Toevoegen</a></p>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/indexPageBase.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>