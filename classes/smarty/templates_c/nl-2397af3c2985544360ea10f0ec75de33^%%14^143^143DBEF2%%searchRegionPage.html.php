<?php /* Smarty version 2.6.18, created on 2010-12-23 15:31:59
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/regions/content/searchRegionPage.html */ ?>
<div class="line">
    <div class="col size4of4">
        <div class="mod search_results">
			<h1>Kies een regio:</h1>
			<?php $_from = $this->_tpl_vars['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['region']):
?>
            <?php $this->assign('id', $this->_tpl_vars['region']->id); ?>
            <div class="search-results">
                <a href="/regions/region/<?php echo $this->_tpl_vars['region']->id; ?>
"><?php echo $this->_tpl_vars['region']->level_name; ?>
 <?php echo $this->_tpl_vars['region']->name; ?>
</a>
                <table>
                    <tr><th>Aantal raadsleden</th><td><?php if ($this->_tpl_vars['councillor_counts'][$this->_tpl_vars['id']] != 0): ?><?php echo $this->_tpl_vars['councillor_counts'][$this->_tpl_vars['id']]; ?>
<?php else: ?>0<?php endif; ?></td></tr>
                    <tr><th>Stemmingen afgelopen maand</th><td><?php if ($this->_tpl_vars['vote_counts'][$this->_tpl_vars['id']] != 0): ?><?php echo $this->_tpl_vars['vote_counts'][$this->_tpl_vars['id']]; ?>
<?php else: ?>0<?php endif; ?></td></tr>
                </table>        
            </div>
			<?php endforeach; else: ?>
				Op deze zoekterm zijn geen regio's gevonden
			<?php endif; unset($_from); ?>
		</div>
	</div>
</div>