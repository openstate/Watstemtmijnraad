<?php /* Smarty version 2.6.18, created on 2011-01-31 15:00:50
         compiled from /var/www/projects/watstemtmijnraad_hg/modules/user/pages/login/content/noRightsPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'urldecode', '/var/www/projects/watstemtmijnraad_hg/modules/user/pages/login/content/noRightsPage.html', 3, false),)), $this); ?>
<h2>Geen rechten</h2>
<div class="block" id="loginForm">
	U heeft onvoldoende rechten om de pagina <?php echo ((is_array($_tmp=$this->_tpl_vars['url'])) ? $this->_run_mod_handler('urldecode', true, $_tmp) : urldecode($_tmp)); ?>
 te zien.
</div>