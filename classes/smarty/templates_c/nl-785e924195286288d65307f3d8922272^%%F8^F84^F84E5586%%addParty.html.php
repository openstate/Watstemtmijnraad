<?php /* Smarty version 2.6.18, created on 2011-01-19 15:25:49
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/addParty.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'strtolower', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/addParty.html', 2, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/addParty.html', 2, false),array('modifier', 'count', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/addParty.html', 60, false),array('modifier', 'max', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/addParty.html', 60, false),array('modifier', 'range', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/addParty.html', 61, false),)), $this); ?>
<h2>Een partij toevoegen aan</h2>
<h4>de <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['region']->level_name)) ? $this->_run_mod_handler('strtolower', true, $_tmp) : strtolower($_tmp)))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['region']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</h4>
<script type="text/javascript"><?php echo '
	function toggleNew(el) {
		if (el.selectedIndex == 1)
			$(\'new\').style.visibility = \'\';
		else
			$(\'new\').style.visibility = \'hidden\';
	}

	function toggleCombination(enable) {
		if (enable)
			$(\'combination\').style.visibility = \'\';
		else
			$(\'combination\').style.visibility = \'hidden\';
	}
	
	function addParent() {
		var clone = $(\'combination_clone\').clone();
		$ES(\'select\', clone).each(function (e) { e.selectedIndex = 0; });
		clone.inject(\'inject_before\', \'before\');
	}
'; ?>
</script>
<div class="forms">
    <div class="leftcol">
		<form action="" method="post">
			<div class="block">
				<div class="field field-party">
					<label for="party">Selecteer de partij die u wilt toevoegen</label>
					<select id="party" name="party" onclick="toggleNew(this);">
						<option value="">&gt; Selecteer een partij</option>
						<option value="new"<?php if ($this->_tpl_vars['post']['party'] == 'new'): ?> selected="selected"<?php endif; ?>>Ik wil een andere partij toevoegen</option>
					<?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['p']):
?>
						<option value="<?php echo $this->_tpl_vars['p']->id; ?>
"><?php echo ((is_array($_tmp=$this->_tpl_vars['p']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</option>
					<?php endforeach; endif; unset($_from); ?>
					</select>
				</div>
				<div id="new"<?php if ($this->_tpl_vars['post']['party'] != 'new'): ?> style="visibility: hidden;"<?php endif; ?>>
					<div class="field field-name">
						<label for="name">Hoe heet de partij?</label>
						<input id="name" name="name" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['name'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" maxlength="255" />
						<?php if ($this->_tpl_vars['error']['name']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
					</div>
					<div class="field field-short_form">
						<label for="short_form">Heeft de partij een afkorting?</label>
						<label><input type="radio" name="has_short_form" value="0"<?php if (! $this->_tpl_vars['post']['has_short_form']): ?> checked="checked"<?php endif; ?> /> Nee</label>
						<label><input type="radio" name="has_short_form" value="1"<?php if ($this->_tpl_vars['post']['has_short_form']): ?> checked="checked"<?php endif; ?> /> Ja, namelijk:</label>
						<input id="short_form" name="short_form" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['short_form'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" maxlength="255" />
						<div>bijvoorbeeld: 'PvdA' of 'CDA'</div>
						<?php if ($this->_tpl_vars['error']['short_form']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
						<?php if ($this->_tpl_vars['error']['short_form_invalid']): ?><div class="error">Deze waarde is te lang</div><?php endif; ?>
					</div>
					<div class="field field-combination">
						<label>Is de partij een combinatiepartij?</label>
						<label><input type="radio" name="combination" value="0"<?php if (! $this->_tpl_vars['post']['combination']): ?> checked="checked"<?php endif; ?> onclick="toggleCombination(false);" /> Nee</label>
						<label><input type="radio" name="combination" value="1"<?php if ($this->_tpl_vars['post']['combination']): ?> checked="checked"<?php endif; ?> onclick="toggleCombination(true);" /> Ja</label>
					</div>
					<div class="field combination" id="combination"<?php if (! $this->_tpl_vars['post']['combination']): ?> style="visibility:hidden;"<?php endif; ?>>
						<label>De partij is een combinatie van:</label>
						<?php $this->assign('count', ((is_array($_tmp=count($this->_tpl_vars['post']['parent']))) ? $this->_run_mod_handler('max', true, $_tmp, 2) : max($_tmp, 2))); ?><?php $this->assign('count', $this->_tpl_vars['count']-1); ?>
						<?php $_from = ((is_array($_tmp=0)) ? $this->_run_mod_handler('range', true, $_tmp, $this->_tpl_vars['count']) : range($_tmp, $this->_tpl_vars['count'])); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i']):
?>
							<?php if ($this->_tpl_vars['i'] == $this->_tpl_vars['count']): ?><div id="combination_clone"><?php endif; ?>
							<?php if ($this->_tpl_vars['i'] > 0): ?><div>en</div><?php endif; ?>
							<select name="parent[]">
								<option value="">&gt; Selecteer een partij</option>
							<?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['p']):
?>
								<option value="<?php echo $this->_tpl_vars['p']->id; ?>
"<?php if ($this->_tpl_vars['p']->id == $this->_tpl_vars['post']['parent'][$this->_tpl_vars['i']]): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['p']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</option>
							<?php endforeach; endif; unset($_from); ?>
							</select>
							<?php if ($this->_tpl_vars['i'] == $this->_tpl_vars['count']): ?></div><?php endif; ?>
						<?php endforeach; endif; unset($_from); ?>
						<div id="inject_before"><a onclick="addParent(); return false;" href="#">nog een partij aan deze combinatie toevoegen</a></div>
					</div>
				</div>
			</div>

			<div style="padding:10px;" class="block">
				<div style="margin:0;" class="buttons">
					<button type="submit" name="next" class="next">Partij toevoegen</button>
					<button type="submit" name="cancel" class="prev">Annuleren</button>
				</div>
			</div>
        </form>
    </div>
    
    <div class="rightcol">
        <p>Toelichting voor stap 1 Vestibulum ut porttitor mi. Sed suscipit, turpis at facilisis molestie, turpis nibh ultricies augue, sed hendrerit purus libero eu nisi.</p>
        <p>Suspendisse id velit ac nibh consectetur pulvinar. Nulla at felis in lorem dignissim tristique. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
    </div>
</div>