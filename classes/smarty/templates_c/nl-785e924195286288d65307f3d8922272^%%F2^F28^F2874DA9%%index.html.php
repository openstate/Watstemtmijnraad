<?php /* Smarty version 2.6.18, created on 2010-12-16 14:34:08
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/statistics/content/index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strtotime', '/var/www/projects/watstemtmijnraad_hg/pages/admin/statistics/content/index.html', 4, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/statistics/content/index.html', 4, false),array('modifier', 'default', '/var/www/projects/watstemtmijnraad_hg/pages/admin/statistics/content/index.html', 12, false),array('modifier', 'urlencode', '/var/www/projects/watstemtmijnraad_hg/pages/admin/statistics/content/index.html', 12, false),array('modifier', 'replace', '/var/www/projects/watstemtmijnraad_hg/pages/admin/statistics/content/index.html', 19, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/statistics/content/index.html', 19, false),)), $this); ?>
<h2>Statistieken</h2>

<form action="" method="get">
Startdatum: <input type="text" name="start" value="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['start'])) ? $this->_run_mod_handler('strtotime', true, $_tmp) : strtotime($_tmp)))) ? $this->_run_mod_handler('date_format', true, $_tmp, '%d-%m-%Y') : smarty_modifier_date_format($_tmp, '%d-%m-%Y')); ?>
" />
Einddatum: <input type="text" name="end" value="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['end'])) ? $this->_run_mod_handler('strtotime', true, $_tmp) : strtotime($_tmp)))) ? $this->_run_mod_handler('date_format', true, $_tmp, '%d-%m-%Y') : smarty_modifier_date_format($_tmp, '%d-%m-%Y')); ?>
" />
<div class="container-buttons">
<button class="submit" type="submit">Instellen</button>
<button class="submit" name="clear" type="submit">Verwijderen</button>
</div>
</form>

<img src="/statistics/graph/?metric=visits&amp;start=<?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['start'])) ? $this->_run_mod_handler('default', true, $_tmp, '-infinity') : smarty_modifier_default($_tmp, '-infinity')))) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&amp;end=<?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['end'])) ? $this->_run_mod_handler('default', true, $_tmp, 'infinity') : smarty_modifier_default($_tmp, 'infinity')))) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
" />
<img src="/statistics/graph/?metric=avg_time_on_site&amp;start=<?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['start'])) ? $this->_run_mod_handler('default', true, $_tmp, '-infinity') : smarty_modifier_default($_tmp, '-infinity')))) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&amp;end=<?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['end'])) ? $this->_run_mod_handler('default', true, $_tmp, 'infinity') : smarty_modifier_default($_tmp, 'infinity')))) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
" />

<div style="width: 50%; float: left;">
<table>
<tr><th>Plaats</th><th>Aantal bezoekers</th></tr>
<?php $_from = $this->_tpl_vars['locations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['loc'] => $this->_tpl_vars['cnt']):
?>
<tr><td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['loc'])) ? $this->_run_mod_handler('replace', true, $_tmp, ', (not set)', '') : smarty_modifier_replace($_tmp, ', (not set)', '')))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</td><td><?php echo $this->_tpl_vars['cnt']; ?>
</td></tr>
<?php endforeach; endif; unset($_from); ?>
</table>
</div>

<div style="width: 50%; float: left;">
<table>
<tr><th>Pagina</th><th>Aantal bezoeken</th></tr>
<?php $_from = $this->_tpl_vars['pages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['pg'] => $this->_tpl_vars['cnt']):
?>
<tr><td><?php echo ((is_array($_tmp=$this->_tpl_vars['pg'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</td><td><?php echo $this->_tpl_vars['cnt']; ?>
</td></tr>
<?php endforeach; endif; unset($_from); ?>
</table>
</div>