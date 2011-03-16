<?php /* Smarty version 2.6.18, created on 2010-12-09 13:08:00
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/parties/content/partyPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'count', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/parties/content/partyPage.html', 170, false),)), $this); ?>
<?php echo '
<script>
jQuery(document).ready(function(){
	handleTextFields(jQuery(\'form#search_rs input\'));

        // if Javascript is enabled hide the dropdowns
        jQuery(".advanced_first").hide();
        jQuery(".advanced_second").hide();

        jQuery("#first").click(function () {
            if(jQuery(".advanced_first").is(":hidden")) {
               jQuery(".advanced_first").css({
                   background : \'#fff\',
                   border : \'1px solid #000\',
                   position : \'absolute\'
               });
               jQuery("#first").css({
                   background : \'transparent url(/images/closed.gif) no-repeat scroll right 0.3em\'
               })
               if(jQuery(".advanced_second").is(":show")) {
                jQuery(".advanced_second").hide();
               }
                jQuery(".advanced_first").show();
                return false;
            }
            if(jQuery(".advanced_first").is(":show")) {
               jQuery("#first").css({
                   background : \'transparent url(/images/arrow-d-blue-md.png) no-repeat scroll right 0.3em\'
               })
                jQuery(".advanced_first").hide();
                return false;
            }
        });


        jQuery("#second").click(function () {
            if(jQuery(".advanced_second").is(":hidden")) {
               jQuery(".advanced_second").css({
                   background : \'#fff\',
                   border : \'1px solid #000\',
                   position : \'absolute\'
               });
               jQuery("#second").css({
                   background : \'transparent url(/images/closed.gif) no-repeat scroll right 0.3em\'
               })
               if(jQuery(".advanced_first").is(":show")) {
                jQuery(".advanced_first").hide();
               }
               jQuery(".advanced_second").show();
               return false;
            }

            if(jQuery(".advanced_first").is(":show")) {
               jQuery("#second").css({
                   background : \'transparent url(/images/arrow-d-blue-md.png) no-repeat scroll right 0.3em\'
               })
                jQuery(".advanced_second").hide();
                return false;
            }
        });
});
</script>
'; ?>


<div class="line">
	<div class="col size2of4">
		<div class="mod search">
			<h2>Zoek naar een onderwerp</h2>
			<form method="post" action="/search/" id="search_rs">
              	<div class="field">
      	        	<label for="terms"><span>Onderwerp of beleidsveld</span></label>
	                <input type="text" id="terms" name="q" class="text" />
	                <input type="hidden" name="party" value="<?php echo $this->_tpl_vars['party']->id; ?>
" />
	                <?php if ($this->_tpl_vars['region']): ?><input type="hidden" name="region" value="<?php echo $this->_tpl_vars['region']->id; ?>
" /><?php endif; ?>
                </div>
				<button type="submit" id="submit" name="submit" value="Zoek">Zoek</button>
			</form>
			<a class="more" href="/search/party/<?php echo $this->_tpl_vars['party']->id; ?>
/region/<?php echo $this->_tpl_vars['region']->id; ?>
/submit/1">Uitgebreid zoeken</a>
		</div>
	</div>
	<div class="col size1of4">
		<div class="mod filter">
			<?php if ($this->_tpl_vars['categories']): ?>
			<a href="#" class="advanced" id="first">Kies een beleidsveld</a>
                        <div class="advanced_first" style="z-index: 10;">
                            <ul>
                            	<li style="list-style-type: none;"><a href="?region=<?php echo $this->_tpl_vars['region']->id; ?>
" <?php if (! $this->_tpl_vars['cur_category']): ?>class="current"<?php endif; ?>>Alle categorien</a></li>
                                <?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['category']):
?>
                                    <li style="list-style-type: none;"><a href="?region=<?php echo $this->_tpl_vars['region']->id; ?>
&amp;category=<?php echo $this->_tpl_vars['category']['id']; ?>
" <?php if ($this->_tpl_vars['cur_category'] == $this->_tpl_vars['category']['id']): ?>class="current"<?php endif; ?>><span style="float: right;"><?php echo $this->_tpl_vars['category']['count']; ?>
</span> <?php echo $this->_tpl_vars['category']['name']; ?>
</a></li>
                                <?php endforeach; endif; unset($_from); ?>
                            </ul>
                        </div>
                        
            <?php else: ?>
            	<a href="#" class="advanced" style="background: transparent url(/images/closed.gif) no-repeat scroll right 0.3em; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous;">Geen beleidsvelden</a>
            <?php endif; ?>
            
            <?php if ($this->_tpl_vars['tags']): ?>
			<a href="#" class="advanced" id="second">Kies een tag</a>
                        <div class="advanced_second" style="z-index: 10;">
                            <ul>
                                <li style="list-style-type: none;"><a href="?region=<?php echo $this->_tpl_vars['region']->id; ?>
" <?php if (! $this->_tpl_vars['cur_tag']): ?>class="current"<?php endif; ?>>Alle tags</a></li>
                                <?php $_from = $this->_tpl_vars['tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['tag']):
?>
                                    <li style="list-style-type: none;"><a href="?region=<?php echo $this->_tpl_vars['region']->id; ?>
&amp;tag=<?php echo $this->_tpl_vars['tag']['id']; ?>
" <?php if ($this->_tpl_vars['cur_tag'] == $this->_tpl_vars['tag']['id']): ?>class="current"<?php endif; ?>><span style="float: right;"><?php echo $this->_tpl_vars['tag']['count']; ?>
</span> <?php echo $this->_tpl_vars['tag']['name']; ?>
</a></li>
                                <?php endforeach; endif; unset($_from); ?>
                            </ul>
                        </div>
                        
            <?php else: ?>
            	<a href="#" class="advanced" style="background: transparent url(/images/closed.gif) no-repeat scroll right 0.3em; -moz-background-clip: border; -moz-background-origin: padding; -moz-background-inline-policy: continuous;">Geen tags</a>
            <?php endif; ?>
		</div>
	</div>
	<?php if ($this->_tpl_vars['region']): ?>
	<div class="col size1of4">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../regions/includable/sb_mun_branding.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<?php endif; ?>
</div>

<div class="line">
	<div class="col size3of4">
		<div class="mod votings">
			<h2>
				<?php if ($this->_tpl_vars['filter_cat']): ?>
					Raadsstukken met het beleidsveld <?php echo $this->_tpl_vars['filter_cat']->name; ?>
 <a href="/parties/party/<?php echo $this->_tpl_vars['party']->id; ?>
<?php if ($this->_tpl_vars['region']->id): ?>?region=<?php echo $this->_tpl_vars['region']->id; ?>
<?php endif; ?>" class="remove-filter">Verwijder filter</a>
				<?php elseif ($this->_tpl_vars['filter_tag']): ?>
					Raadsstukken met de tag <?php echo $this->_tpl_vars['filter_tag']->name; ?>
 <a href="/parties/party/<?php echo $this->_tpl_vars['party']->id; ?>
<?php if ($this->_tpl_vars['region']->id): ?>?region=<?php echo $this->_tpl_vars['region']->id; ?>
<?php endif; ?>" class="remove-filter">Verwijder filter</a>
				<?php else: ?>
					Recente onderwerpen
				<?php endif; ?>
			</h2>
			<?php $_from = $this->_tpl_vars['votings']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['voting']):
?>
				<?php $this->assign('id', $this->_tpl_vars['voting']->id); ?>
				<div class="voting">
					<?php if ($this->_tpl_vars['voting']->result > 0): ?>
					<div class="votes">
						<div class="vote">
							<h5 class="party">
								<abbr title="<?php echo $this->_tpl_vars['party']->name; ?>
"><?php echo $this->_tpl_vars['party']->short_form; ?>
</abbr>
							</h5>
							<p>
								<?php if ($this->_tpl_vars['total_votes'][$this->_tpl_vars['id']] == 0 || ( ( $this->_tpl_vars['party_pro'][$this->_tpl_vars['id']] + $this->_tpl_vars['party_contra'][$this->_tpl_vars['id']] ) == 0 )): ?>
									Geen van de leden heeft gestemd
								<?php elseif ($this->_tpl_vars['party_pro'][$this->_tpl_vars['id']] == $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]): ?>
									Alle <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-pro">voor</span>
								<?php elseif ($this->_tpl_vars['party_contra'][$this->_tpl_vars['id']] == $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]): ?>
									Alle <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-contra">tegen</span>
								<?php elseif ($this->_tpl_vars['party_contra'][$this->_tpl_vars['id']] > $this->_tpl_vars['party_pro'][$this->_tpl_vars['id']]): ?>
									<?php echo $this->_tpl_vars['party_contra'][$this->_tpl_vars['id']]; ?>
 van <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-contra">tegen</span>
								<?php else: ?>
									<?php echo $this->_tpl_vars['party_pro'][$this->_tpl_vars['id']]; ?>
 van <?php echo $this->_tpl_vars['total_votes'][$this->_tpl_vars['id']]; ?>
 leden stemden <span class="vote-pro">voor</span>
								<?php endif; ?>

							</p>
						</div>
						
					</div>
                    <a class="more2" href="/raadsstukken/raadsstuk/<?php echo $this->_tpl_vars['voting']->id; ?>
/?party=<?php echo $this->_tpl_vars['party']->id; ?>
">Bekijk alle stemmingen</a>
					<?php endif; ?>
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../raadsstukken/includable/voting.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</div>
			<?php endforeach; else: ?>
				<?php if ($this->_tpl_vars['filter_cat'] || $this->_tpl_vars['filter_tag']): ?>
					Geen raadstukken gevonden met het huidige filter
				<?php else: ?>
					Nog geen stemmen uitgebracht
				<?php endif; ?>
			<?php endif; unset($_from); ?>
			<?php if (count($this->_tpl_vars['votings']) >= $this->_tpl_vars['max_rs_count']): ?>
				<a class="mod-more-big" href="/search<?php echo $this->_tpl_vars['extra_url_params']; ?>
/q/-all/party/<?php echo $this->_tpl_vars['party']->id; ?>
/<?php if ($this->_tpl_vars['region']): ?>region/<?php echo $this->_tpl_vars['region']->id; ?>
/<?php endif; ?>submit/Link">Bekijk alle raadsstukken</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="col size1of4 sidebar">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../parties/includable/sb_correlate.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../parties/includable/sb_list.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
       <div class="mod nav-parties">
                <h3>Extra's</h3>
                <ul class="party">
                    <li><a href="/iframe/explanation/?party=<?php echo $this->_tpl_vars['party']->id; ?>
<?php if ($this->_tpl_vars['region']->id): ?>&region=<?php echo $this->_tpl_vars['region']->id; ?>
<?php endif; ?>" target="_blank">Raadsstukken op uw website</a></li>
                </ul>
            </div>
        </div>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../regions/includable/sb_mun_pages.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
</div>