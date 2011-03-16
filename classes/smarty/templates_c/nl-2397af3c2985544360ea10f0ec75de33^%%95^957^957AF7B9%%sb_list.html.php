<?php /* Smarty version 2.6.18, created on 2010-12-09 12:43:43
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/politicians/php/../content//../../parties/includable/sb_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_image', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/politicians/php/../content//../../parties/includable/sb_list.html', 11, false),)), $this); ?>
<?php if (! $this->_tpl_vars['region_id']): ?><?php $this->assign('region_id', $this->_tpl_vars['region']->id); ?><?php endif; ?>

<div class="mod nav-parties">
	<h3>Leden van deze fractie:</h3>
	<ul class="party">
		<?php $_from = $this->_tpl_vars['party_members']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['member']):
?>
		<li>
			<a href="/politicians/politician/<?php echo $this->_tpl_vars['member']->id; ?>
<?php if ($this->_tpl_vars['region_id']): ?>?region=<?php echo $this->_tpl_vars['region_id']; ?>
<?php endif; ?>">
				<?php if ($this->_tpl_vars['member']->photo != NULL): ?>
					<?php $this->assign('img_file', $this->_tpl_vars['member']->photo); ?>
					<?php echo smarty_function_html_image(array('file' => "images/".($this->_tpl_vars['img_file']),'maxwidth' => '38','maxheight' => '44','alt' => "Foto van ".($this->_tpl_vars['member'])."->formatName()"), $this);?>

				<?php else: ?>
					<img height="44" width="38" src="/images/profile-photo-party.jpg" alt="Geen foto beschikbaar" />
				<?php endif; ?>
				<p><strong><?php echo $this->_tpl_vars['member']->formatName(); ?>
</strong></p>
			</a>
		</li>
		<?php endforeach; else: ?>
		<li>
			<span>Geen actieve leden.</span>
		</li>
		<?php endif; unset($_from); ?>
	</ul>
</div>