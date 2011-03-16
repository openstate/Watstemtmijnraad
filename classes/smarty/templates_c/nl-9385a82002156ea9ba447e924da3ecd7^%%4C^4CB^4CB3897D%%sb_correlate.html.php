<?php /* Smarty version 2.6.18, created on 2011-02-20 13:02:56
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/parties/php/../content//../../parties/includable/sb_correlate.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_image', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/parties/php/../content//../../parties/includable/sb_correlate.html', 20, false),array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/parties/php/../content//../../parties/includable/sb_correlate.html', 33, false),)), $this); ?>
<?php if (sizeof ( $this->_tpl_vars['corr_info'] )): ?>
<div class="mod nav-parties">

	<h3>Het stemgedrag komt overeen met</h3>

	<div class="party-compare">
	<?php $_from = $this->_tpl_vars['corr_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['fit']):
?>
		<?php if ($this->_tpl_vars['fit']['fit'] > 75): ?>
			<?php $this->assign('barclass', 'much'); ?>
		<?php elseif ($this->_tpl_vars['fit']['fit'] > 50): ?>
			<?php $this->assign('barclass', 'medium'); ?>
		<?php elseif ($this->_tpl_vars['fit']['fit'] > 0): ?>
			<?php $this->assign('barclass', 'little'); ?>
		<?php else: ?>
			<?php $this->assign('barclass', 'zero'); ?>
		<?php endif; ?>
	
		<div class="block">
        <?php $this->assign('image_file', $this->_tpl_vars['fit']['image']); ?>
        	<div class="party"><?php if ($this->_tpl_vars['image_file']): ?><?php echo smarty_function_html_image(array('file' => "parties/".($this->_tpl_vars['image_file']),'maxwidth' => '57','maxheight' => '20','alt' => 'image'), $this);?>
<?php endif; ?></div>
        	<div class="compare-width">
        		<?php if ($this->_tpl_vars['fit']['fit']): ?>
	        		<div style="width: <?php echo $this->_tpl_vars['fit']['fit']; ?>
%" class="percentage <?php echo $this->_tpl_vars['barclass']; ?>
">
	        			<?php if ($this->_tpl_vars['fit']['fit'] > 20): ?><?php echo $this->_tpl_vars['fit']['fit']; ?>
%<?php endif; ?>
	        		</div>
	        		<?php if ($this->_tpl_vars['fit']['fit'] <= 20): ?><div class="percentage_label"><?php echo $this->_tpl_vars['fit']['fit']; ?>
%</div><?php endif; ?>
	        	<?php else: ?>
	        		<div class="no-data">Geen gegevens voor vergelijking</div>
        		<?php endif; ?>
        	</div>
        	
        	
        	<p><?php echo ((is_array($_tmp=$this->_tpl_vars['fit']['name'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
</p>
    	</div>
    <?php endforeach; endif; unset($_from); ?>
	</div>
</div>
<?php endif; ?>