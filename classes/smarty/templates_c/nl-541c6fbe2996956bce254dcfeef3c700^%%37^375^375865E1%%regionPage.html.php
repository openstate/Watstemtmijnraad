<?php /* Smarty version 2.6.18, created on 2010-12-09 15:25:05
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/iframe/content/regionPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/iframe/content/regionPage.html', 2, false),array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/iframe/content/regionPage.html', 21, false),array('modifier', 'strip_tags', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/iframe/content/regionPage.html', 22, false),array('modifier', 'truncate', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/iframe/content/regionPage.html', 22, false),)), $this); ?>

<div id="main" class="<?php echo ((is_array($_tmp=@$this->_tpl_vars['theme'])) ? $this->_run_mod_handler('default', true, $_tmp, 'dark') : smarty_modifier_default($_tmp, 'dark')); ?>
">
	<div class="widget">
    	<div class="top">
            <div class="politician" style="padding-left: 10px;">
                <h1><?php echo $this->_tpl_vars['region']->formatName(); ?>
</h1>
                <p>Dit zijn de laatste <?php echo $this->_tpl_vars['num']; ?>
 raadstukken gepubliceerd in <?php echo $this->_tpl_vars['region']->formatName(); ?>
</p>
            </div>
            <a href="http://watstemtmijnraad.nl" class="logo">WSMR</a>
        </div>
            
        <ul>
        <?php $_from = $this->_tpl_vars['raadsstukken']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['radlist'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['radlist']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['rad']):
        $this->_foreach['radlist']['iteration']++;
?>
        	<li <?php if (($this->_foreach['radlist']['iteration'] <= 1)): ?>class="first"<?php endif; ?>>
        		<?php if ($this->_tpl_vars['rad']->result == 0): ?><span class="result new">Niet gestemd</span>
        		<?php elseif ($this->_tpl_vars['rad']->result == 1): ?><span class="result pro">Aangenomen</span>
        		<?php else: ?><span class="result contra">Afgewezen</span>
        		<?php endif; ?>
        	
            	<a href="/raadsstukken/raadsstuk/<?php echo $this->_tpl_vars['rad']->id; ?>
" target="_blank">
                	<h2><?php echo ((is_array($_tmp=$this->_tpl_vars['rad']->title)) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
</h2>
                    <p><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['rad']->summary)) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 110, '...', 1) : smarty_modifier_truncate($_tmp, 110, '...', 1)); ?>
</p>
                </a>
            </li>
        <?php endforeach; else: ?>
        	<li class="first">
            	<h4 style="text-align: center;">Geen raadsstukken gevonden</h4>
            </li>
        <?php endif; unset($_from); ?>
        </ul>
        
        <div style="clear: both"></div>
    </div>
</div>