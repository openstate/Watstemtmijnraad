<?php /* Smarty version 2.6.18, created on 2011-01-27 08:20:48
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html', 17, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html', 23, false),array('modifier', 'urlencode', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html', 33, false),array('modifier', 'nl2br', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html', 60, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html', 78, false),array('modifier', 'lower', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html', 78, false),array('function', 'html_image', '/var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/raadsstukken/content/raadsstukPage.html', 100, false),)), $this); ?>




<?php echo '
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function(){
	handleTextFields(jQuery(\'form#search_rs input\'));
});
</script>
'; ?>


<div class="line">
	<div class="col size3of4">
		<div class="mod-raadsstuk-title">
			<h2><?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->title)) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
</h2>
		</div>
		<div class="col size2of3">
		<div class="mod votings pol-votings">
				<div class="voting">
					<span class="meta-info"><?php echo $this->_tpl_vars['raadsstuk']->metainfo; ?>
</span>
					<span class="date">Stemdatum: <?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->vote_date)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%A %e %B %Y") : smarty_modifier_date_format($_tmp, "%A %e %B %Y")); ?>
</span>
					<p>
						<?php echo $this->_tpl_vars['raadsstuk']->summary; ?>

					</p>
				</div>
			</div>
			<div class="mod votings voting-details">
           		<table>
	                <tr><th class="bold">Onderwerp(en):</th><td>
	                	<?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['category_loop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['category_loop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['category']):
        $this->_foreach['category_loop']['iteration']++;
?>	                		
	                		<a href="/search<?php echo $this->_tpl_vars['extra_search_params']; ?>
/category/<?php echo ((is_array($_tmp=$this->_tpl_vars['k'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
/submit/1"><?php echo $this->_tpl_vars['category']; ?>
</a>
	                		<?php if (! ($this->_foreach['category_loop']['iteration'] == $this->_foreach['category_loop']['total'])): ?>, <?php endif; ?>
	                	<?php endforeach; else: ?>
	                		Geen
	                	<?php endif; unset($_from); ?></td></tr>
	                <tr><th class="bold">Code:</th><td><?php echo $this->_tpl_vars['raadsstuk']->code; ?>
</td></tr>
	                <tr><th class="bold">Soort:</th><td><?php echo $this->_tpl_vars['raadsstuk']->type_name; ?>
</td></tr>
	                <?php if ($this->_tpl_vars['raadsstuk']->party): ?><tr><td class="bold">Ingediend door:</td><td><a href="/parties/party/<?php echo $this->_tpl_vars['raadsstuk']->party; ?>
/?region=<?php echo $this->_tpl_vars['raadsstuk']->region; ?>
"><?php echo $this->_tpl_vars['raadsstuk']->party_name; ?>
</a></td></tr><?php endif; ?>
	                <?php if ($this->_tpl_vars['submitters']): ?>
	                	<tr><th class="bold">Ingediend door:</th>
			                <td><?php $_from = $this->_tpl_vars['submitters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['party_id'] => $this->_tpl_vars['sub']):
?>
			                <a href="/parties/party/<?php echo $this->_tpl_vars['party_id']; ?>
/?region=<?php echo $this->_tpl_vars['raadsstuk']->region; ?>
"><?php echo $this->_tpl_vars['sub']['name']; ?>
</a>
			                 - <?php $_from = $this->_tpl_vars['sub']['members']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['name']):
?><a href="/politicians/politician/<?php echo $this->_tpl_vars['id']; ?>
/?region=<?php echo $this->_tpl_vars['raadsstuk']->region; ?>
"><?php echo $this->_tpl_vars['name']; ?>
</a><?php endforeach; endif; unset($_from); ?><br />
			                <?php endforeach; endif; unset($_from); ?>
						</td></tr>
					<?php endif; ?>
	                <tr><th class="bold">Tags:</th><td>
	                	<?php $_from = $this->_tpl_vars['tags']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['tag']):
?>
	                		<a href="/search<?php echo $this->_tpl_vars['extra_search_params']; ?>
/tags/<?php echo $this->_tpl_vars['tag']; ?>
/submit/1/"><?php echo $this->_tpl_vars['tag']; ?>
</a>
	                	<?php endforeach; else: ?>
	                		Geen
	                	<?php endif; unset($_from); ?>
	                </td></tr>
	                <?php if ($this->_tpl_vars['parent']): ?>
	                	<tr><th class="bold">Raadsstuk:</th><td>
	                			                		<?php if (( $this->_tpl_vars['iframe'] > 0 ) && ( $this->_tpl_vars['parent']->region != $this->_tpl_vars['raadsstuk']->region )): ?>
	                			<span><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['parent']->title)) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</span>
	                		<?php else: ?>
	                			<a href="/raadsstukken/raadsstuk/<?php echo $this->_tpl_vars['parent']->id; ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['parent']->title)) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</a>
	                		<?php endif; ?>
	                	</td></tr>
	                <?php endif; ?>
                    <?php if ($this->_tpl_vars['ext_url_info']): ?>
                    <tr><th class="bold">Extra: </th><td>
                            <a href="<?php echo $this->_tpl_vars['ext_url_info']; ?>
">Externe informatie</a>
                        </td>
                    </tr>
                    <?php endif; ?>
               </table>
           </div>
	</div>
    
	<div class="col size1of3 sidebar">
		<div class="mod voting-info">
           	<h5 class="<?php echo $this->_tpl_vars['verdict_class']; ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->vote_message)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
">Dit raadstuk is <?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->getResultTitle())) ? $this->_run_mod_handler('lower', true, $_tmp) : smarty_modifier_lower($_tmp)); ?>
</h5>
			<span class="pro"><?php echo $this->_tpl_vars['raadsstuk']->vote_0; ?>
 voor</span>
			<span class="contra"><?php echo $this->_tpl_vars['raadsstuk']->vote_1; ?>
 tegen</span>
			<span class="other">
			    <p>Niet gestemd: <?php echo $this->_tpl_vars['raadsstuk']->vote_2; ?>
</p>
			    <p>Afwezig: <?php echo $this->_tpl_vars['raadsstuk']->vote_3; ?>
</p>
			</span>
			<?php $this->assign('count', 0); ?>
			<?php $_from = $this->_tpl_vars['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['votings'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['votings']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['party_vote']):
        $this->_foreach['votings']['iteration']++;
?>
             <?php if (($this->_foreach['votings']['iteration'] == $this->_foreach['votings']['total'])): ?>
             	<div class="party-voting last-vote">
             <?php else: ?>
             	<div class="party-voting">
             <?php endif; ?>
					<?php if ($this->_tpl_vars['party_vote']->vote_0 > 0): ?><span class="voted pro"><?php echo $this->_tpl_vars['party_vote']->vote_0; ?>
 voor</span><?php endif; ?>
					<?php if ($this->_tpl_vars['party_vote']->vote_1 > 0): ?><span class="voted contra"><?php echo $this->_tpl_vars['party_vote']->vote_1; ?>
 tegen</span><?php endif; ?>
					<span class="this-party" id="this-party">
						<a class="blaat" href="" >
							<?php $this->assign('zaebalo', $this->_tpl_vars['party_vote']->party); ?>
							<abbr title="<?php echo ((is_array($_tmp=$this->_tpl_vars['partyMessages'][$this->_tpl_vars['zaebalo']])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
">
								<?php if ($this->_tpl_vars['party_vote']->party_logo != ''): ?>
									<?php $this->assign('img_file', $this->_tpl_vars['party_vote']->party_logo); ?>
									<?php echo smarty_function_html_image(array('file' => "parties/".($this->_tpl_vars['img_file']),'maxwidth' => '120','alt' => ($this->_tpl_vars['party_vote'])."->party_name"), $this);?>

								<?php elseif ($this->_tpl_vars['party_vote']->party_short_name): ?>
									<span class="party-name-raadstuk"><?php echo $this->_tpl_vars['party_vote']->party_short_name; ?>
</span>
								<?php else: ?>
									<span class="party-name-raadstuk"><?php echo $this->_tpl_vars['party_vote']->party_name; ?>
</span>
								<?php endif; ?>
							</abbr>
                            <span class="read-more">Lees meer...</span>
						</a>
					</span>
					<div class="voteinfo">
						<br/>
                        
						<?php $this->assign('partyid', $this->_tpl_vars['party_vote']->party); ?>

                         <?php $_from = $this->_tpl_vars['votes'][$this->_tpl_vars['partyid']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['members'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['members']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['vote']):
        $this->_foreach['members']['iteration']++;
?>
                            <?php if ($this->_tpl_vars['vote']['id'] == $this->_tpl_vars['submitter_party'][$this->_tpl_vars['partyid']]['id']): ?>
                                <span class="pol-name"><a href="/politicians/politician/<?php echo $this->_tpl_vars['submitter_party'][$this->_tpl_vars['partyid']]['id']; ?>
?region=<?php echo $this->_tpl_vars['raadsstuk']->region; ?>
"><?php echo $this->_tpl_vars['submitter_party'][$this->_tpl_vars['partyid']]['name']; ?>
</a></span><span style="float: right; margin-top: 5px;"> <?php if ($this->_tpl_vars['vote']['vote'] == '0'): ?><span class="voted pro">Voor</span><?php elseif ($this->_tpl_vars['vote']['vote'] == '1'): ?><span class="voted contra">Tegen</span><?php elseif ($this->_tpl_vars['vote']['vote'] == '2'): ?><span class="voted not-voted">Onthouden</span><?php else: ?><span class="voted not-voted">Afwezig</span><?php endif; ?></span><span style="display:block; border-bottom:1px solid #d2d2d2; clear:both; height:1px;"/>&nbsp;</span>
                            <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?>
                         <?php $_from = $this->_tpl_vars['votes'][$this->_tpl_vars['partyid']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['members'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['members']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['vote']):
        $this->_foreach['members']['iteration']++;
?>
                         <?php $this->assign('submitter_id', $this->_tpl_vars['submitter_party'][$this->_tpl_vars['partyid']]); ?>
                            <?php if ($this->_tpl_vars['submitter_party'][$this->_tpl_vars['partyid']]['id'] != $this->_tpl_vars['vote']['id']): ?>
							<span class="pol-name" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['vote']['message'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
"><a href="/politicians/politician/<?php echo $this->_tpl_vars['vote']['id']; ?>
?region=<?php echo $this->_tpl_vars['raadsstuk']->region; ?>
"><?php echo $this->_tpl_vars['vote']['first_name']; ?>
 <?php echo $this->_tpl_vars['vote']['last_name']; ?>
</a></span><span style="float:right; margin-top:5px;"> <?php if ($this->_tpl_vars['vote']['vote'] == '0'): ?><span class="voted pro">Voor</span><?php elseif ($this->_tpl_vars['vote']['vote'] == '1'): ?><span class="voted contra">Tegen</span><?php elseif ($this->_tpl_vars['vote']['vote'] == '2'): ?><span class="voted not-voted">Onthouden</span><?php else: ?><span class="voted not-voted">Afwezig</span><?php endif; ?></span><span style="display:block; border-bottom:1px solid #d2d2d2; clear:both; height:1px;"/>&nbsp;</span>
                            <?php endif; ?>
						<?php endforeach; endif; unset($_from); ?>
					</div>
	            </div>
				<?php $this->assign('count', ($this->_tpl_vars['count']+1)); ?>
           	<?php endforeach; endif; unset($_from); ?>
		</div>
	</div>
	</div>
	<div class="col size1of4">
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../regions/includable/sb_mun_branding.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
</div>

<?php echo '
<script type="text/javascript">
jQuery.noConflict();
jQuery(document).ready(function() {
    jQuery(".voteinfo").css(\'display\', \'none\');

    jQuery(\'.this-party\').click(function(ev) {
        var parent = jQuery(this).parent();
        parent.children(\'.voteinfo\').toggle(\'fast\');
        ev.preventDefault();
        return false;
    })
});
</script>
'; ?>