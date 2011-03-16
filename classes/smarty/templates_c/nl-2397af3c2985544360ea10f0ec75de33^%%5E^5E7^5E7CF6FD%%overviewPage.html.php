<?php /* Smarty version 2.6.18, created on 2010-12-09 12:43:57
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/regions/content/overviewPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'lower', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/regions/content/overviewPage.html', 19, false),)), $this); ?>
<div class="line">
    <div class="col size3of4">
        <div class="mod search_results">
			<h1><?php echo $this->_tpl_vars['region']->name; ?>
</h1>
			
            <p class="head">Resultaten</p>

			<?php $_from = $this->_tpl_vars['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['child']):
?>
			<?php $this->assign('child_id', $this->_tpl_vars['child']->id); ?>
            <div class="search-results">
                <a href="/regions/region/<?php echo $this->_tpl_vars['child']->id; ?>
"><?php echo $this->_tpl_vars['child']->level_name; ?>
 <?php echo $this->_tpl_vars['child']->name; ?>
</a>
                <?php if ($this->_tpl_vars['voting_count']): ?>
                <table>
                    <tr><th>Stemmingen afgelopen maand</th><td><?php echo $this->_tpl_vars['voting_count'][$this->_tpl_vars['child_id']]; ?>
</td></tr>
                </table>
                <?php endif; ?>
            </div>
			<?php endforeach; else: ?>
				Deze <?php echo ((is_array($_tmp=$this->_tpl_vars['region']->level_name)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
 heeft geen actieve <?php echo ((is_array($_tmp=$this->_tpl_vars['child_level']->name)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
.
			<?php endif; unset($_from); ?>
		</div>
	</div>
	<div class="col size1of4">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../includable/sb_mun_branding.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
</div>