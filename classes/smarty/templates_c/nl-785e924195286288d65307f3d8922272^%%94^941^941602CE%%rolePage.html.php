<?php /* Smarty version 2.6.18, created on 2011-01-31 15:11:12
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/user/content/rolePage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_checkboxes', '/var/www/projects/watstemtmijnraad_hg/pages/admin/user/content/rolePage.html', 7, false),array('modifier', 'cat', '/var/www/projects/watstemtmijnraad_hg/pages/admin/user/content/rolePage.html', 16, false),)), $this); ?>
<h2>Rollen voor <?php echo $this->_tpl_vars['user']->username; ?>
<a class="back" href="/user/">Terug naar lijst met gebruikers</a></h2>
<p></p>
<form method="POST" action="">
<h1>Selecteer een rol</h1>
<div style="background: #EFEFEF;padding:10px; margin-bottom:10px;">
    <input type="hidden" name="userid" value="<?php echo $this->_tpl_vars['user']->id; ?>
" />
    <?php echo smarty_function_html_checkboxes(array('options' => $this->_tpl_vars['roles'],'name' => 'roles','selected' => $this->_tpl_vars['selectedRoles'],'labels' => true), $this);?>

</div>

<h1>Selecteer griffies</h1>
<?php if ($this->_tpl_vars['errors']): ?>
    <span style="color: #ff0000">Er moet minimaal 1 griffie worden geselecteerd</span>
<?php endif; ?>

<table class="list selection">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'recur_rolePage.html') : smarty_modifier_cat($_tmp, 'recur_rolePage.html')), 'smarty_include_vars' => array('nodes' => $this->_tpl_vars['regions'],'level' => 1,'parent_path' => '','parent' => '_')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</table>

    <div class="container-buttons"><input type="submit" value="Vestuur"></div>

</form>