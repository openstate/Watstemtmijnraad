<?php /* Smarty version 2.6.18, created on 2010-12-09 12:43:43
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/politicians/content/politicianPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_image', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/politicians/content/politicianPage.html', 86, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/politicians/content/politicianPage.html', 91, false),array('modifier', 'count', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/politicians/content/politicianPage.html', 122, false),)), $this); ?>
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
<div class="col size3of4">
    <div class="line">
        <div class="col size2of3">
            <div class="mod politician">
                <h1><?php echo $this->_tpl_vars['politician']->formatName(); ?>
</h1>
                <?php if ($this->_tpl_vars['stats']['votes'] == 0): ?>
                    <p>Heeft niet gestemd in deze regio</p>
                <?php elseif ($this->_tpl_vars['stats']['real_votes'] == 0): ?>
                    <p>Heeft geen stemmen in deze regio van totaal <?php echo $this->_tpl_vars['stats']['votes']; ?>
 stemmen</p>
                <?php else: ?>
                    <p>Heeft gestemd op <?php echo $this->_tpl_vars['stats']['real_votes']; ?>
 van de <?php echo $this->_tpl_vars['stats']['votes']; ?>
 voorstellen</p>
                <?php endif; ?>
                <p>Heeft <?php if ($this->_tpl_vars['stats']['submits'] > 0): ?><?php echo $this->_tpl_vars['stats']['submits']; ?>
<?php else: ?>geen<?php endif; ?> voorstellen ingediend</p>
            </div>
        </div>
        <div class="col size1of3">
            <div class="mod politician-info">
                <h5>Foto</h5>
                <?php if ($this->_tpl_vars['member']->photo != NULL): ?>
                	<?php $this->assign('img_file', $this->_tpl_vars['member']->photo); ?>
                	<?php echo smarty_function_html_image(array('file' => "images/".($this->_tpl_vars['img_file']),'maxwidth' => '80','maxheight' => '92','alt' => "Foto van ".($this->_tpl_vars['politician'])."->formatName()"), $this);?>

                <?php else: ?>
                	<img height="92" width="80" src="/images/empty_photo.gif" alt="Geen foto beschikbaar" />
                <?php endif; ?>
                <p><strong>Partij</strong>: <?php echo $this->_tpl_vars['party']->name; ?>
</p>
                <?php if ($this->_tpl_vars['member_since']): ?><p><strong>Lid sinds</strong>: <?php echo ((is_array($_tmp=$this->_tpl_vars['member_since'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%B %Y") : smarty_modifier_date_format($_tmp, "%B %Y")); ?>
</p><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="line">
    
        <div class="col size2of3">
    
            <div class="mod votings pol-votings">
                <h2>
                	<?php if ($this->_tpl_vars['filter_cat']): ?>
						Raadsstukken met het beleidsveld <?php echo $this->_tpl_vars['filter_cat']->name; ?>
 <a href="/politicians/politician/<?php echo $this->_tpl_vars['politician']->id; ?>
<?php if ($this->_tpl_vars['region']->id): ?>?region=<?php echo $this->_tpl_vars['region']->id; ?>
<?php endif; ?>" class="remove-filter">Verwijder filter</a>
					<?php elseif ($this->_tpl_vars['filter_tag']): ?>
						Raadsstukken met de tag <?php echo $this->_tpl_vars['filter_tag']->name; ?>
 <a href="/politicians/politician/<?php echo $this->_tpl_vars['politician']->id; ?>
<?php if ($this->_tpl_vars['region']->id): ?>?region=<?php echo $this->_tpl_vars['region']->id; ?>
<?php endif; ?>" class="remove-filter">Verwijder filter</a>
					<?php else: ?>
						Recente onderwerpen
					<?php endif; ?>
                </h2>
                <?php $_from = $this->_tpl_vars['votings']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['voting']):
?>
                    <?php $this->assign('voting_id', $this->_tpl_vars['voting']->id); ?>
                    <div class="voting">
                        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../raadsstukken/includable/voting.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    </div>
                <?php endforeach; else: ?>
                    <?php if ($this->_tpl_vars['filter_cat'] || $this->_tpl_vars['filter_tag']): ?>
						Er zijn geen raadstukken gevonden met het huidige filter.
					<?php else: ?>
						<?php echo $this->_tpl_vars['politician']->formatName(); ?>
 heeft nog niet gestemd.
					<?php endif; ?>
                <?php endif; unset($_from); ?>
                <?php if (count($this->_tpl_vars['votings']) >= $this->_tpl_vars['max_rs_count']): ?><a class="mod-more-big" href="/search<?php echo $this->_tpl_vars['extra_url_params']; ?>
/politician_id/<?php echo $this->_tpl_vars['politician']->id; ?>
/region/<?php echo $this->_tpl_vars['region']->id; ?>
/q/-all/submit/Link">Meer recente stemmingen</a><?php endif; ?>
            </div>
        </div>
        
        <div class="col size1of3">
            <div class="mod search pol-search-small">
                <h2>Zoek raadstukken<span style="display:block;font-size:0.45em;">waar deze politicus op heeft gestemd</span></h2>
                <form method="post" action="/search/" id="search_rs">
                    <div class="field">
                        <label for="title"><span>Onderwerp (stuk van titel)</span></label>
                        <input type="text" class="text" name="q" id="title" />
                        <input type="hidden" name="politician_id" value="<?php echo $this->_tpl_vars['politician']->id; ?>
" />
                        <?php if ($this->_tpl_vars['region']): ?><input type="hidden" name="region" value="<?php echo $this->_tpl_vars['region']->id; ?>
" /><?php endif; ?>
                        <?php if ($this->_tpl_vars['party']): ?><input type="hidden" name="party" value="<?php echo $this->_tpl_vars['party']->id; ?>
" /><?php endif; ?>
                    </div>
                    <button type="submit" id="submit" name="submit" class="polsearch" value="Zoek">Zoek</button>
                </form>
            </div>
            <div class="mod filter">
                <a href="#" class="advanced" id="first">Kies een beleidsveld</a>
                            <div class="advanced_first" style="z-index: 10;">
                                <ul>
                                    <li style="list-style-type: none;"><a href="?region=<?php echo $this->_tpl_vars['region']->id; ?>
" <?php if (! $this->_tpl_vars['cur_category']): ?>class="current"<?php endif; ?>>Alle categorien</a></li>
                                    <?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['category']):
?>
                                        <li style="list-style-type: none;"><a href="?region=<?php echo $this->_tpl_vars['region']->id; ?>
&amp;category=<?php echo $this->_tpl_vars['category']->id; ?>
" <?php if ($this->_tpl_vars['cur_category'] == $this->_tpl_vars['category']->id): ?>class="current"<?php endif; ?>><?php echo $this->_tpl_vars['category']->name; ?>
</a></li>
                                    <?php endforeach; endif; unset($_from); ?>
                                </ul>
                            </div>
    
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
" <?php if ($this->_tpl_vars['cur_tag'] == $this->_tpl_vars['tag']['id']): ?>class="current"<?php endif; ?>><?php echo $this->_tpl_vars['tag']['name']; ?>
</a></li>
                                    <?php endforeach; endif; unset($_from); ?>
                                </ul>
                            </div>
            </div>
        </div>
    
    </div>
</div>
 
 
    
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
        <div class="mod nav-parties">
            <h3>Extra's</h3>
            <ul class="party">
                <li><a href="/iframe/explanation/?politician=<?php echo $this->_tpl_vars['politician']->id; ?>
<?php if ($this->_tpl_vars['region']->id): ?>&region=<?php echo $this->_tpl_vars['region']->id; ?>
<?php endif; ?>" target="_blank">Raadsstukken op uw website</a></li>
            </ul>
        </div>
	</div>

</div>