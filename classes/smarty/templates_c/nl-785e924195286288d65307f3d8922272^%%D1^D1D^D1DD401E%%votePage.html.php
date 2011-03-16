<?php /* Smarty version 2.6.18, created on 2010-12-13 13:08:25
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/votePage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strtolower', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/votePage.html', 38, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/votePage.html', 38, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/votePage.html', 39, false),array('modifier', 'count', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/votePage.html', 71, false),array('function', 'html_options', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/votePage.html', 54, false),)), $this); ?>





<h2>Stemming invoeren</h2>
<div id="vote-box" style="display: none; border: 1px solid black; padding: 2px; background-color: #fff; ">
        <div style="margin-bottom: 10px;">&#160;</div>
        <div class="vote-box-item voor border" id="box-voor"><input type="radio" name="vote" value="0"/><span class="vote-text">Voor</span></div>
        <div class="vote-box-item tegen border" id="box-tegen" style="padding-bottom: 10px;"><input type="radio" name="vote" value="1"/><span class="vote-text">Tegen</span></div>
        <div class="vote-box-item onthouden border" id="box-onthouden"><input type="radio" name="vote" value="2"/><span class="vote-text">Onthouden</span></div>
        <div class="vote-box-item afwezig" id="box-afwezig" style="padding-bottom: 10px;"><input type="radio" name="vote" value="3"/><span class="vote-text">Afwezig</span></div>
        <div class="vote-box-item afwezig" id="box-afwezig" style="padding-bottom: 10px;"><input type="radio" name="vote" value="4"/><span class="vote-text">Verwijder stem</span></div>
        <div id="vote_message_link"><a href="#" onclick="return showMessageInput();">Stemverklaring opgeven</a></div>
        <div id="vote_message" style="display: none;">
                Stemverklaring<br />
                <textarea cols="22" rows="5" id="vote_message_input"></textarea><br />
                <button id="vote_message_ok">OK</button>
                <button id="vote_message_cancel">Annuleren</button>
        </div>
</div>
<div id="result-box" style="display: none; z-index: 42; border: 1px solid black; margin-top: -70px; padding: 2px; background-color: #fff; ">
        <div class="result-box-item voor border" id="box-accept"><input type="radio" name="result-radio" id="result-box-item-1" value="1"/><span class="vote-text">Aangenomen</span></div>
        <div class="result-box-item tegen border" id="box-reject"><input type="radio" name="result-radio" id="result-box-item-2" value="2"/><span class="vote-text">Afgewezen</span></div>
        <div class="result-box-item notVoted" id="box-notVoted"><input type="radio" name="result-radio" id="result-box-item-0" value="0"/><span class="vote-text">in behandeling</span></div>
</div>

<div class="forms">
	<?php if (! $this->_tpl_vars['create']): ?>
   	<ul class="steps">
        <li><a href="/raadsstukken/edit/<?php echo $this->_tpl_vars['raadsstuk']->id; ?>
">Gegevens</a></li>
        <li><a class="active" href="#">Stemmingen</a></li>
    </ul>
	<?php endif; ?>
    <div class="leftcol">
		<form action="" method="post" id="voteForm" style="position: relative;">
		<div class="block" style="padding-right: 10px;">
            <h4 class="margin">voor <?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->type_name)) ? $this->_run_mod_handler('strtolower', true, $_tmp) : strtolower($_tmp)); ?>
 '<?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->title)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
'</h4>
            <p>Datum: <?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->vote_date)) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %B %Y') : smarty_modifier_date_format($_tmp, '%e %B %Y')); ?>
</p>
            <p style="border-bottom:1px solid #d2d2d2;">Code: <?php echo ((is_array($_tmp=$this->_tpl_vars['raadsstuk']->code)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</p>
			<table id="council">
				<tr>
					<td class="space"><h1><span id="resultCaption">Stemming</span></h1></td>
					<td class="space width">
						<input type="hidden" id="result" name="result" value="<?php echo $this->_tpl_vars['raadsstuk']->result; ?>
"/>
						<div class="result-item notVoted" style="z-index: 100; position: relative;">
							<h3><span class="vote-text">in behandeling</span></h3>
						</div>
					</td>
				</tr>
                <tr>
                    <td class="space"><h1><span id="resultCaption">Consensus</span></h1></td>
                    <td class="space width">
                        <?php echo smarty_function_html_options(array('name' => 'consensus','options' => $this->_tpl_vars['consensus'],'selected' => $this->_tpl_vars['consensus_selected']), $this);?>

                    </td>
                </tr>

				<tr class="border">
					<td class="space2"><h3><span><?php echo $this->_tpl_vars['region']->level_name; ?>
 <?php echo $this->_tpl_vars['region']->name; ?>
</span></h3></td><td class="space2 width"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."options.html", 'smarty_include_vars' => array('prefix' => 'council','class' => 'raad','set' => $this->_tpl_vars['council']['vote'],'message' => $this->_tpl_vars['raadsstuk']->vote_message)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
				</tr>
            </table>
           	<table class="list">
				<?php $_from = $this->_tpl_vars['council']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['partyName'] => $this->_tpl_vars['party']):
?>
				<?php ob_start(); ?>party[<?php echo $this->_tpl_vars['party']['id']; ?>
]<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('pname', ob_get_contents());ob_end_clean(); ?>
				<?php $this->assign('pid', $this->_tpl_vars['party']['id']); ?><?php $this->assign('msg', $this->_tpl_vars['partyMessages'][$this->_tpl_vars['pid']]); ?>
				<?php if ($this->_tpl_vars['msg']): ?><?php $this->assign('msg', $this->_tpl_vars['msg']->message); ?><?php else: ?><?php $this->assign('msg', ''); ?><?php endif; ?>
				<tr class="hover alt" party="<?php echo $this->_tpl_vars['party']['id']; ?>
" status="closed" onclick="return toggleParty(this);">
					<td width="15px">
                    
                    <a class="party_folding"><img src="/images/expand.gif" class="image_<?php echo $this->_tpl_vars['party']['id']; ?>
" width="16" height="16" /></a></td><td>
                    <strong><span class="party-name"><?php echo $this->_tpl_vars['partyName']; ?>
<?php if ($this->_tpl_vars['party']['short_form']): ?> (<?php echo ((is_array($_tmp=$this->_tpl_vars['party']['short_form'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
)<?php endif; ?></span></strong> (<?php echo count($this->_tpl_vars['party']['politicians']); ?>
 stem<?php if (count($this->_tpl_vars['party']['politicians']) != 1): ?>men<?php endif; ?>)</td><td class="width"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."options.html", 'smarty_include_vars' => array('prefix' => $this->_tpl_vars['pname'],'class' => 'party','set' => $this->_tpl_vars['party']['vote'],'message' => $this->_tpl_vars['msg'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
				</tr>
				<?php $_from = $this->_tpl_vars['party']['politicians']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['politicianId'] => $this->_tpl_vars['politician']):
?>
				<?php ob_start(); ?>politician[<?php echo $this->_tpl_vars['politicianId']; ?>
]<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('poname', ob_get_contents());ob_end_clean(); ?>
				<tr class="party_<?php echo $this->_tpl_vars['party']['id']; ?>
" style="display: none;">
					<?php if (is_null ( $this->_tpl_vars['politician']['vote']->vote ) && $this->_tpl_vars['absents'][$this->_tpl_vars['politicianId']]): ?>
						<td width="15px">&nbsp;</td><td><span><?php echo $this->_tpl_vars['politician']['name']; ?>
</span></td><td class="width"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."options.html", 'smarty_include_vars' => array('prefix' => $this->_tpl_vars['poname'],'class' => ($this->_tpl_vars['pname'])." politician",'set' => 3,'message' => $this->_tpl_vars['politician']['vote']->message)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
					<?php else: ?>
						<td width="15px">&nbsp;</td><td><span><?php echo $this->_tpl_vars['politician']['name']; ?>
</span></td><td class="width"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."options.html", 'smarty_include_vars' => array('prefix' => $this->_tpl_vars['poname'],'class' => ($this->_tpl_vars['pname'])." politician",'set' => $this->_tpl_vars['politician']['vote']->vote,'message' => $this->_tpl_vars['politician']['vote']->message)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
					<?php endif; ?>
				</tr>
				<?php endforeach; endif; unset($_from); ?>
				<?php endforeach; endif; unset($_from); ?>
				
				<?php $_from = $this->_tpl_vars['voting_parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['partyName'] => $this->_tpl_vars['party']):
?>
					<?php ob_start(); ?>party[<?php echo $this->_tpl_vars['party']['id']; ?>
]<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('pname', ob_get_contents());ob_end_clean(); ?>
				<tr>
					<td><span class="party-name"><?php echo $this->_tpl_vars['partyName']; ?>
</span></td>
					<td class="width"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."options.html", 'smarty_include_vars' => array('prefix' => $this->_tpl_vars['pname'],'class' => 'party','set' => $this->_tpl_vars['party']['vote'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></td>
				</tr>
				<?php endforeach; endif; unset($_from); ?>
			</table>
		</div>
		<div style="padding:10px;" class="block">
			<div style="margin:0;" class="buttons">
				<button class="next" type="submit" name="show" value="1">Stemming publiceren</button>
				<button class="next" type="submit" name="show" value="0">Opslaan als concept</button>
				<button class="prev" type="submit" name="cancel">Annuleren</button>
			</div>
		</div>
	</div>
        <div class="rightcol">
            <p>Bij deze stap vult u alle gegevens over de stemming in.</p>
            <p><strong>Tip 1:</strong> Door te kiezen voor 'in behandeling' kunt u het raadsvoorstel publiceren zonder de stemming</p>
            <p><strong>Tip 2:</strong> Om de stemming dus daadwerkelijk te publiceren dient u te selecteren of het voorstel is aangenomen of afgewezen</p>
            <p><strong>Tip 3:</strong> Het systeem onthoudt gedurende de gehele sessie welke personen u op afwezig heeft gezet. Als deze aanwezigheid per raadsstuk verschilt, dient u deze dus handmatig te wijzigen.</p>
            <p><strong>Tip 4:</strong> Als personen op 'afwezig' staan, worden ze niet 'meegenomen' als u voor de gehele gemeente of per partij 'voor' of 'tegen' selecteert.</p>
    </div>
</div>
<div id="spacer" style="height: 6em"/>
</form>
