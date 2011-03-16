<?php /* Smarty version 2.6.18, created on 2011-01-19 09:31:40
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../raadsstukken/includable/voting.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../raadsstukken/includable/voting.html', 6, false),array('modifier', 'strip_tags', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../raadsstukken/includable/voting.html', 9, false),array('modifier', 'truncate', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../raadsstukken/includable/voting.html', 9, false),)), $this); ?>
<?php if (! $this->_tpl_vars['id']): ?>
<?php $this->assign('id', $this->_tpl_vars['voting']->id); ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['region'] == 'test'): ?><p class="plaatsnaam"><?php echo $this->_tpl_vars['voting']->region_name; ?>
</p><?php endif; ?>
<h4><?php echo $this->_tpl_vars['voting']->title; ?>
</h4>
<span class="date">Stemdatum: <?php echo ((is_array($_tmp=$this->_tpl_vars['voting']->vote_date)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%A %e %B %Y") : smarty_modifier_date_format($_tmp, "%A %e %B %Y")); ?>
</span>

<p>
	<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['voting']->summary)) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 110, '...', 1) : smarty_modifier_truncate($_tmp, 110, '...', 1)); ?>
 <a href="/raadsstukken/raadsstuk/<?php echo $this->_tpl_vars['id']; ?>
/<?php if ($this->_tpl_vars['party']): ?>?party=<?php echo $this->_tpl_vars['party']->id; ?>
<?php endif; ?>" class="more">Lees meer</a>
</p>


<?php if ($this->_tpl_vars['politician']): ?>
			<div class="pol-results">
			<?php if (is_null ( $this->_tpl_vars['voting']->polvote )): ?>
				<?php if ($this->_tpl_vars['voting']->vote_0): ?>
					<span class="pol-vote pro"><?php echo $this->_tpl_vars['politician']->formatName(); ?>
 stemde voor</span>
				<?php elseif ($this->_tpl_vars['voting']->vote_1): ?>
					<span class="pol-vote contra"><?php echo $this->_tpl_vars['politician']->formatName(); ?>
 stemde tegen</span>
				<?php else: ?>
					<span class="pol-vote not-voted"><?php echo $this->_tpl_vars['politician']->formatName(); ?>
 stemde niet</span>
				<?php endif; ?>
			<?php elseif ($this->_tpl_vars['voting']->polvote == 0): ?>
				<span class="pol-vote pro"><?php echo $this->_tpl_vars['politician']->formatName(); ?>
 stemde voor</span>
			<?php elseif ($this->_tpl_vars['voting']->polvote == 1): ?>
				<span class="pol-vote contra"><?php echo $this->_tpl_vars['politician']->formatName(); ?>
 stemde tegen</span>
			<?php else: ?>
				<span class="pol-vote not-voted"><?php echo $this->_tpl_vars['politician']->formatName(); ?>
 stemde niet</span>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['voting']->result == 1): ?>
				<span class="verdict pro"><?php echo $this->_tpl_vars['voting']->getResultTitle(); ?>
</span>
			<?php elseif ($this->_tpl_vars['voting']->result == 2): ?>
				<span class="verdict contra"><?php echo $this->_tpl_vars['voting']->getResultTitle(); ?>
</span>
			<?php endif; ?>
		</div>
        <div class="more_link">
			<a class="more_link" href="/raadsstukken/raadsstuk/<?php echo $this->_tpl_vars['id']; ?>
">Bekijk alle stemmen</a>
        </div>
	<?php else: ?>
    <div class="results-position">
        <div class="results">
            <?php if ($this->_tpl_vars['voting']->result > 0): ?>
                <span class="pro all"><?php echo $this->_tpl_vars['voting']->vote_0; ?>
 voor</span>
                
                <span class="contra all" style="max-width: 80%; width: <?php echo $this->_tpl_vars['bar_width'][$this->_tpl_vars['id']]; ?>
%;"><?php echo $this->_tpl_vars['voting']->vote_1; ?>
 tegen</span>
            <?php endif; ?>
            <span class="verdict <?php if ($this->_tpl_vars['voting']->result == 0): ?>v-not-voted<?php elseif ($this->_tpl_vars['voting']->result == 1): ?>v-pro<?php else: ?>v-contra<?php endif; ?>"><?php echo $this->_tpl_vars['voting']->getResultTitle(); ?>
</span>
        </div>
    </div>
<?php endif; ?>