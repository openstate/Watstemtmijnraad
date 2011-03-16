<?php /* Smarty version 2.6.18, created on 2010-12-13 13:08:25
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/admin/raadsstukken/php/../content/options.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad/public_html/../pages/admin/raadsstukken/php/../content/options.html', 3, false),)), $this); ?>

<input type="hidden" name="<?php echo $this->_tpl_vars['prefix']; ?>
" class="<?php echo $this->_tpl_vars['class']; ?>
 vote" value="<?php echo $this->_tpl_vars['set']; ?>
"/>
<input type="hidden" name="message_<?php echo $this->_tpl_vars['prefix']; ?>
" class="<?php echo $this->_tpl_vars['class']; ?>
" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" />
<div style="float: right; top:2px;position: relative; <?php if (! $this->_tpl_vars['message']): ?> display: none;<?php endif; ?>"<?php if ($this->_tpl_vars['message']): ?> title="<?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
"<?php endif; ?>><img style="border: 0px" width="16" height="16" src="/images/info.png" alt="information" /></div>
<div class="<?php echo $this->_tpl_vars['class']; ?>
 vote-item undefined" style="position:relative;"<?php if ($this->_tpl_vars['message']): ?> title="<?php echo ((is_array($_tmp=$this->_tpl_vars['message'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
"<?php endif; ?>><span class="vote-text">Kies een stem</span></div>