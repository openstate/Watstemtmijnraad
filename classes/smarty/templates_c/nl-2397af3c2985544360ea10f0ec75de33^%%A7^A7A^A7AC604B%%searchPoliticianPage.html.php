<?php /* Smarty version 2.6.18, created on 2010-12-09 14:50:05
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/politicians/content/searchPoliticianPage.html */ ?>
<div class="line">
    <div class="col size4of4">
        <div class="mod search_results">
        	<h1>Kies een politicus</h1>
                <ul>
                	<li class="first">
                        <span class="pol_search_span"><strong>Politicus</strong></span>
                        <span style="width:300px;"><strong>Partij</strong></span>
                        <strong>Regio/Gemeente</strong>
                    </li>
                </ul>
           		<?php $_from = $this->_tpl_vars['politicians']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['politician']):
?>
           		<?php $this->assign('id', $this->_tpl_vars['politician']->id); ?>
           		<?php $this->assign('region', $this->_tpl_vars['regions'][$this->_tpl_vars['id']]); ?>
           		<?php $this->assign('party', $this->_tpl_vars['parties'][$this->_tpl_vars['id']]); ?>
				<ul>
                	<li>
                        <span class="pol_search_span">
                                <a href="/politicians/politician/<?php echo $this->_tpl_vars['politician']->id; ?>
">
                                    <?php echo $this->_tpl_vars['politician']->formatName(); ?>

                                </a>
                        </span>
                        <span style="width:300px;">
                        	<?php if ($this->_tpl_vars['party']): ?>
                        		<?php if ($this->_tpl_vars['region']): ?>
                        			<a href="/parties/party/<?php echo $this->_tpl_vars['party']->id; ?>
?region=<?php echo $this->_tpl_vars['region']->id; ?>
"><?php echo $this->_tpl_vars['party']->name; ?>
</a>
                        		<?php else: ?>
                        			<?php echo $this->_tpl_vars['party']->name; ?>

                        		<?php endif; ?>
                        	<?php endif; ?>
                        </span>

                       	<?php if ($this->_tpl_vars['region']): ?> 
                       		<a href="/regions/region/<?php echo $this->_tpl_vars['region']->id; ?>
">
								<?php echo $this->_tpl_vars['region']->name; ?>

                       		</a> 
                       	<?php else: ?>
                       		Niet actief
                       	<?php endif; ?>
                    </li>
                </ul>
            <?php endforeach; else: ?>
                Geen politici gevonden
            <?php endif; unset($_from); ?>
		</div>
	</div>
</div>