<?php /* Smarty version 2.6.18, created on 2010-12-09 13:02:39
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/search/content/indexPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'lower', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/search/content/indexPage.html', 47, false),array('modifier', 'array_slice', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/search/content/indexPage.html', 60, false),array('modifier', 'implode', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/search/content/indexPage.html', 61, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/search/content/indexPage.html', 61, false),array('modifier', 'nl2br', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/search/content/indexPage.html', 61, false),)), $this); ?>


<?php echo '
<script>
jQuery(document).ready(function(){
	handleTextFields(jQuery(\'form#search_rs input\'));
});
</script>
'; ?>

<div class="line">
    <div class="col size3of4">
        <div class="mod searchform">
            <?php if ($this->_tpl_vars['fts'] && ! $this->_tpl_vars['searchAll']): ?>
            <h2 class="no-underline">Zoekopdracht</h2>
            <div id="searchBlock" class="search contentBlock searchResultContainer">
                <form method="post" action="/search/" id="search_rs">
                	<div class="field">
                        <label for="terms"><span>Onderwerp of beleidsveld</span></label>
                        <input type="text" id="terms" name="q" class="text" value="<?php echo $this->_tpl_vars['search_params']['q']; ?>
"/>
                        
                        <?php $_from = $this->_tpl_vars['search_params']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['parnam'] => $this->_tpl_vars['par']):
?>
		        			<?php if ($this->_tpl_vars['parnam'] != q): ?><input type="hidden" name="<?php echo $this->_tpl_vars['parnam']; ?>
" value="<?php echo $this->_tpl_vars['par']; ?>
" /><?php endif; ?>
		        		<?php endforeach; endif; unset($_from); ?>
                    </div>
                    <button type="submit" id="submit" name="submit" value="Zoek">Zoek</button>
                </form>
                
                <a class="more" href="/search<?php if ($this->_tpl_vars['region']): ?>/region/<?php echo $this->_tpl_vars['region']->id; ?>
<?php endif; ?><?php if ($this->_tpl_vars['party']): ?>/party/<?php echo $this->_tpl_vars['party']->id; ?>
<?php endif; ?><?php if ($this->_tpl_vars['politician']): ?>/politician_id/<?php echo $this->_tpl_vars['politician']->id; ?>
<?php endif; ?>/submit/1">Uitgebreid zoeken</a>
            </div>
            <?php elseif ($this->_tpl_vars['searchAll']): ?>
            <?php else: ?>
            <h2 id="search_toggle">Wijzig zoekopdracht <img id="searchImage"  style="display: none;"alt="Zoekformulier open/sluiten" /></h2>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../advsearch/content/search.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php endif; ?>
        </div>
    
	
    <?php if ($this->_tpl_vars['stats']['count']): ?>
    <div class="mod results">
		<?php if ($this->_tpl_vars['searchAll']): ?>
			<h2>Alle raadstukken van    
        	<?php if ($this->_tpl_vars['focus'] == 'par'): ?>
        		<?php echo $this->_tpl_vars['party']->name; ?>

        	<?php elseif ($this->_tpl_vars['focus'] == 'pol'): ?>
        		<?php echo $this->_tpl_vars['politician']->formatName(); ?>

        	<?php elseif ($this->_tpl_vars['focus'] == 'reg'): ?>
        		de <?php echo ((is_array($_tmp=$this->_tpl_vars['region']->level_name)) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
 <?php echo $this->_tpl_vars['region']->name; ?>

        	<?php endif; ?>
        	</h2>
        <?php else: ?>
        	<h2>Zoekresultaten <?php echo $this->_tpl_vars['stats']['start']+1; ?>
-<?php echo $this->_tpl_vars['stats']['end']; ?>
 van <?php echo $this->_tpl_vars['stats']['count']; ?>
</h2>
        <?php endif; ?>
		
		    
        <?php if ($this->_tpl_vars['warning']): ?><div class="warning"><p>Uw zoekopdracht heeft het maximale aantal resultaten opgeleverd. U kunt uw zoekopdracht beperken door het toevoegen van meer zoektermen.</p></div><?php endif; ?>
        <?php if ($this->_tpl_vars['sheader']): ?>
        <div class="contentBlock result">
            <p><?php echo $this->_tpl_vars['sheader']['0']; ?>
</p>
            <div class="title"><?php echo $this->_tpl_vars['sheader']['1']; ?>
</div>
            <?php $this->assign('sheader', array_slice($this->_tpl_vars['sheader'], 2)); ?>
            <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp="\n")) ? $this->_run_mod_handler('implode', true, $_tmp, $this->_tpl_vars['sheader']) : implode($_tmp, $this->_tpl_vars['sheader'])))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

        </div>
        <hr class="filter"/>
        <?php endif; ?>

        <?php $_from = $this->_tpl_vars['formdata']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['result_loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['result_loop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['voting']):
        $this->_foreach['result_loop']['iteration']++;
?>
        	<?php $this->assign('id', $this->_tpl_vars['voting']->id); ?>
            <div class="voting">
            
            <?php if ($this->_tpl_vars['focus'] == 'par'): ?>
            	<?php if ($this->_tpl_vars['voting']->result > 0): ?>
					<div class="votes">
						<div class="vote">
							<h5 class="party">
                                <?php if ($this->_tpl_vars['party']->short_form): ?>
                                    <abbr title="<?php echo $this->_tpl_vars['party']->name; ?>
"><?php echo $this->_tpl_vars['party']->short_form; ?>
</abbr>
                                <?php else: ?>
                                    <abbr title="<?php echo $this->_tpl_vars['party']->name; ?>
"><?php echo $this->_tpl_vars['party']->name; ?>
</abbr>
                                <?php endif; ?>
							</h5>
							<p>
								<?php if ($this->_tpl_vars['total_votes'][$this->_tpl_vars['id']] == 0 || ( ( $this->_tpl_vars['voting']->vote_0 + $this->_tpl_vars['voting']->vote_1 ) == 0 )): ?>
									Geen van de leden heeft gestemd
								<?php elseif ($this->_tpl_vars['voting']->vote_0 == $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]): ?>
									Alle <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-pro">voor</span>
								<?php elseif ($this->_tpl_vars['voting']->vote_1 == $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]): ?>
									Alle <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-contra">tegen</span>
								<?php elseif ($this->_tpl_vars['voting']->vote_1 > $this->_tpl_vars['voting']->vote_0): ?>
									<?php echo $this->_tpl_vars['voting']->vote_1; ?>
 van <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-contra">tegen</span>
								<?php else: ?>
									<?php echo $this->_tpl_vars['voting']->vote_0; ?>
 van <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-pro">voor</span>
								<?php endif; ?>
							</p>
						</div>
						<a class="more" href="/raadsstukken/raadsstuk/<?php echo $this->_tpl_vars['voting']->id; ?>
">Bekijk alle stemmingen</a>
					</div>
				<?php endif; ?>
            <?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../raadsstukken/includable/voting.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>           
            </div>
        <?php endforeach; endif; unset($_from); ?>
    </div>
    <?php if ($this->_tpl_vars['pager']): ?><p class="pager"><?php echo $this->_tpl_vars['pager']; ?>
</p><?php endif; ?>
    </div>
    
    <?php else: ?>
	    <div class="mod results">
        	<h5>Uw zoekopdracht heeft geen resultaten opgeleverd.</h5>
    	</div>
    </div>
    <?php endif; ?>
    
    <?php if ($this->_tpl_vars['focus'] == 'par'): ?>
        <div class="col size1of4 sidebar">
            <?php if ($this->_tpl_vars['region'] || $this->_tpl_vars['region_id']): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../regions/includable/sb_mun_branding.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../parties/includable/sb_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div>
    <?php elseif ($this->_tpl_vars['focus'] == 'pol'): ?>
        <div class="col size1of4 sidebar">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../regions/includable/sb_mun_branding.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../parties/includable/sb_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div>
    <?php elseif ($this->_tpl_vars['focus'] == 'reg' && ! $this->_tpl_vars['no_region_bar']): ?>
        <div class="col size1of4 sidebar">
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../regions/includable/sb_mun_branding.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <div class="mod nav-parties">
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../regions/includable/sb_mun_parties.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            </div>
        </div>
    <?php else: ?>
        <div class="col size1of4">
        </div>
    <?php endif; ?>
    
    </div>
</div>