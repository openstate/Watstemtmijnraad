<?php /* Smarty version 2.6.18, created on 2010-12-15 03:20:01
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/parties/content/partyPage_region_select.html */ ?>
<div class="line">
    <div class="col size3of4">
        <div class="mod search_results">
			<h1><?php echo $this->_tpl_vars['party']->name; ?>
</h1>
			
            <p class="head">Kies een regio waar deze partij actief is</p>

			<?php $_from = $this->_tpl_vars['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['reg']):
?>
            <div class="search-results">
                <a href="/parties/party/4?region=<?php echo $this->_tpl_vars['reg']->id; ?>
"><?php echo $this->_tpl_vars['reg']->formatName(); ?>
</a>
            </div>
			<?php endforeach; else: ?>
				De partij <?php echo $this->_tpl_vars['party']->name; ?>
 is in geen enkele regio actief.
			<?php endif; unset($_from); ?>
		</div>
	</div>
</div>