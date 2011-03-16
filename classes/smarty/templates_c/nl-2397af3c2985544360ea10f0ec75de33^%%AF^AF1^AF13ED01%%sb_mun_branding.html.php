<?php /* Smarty version 2.6.18, created on 2010-12-09 13:03:56
         compiled from /var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../regions/includable/sb_mun_branding.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'lower', '/var/www/projects/watstemtmijnraad/public_html/../pages/watstemtmijnraad/search/php/../content//../../regions/includable/sb_mun_branding.html', 23, false),)), $this); ?>
<?php if (! $this->_tpl_vars['party_id']): ?><?php $this->assign('party_id', $this->_tpl_vars['party']->id); ?><?php endif; ?>
<?php if (! $this->_tpl_vars['politician_id']): ?><?php $this->assign('politician_id', $this->_tpl_vars['politician']->id); ?><?php endif; ?>

<div class="mod mun-branding">
	<h1 class="blue">
        <?php if ($this->_tpl_vars['party']->image): ?>
            <img src="/images/parties/<?php echo $this->_tpl_vars['party']->image; ?>
" alt="Logo van <?php echo $this->_tpl_vars['party']->name; ?>
"/>
        <?php elseif ($this->_tpl_vars['party']->name): ?>
            <?php echo $this->_tpl_vars['party']->name; ?>

        <?php else: ?>
            <?php if (! $this->_tpl_vars['iframe'] == 2): ?><a href="/regions/region/<?php echo $this->_tpl_vars['region']->id; ?>
"><?php endif; ?>
                <?php if ($this->_tpl_vars['region']->getLogo()): ?>
                    <img src="/files/<?php echo $this->_tpl_vars['region']->getLogo(); ?>
" alt="Logo <?php echo $this->_tpl_vars['region']->level_name; ?>
 <?php echo $this->_tpl_vars['region']->name; ?>
" />
                <?php else: ?>
                    <?php echo $this->_tpl_vars['region']->level_name; ?>
 <?php echo $this->_tpl_vars['region']->name; ?>

                <?php endif; ?>
            <?php if (! $this->_tpl_vars['iframe'] == 2): ?></a><?php endif; ?>
        <?php endif; ?>
	</h1>
	<?php if ($this->_tpl_vars['show_region_link']): ?><a class="back" href="/regions/region/<?php echo $this->_tpl_vars['region']->id; ?>
">Terug naar de <?php echo ((is_array($_tmp=$this->_tpl_vars['region']->level_name)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
 pagina</a><?php endif; ?>
	<?php if ($this->_tpl_vars['show_party_link']): ?><a class="back" href="/parties/party/<?php echo $this->_tpl_vars['party_id']; ?>
/?region=<?php echo $this->_tpl_vars['region']->id; ?>
">Terug naar de partij pagina</a><?php endif; ?>
	<?php if ($this->_tpl_vars['show_politician_link']): ?><a class="back" href="/politicians/politician/<?php echo $this->_tpl_vars['politician_id']; ?>
/?region=<?php echo $this->_tpl_vars['region']->id; ?>
">Terug naar de politicus pagina</a><?php endif; ?>
</div>