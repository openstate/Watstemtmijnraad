<?php /* Smarty version 2.6.18, created on 2010-12-13 13:05:22
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 20, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 33, false),array('modifier', 'truncate', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 45, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 45, false),array('modifier', 'implode', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 87, false),array('modifier', 'array_keys', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 89, false),array('modifier', 'count', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 90, false),array('modifier', 'max', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 90, false),array('modifier', 'range', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 91, false),array('function', 'html_options', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/formPage.html', 40, false),)), $this); ?>



<h2><?php echo $this->_tpl_vars['form']['header']; ?>
</h2>
<h4><?php echo $this->_tpl_vars['form']['note']; ?>
</h4>

<div class="forms">
	<?php if (! $this->_tpl_vars['create']): ?>
   	<ul class="steps">
        <li><a class="active" href="#">Gegevens</a></li>
        <li><a href="/raadsstukken/vote/<?php echo $this->_tpl_vars['formdata']['id']; ?>
">Stemmingen</a></li>
    </ul>
	<?php endif; ?>
    <div class="leftcol">
		<form action="" name="<?php echo $this->_tpl_vars['form']['name']; ?>
" method="post" onsubmit="return formSubmit(this)" enctype="multipart/form-data" id="rsForm">
			<div class="block">
				<h1>1. Basisinformatie</h1>
				<div class="field">
					<label for="title">Titel</label>
					<?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['title'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="title" value="<?php echo $this->_tpl_vars['formdata']['title']; ?>
" id="title" class="large vld_required defErrorHandler" onkeyup="revalidate(this.form)" /> <div class="error" id="_err_title_required" style="<?php if (! $this->_tpl_vars['formerrors']['title_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</div><?php endif; ?>
				</div>
				<div class="field">
					<label for="title">Subtitel</label>
					<?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['metainfo'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="metainfo" value="<?php echo $this->_tpl_vars['formdata']['metainfo']; ?>
" id="metainfo" class="large defErrorHandler" onkeyup="revalidate(this.form)" /> <div class="error" id="_err_metainfo_required" style="<?php if (! $this->_tpl_vars['formerrors']['metainfo_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</div><?php endif; ?>
				</div>
				<div class="field" style="width: 60%; z-index:10;">
					<label for="code">Code</label>
					<?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['code'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><input type="text" name="code" class="vld_required defErrorHandler" value="<?php echo $this->_tpl_vars['formdata']['code']; ?>
" onkeyup="revalidate(this.form)"/> <div class="error" id="_err_code_required" style="<?php if (! $this->_tpl_vars['formerrors']['code_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</div><?php endif; ?>
				</div>
				<div class="field" style="width: 40%; z-index:10;">
					<label for="vote_date">Stemdatum</label>
                    <div>
                        <input class="text calendar" id="vote_date" type="text" maxlength="20"  name="vote_date" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['vote_date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e-%m-%Y') : smarty_modifier_date_format($_tmp, '%e-%m-%Y')); ?>
" />
                        <button class="calendar" type="button" style="display: none;"></button>
                    </div>
									</div>
				<div class="field" style="clear: left;">
					<label for="type">Soort stuk</label>
					<?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['type_name'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
<?php else: ?><?php echo smarty_function_html_options(array('id' => 'type','name' => 'type','options' => $this->_tpl_vars['types'],'selected' => $this->_tpl_vars['formdata']['type']), $this);?>
 <div class="error" id="_err_type_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['type_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</div><?php endif; ?>
				</div>
				<?php if (! $this->_tpl_vars['form']['freeze']): ?><div class="field" id="parent_row"<?php if ($this->_tpl_vars['formdata']['type'] != 3 && $this->_tpl_vars['formdata']['type'] != 4): ?> style="display: none"<?php endif; ?>>
					<label for="parent">Raadsstuk</label>
					<span id="parent_el">
						<?php echo smarty_function_html_options(array('id' => 'parent','name' => 'parent','options' => ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['rs_parents'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 80) : smarty_modifier_truncate($_tmp, 80)))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)),'selected' => $this->_tpl_vars['formdata']['parent']), $this);?>

					</span>
					<input type="checkbox" name="unrestrict_parent" id="unrestrict_parent"<?php if ($this->_tpl_vars['formdata']['unrestrict_parent']): ?> checked="checked"<?php endif; ?> onchange="unrestrictParentOnChange();" />
					Alle raadsstukken tonen
				</div><?php endif; ?>
				<div class="field" id="sub_el">
					<label for="submitters">Ingediend door</label>
					<?php if ($this->_tpl_vars['form']['freeze']): ?>
						<?php if ($this->_tpl_vars['formdata']['submit_type'] == 3 || $this->_tpl_vars['formdata']['submit_type'] == 4): ?>
							<?php $_from = $this->_tpl_vars['formdata']['submitters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['foo'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['foo']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['i']):
        $this->_foreach['foo']['iteration']++;
?>
								<?php if (isset ( $this->_tpl_vars['councilMembers'][$this->_tpl_vars['i']] )): ?><?php echo $this->_tpl_vars['councilMembers'][$this->_tpl_vars['i']]->formatName(); ?>

									<?php if (! ($this->_foreach['foo']['iteration'] == $this->_foreach['foo']['total'])): ?>, <?php endif; ?>
								<?php endif; ?>
							<?php endforeach; endif; unset($_from); ?>
						<?php else: ?><?php echo $this->_tpl_vars['formdata']['submit_type_name']; ?>
<?php endif; ?>
					<?php else: ?>
					<div id="sub_el_rs"<?php if ($this->_tpl_vars['formdata']['type'] != 1): ?> style="display: none"<?php endif; ?>>
						<?php echo smarty_function_html_options(array('id' => 'submitters_rs','name' => 'submitters','class' => 'vld_required_select','options' => $this->_tpl_vars['rs_submitters'],'selected' => $this->_tpl_vars['formdata']['submit_type']), $this);?>

					</div>
					<div id="sub_el_members"<?php if ($this->_tpl_vars['formdata']['type'] != 2 && $this->_tpl_vars['formdata']['type'] != 3 && $this->_tpl_vars['formdata']['type'] != 4): ?> style="display: none"<?php endif; ?>>
						<?php echo smarty_function_html_options(array('id' => 'submitters','name' => "submitters[]",'class' => 'vld_required_select idErrorHandler','multiple' => 'multiple','size' => '16','options' => $this->_tpl_vars['councilView'],'selected' => $this->_tpl_vars['formdata']['submitters'],'onclick' => "revalidate(this.form)"), $this);?>

					</div>
					<div id="sub_el_citizen"<?php if ($this->_tpl_vars['formdata']['type'] != 5): ?> style="display: none"<?php endif; ?>><span class="non-select">Burger</span></div>
					<div id="sub_el_onbekend"<?php if ($this->_tpl_vars['formdata']['type'] != 6): ?> style="display: none"<?php endif; ?>><span class="non-select">Onbekend</span></div>
					<div class="error" id="_err_submitters_required" style="<?php if (! $this->_tpl_vars['formerrors']['submitters_required']): ?>display:none<?php endif; ?>">Dit veld is verplicht</div>
					<?php endif; ?>
				</div>

                <div class="field" id="sub_el">
                    <label for="party">Ingediend door partij: </label>
                    <?php echo smarty_function_html_options(array('id' => 'party','name' => 'party','options' => $this->_tpl_vars['list_parties'],'selected' => $this->_tpl_vars['selected_party']), $this);?>

                </div>

                <div class="field" id="sub_ext_url">
                    <label for="sub_ext_url">Externe informatie over dit raadsstuk (webadres):</label>
                    <input type="text"  size="40" value="<?php echo $this->_tpl_vars['ext_url_info']; ?>
" name="ext_url"/>
                </div>
			</div>
			<div class="block">
				<h1>2. Beleidsvelden</h1>
				<div class="field">
					<label for="category">In welk(e) beleidsveld(en) valt het raadsstuk?</label>
					<?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=', ')) ? $this->_run_mod_handler('implode', true, $_tmp, $this->_tpl_vars['formdata']['cats']) : implode($_tmp, $this->_tpl_vars['formdata']['cats'])); ?>
<?php else: ?>
						<?php if (! $this->_tpl_vars['formdata']['cats']): ?><?php $this->assign('cats', ''); ?><?php else: ?>
						<?php $this->assign('cats', array_keys($this->_tpl_vars['formdata']['cats'])); ?><?php endif; ?>
						<?php $this->assign('count', ((is_array($_tmp=count($this->_tpl_vars['formdata']['cats']))) ? $this->_run_mod_handler('max', true, $_tmp, 1) : max($_tmp, 1))); ?><?php $this->assign('count', $this->_tpl_vars['count']-1); ?>
						<?php $_from = ((is_array($_tmp=0)) ? $this->_run_mod_handler('range', true, $_tmp, $this->_tpl_vars['count']) : range($_tmp, $this->_tpl_vars['count'])); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i']):
?>
							<?php if ($this->_tpl_vars['i'] == $this->_tpl_vars['count']): ?><div id="cat_clone"><?php endif; ?><select class="select-margin" name="cats[]">
								<option value="">&#160;</option>
							<?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['cat']):
?>
								<option value="<?php echo $this->_tpl_vars['key']; ?>
"<?php if ($this->_tpl_vars['key'] == $this->_tpl_vars['cats'][$this->_tpl_vars['i']]): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['cat'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</option>
							<?php endforeach; endif; unset($_from); ?>
							</select><?php if ($this->_tpl_vars['i'] == $this->_tpl_vars['count']): ?></div><?php endif; ?>
						<?php endforeach; endif; unset($_from); ?>
						<span class="cat_add" id="cat_add">nog een beleidsveld toevoegen</span>
						<div class="error" id="_err_category_invalid" style="<?php if (! $this->_tpl_vars['formerrors']['category_invalid']): ?>display:none<?php endif; ?>">Ongeldige waarde</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="block">
				<h1>3. Onderwerpen</h1>
				<div class="field">
					<label for="tags">Welke specifieke onderwerpen heeft het raadsstuk?</label>
					<?php if ($this->_tpl_vars['form']['freeze']): ?><?php echo ((is_array($_tmp=', ')) ? $this->_run_mod_handler('implode', true, $_tmp, $this->_tpl_vars['formdata']['tags']) : implode($_tmp, $this->_tpl_vars['formdata']['tags'])); ?>
<?php else: ?>
						<?php $this->assign('count', ((is_array($_tmp=count($this->_tpl_vars['formdata']['tags']))) ? $this->_run_mod_handler('max', true, $_tmp, 1) : max($_tmp, 1))); ?><?php $this->assign('count', $this->_tpl_vars['count']-1); ?>
						<?php if (! $this->_tpl_vars['formdata']['tags']): ?>
						<div id="tag_empty" class="tag_empty"><span class="non-select">Nog geen onderwerpen gekozen</span></div>
						<?php endif; ?>
						<?php $_from = ((is_array($_tmp=0)) ? $this->_run_mod_handler('range', true, $_tmp, $this->_tpl_vars['count']) : range($_tmp, $this->_tpl_vars['count'])); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['i']):
?>
							<?php if ($this->_tpl_vars['i'] == $this->_tpl_vars['count']): ?><div id="tag_clone"<?php if (! $this->_tpl_vars['formdata']['tags']): ?> style="display: none;"<?php endif; ?>><?php endif; ?>
							<input name="tags[]" class="large tag_autocomplete" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['tags'][$this->_tpl_vars['i']])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" />
							<?php if ($this->_tpl_vars['i'] == $this->_tpl_vars['count']): ?></div><?php endif; ?>
						<?php endforeach; endif; unset($_from); ?>
					<span id="tag_add" class="tag_add">voeg een onderwerp toe</span>
					<?php endif; ?>
				</div>
			</div>
			<div class="block">
				<h1>4. Samenvatting</h1>
				<div class="field">
					<?php if ($this->_tpl_vars['form']['freeze']): ?><div class="summary"><?php echo $this->_tpl_vars['formdata']['summary']; ?>
</div><?php else: ?><textarea name="summary" class="richtext" rows="5" cols="40"><?php echo ((is_array($_tmp=$this->_tpl_vars['formdata']['summary'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</textarea> <div class="error" id="_err_summary_too_large" style="<?php if (! $this->_tpl_vars['formerrors']['summary_too_large']): ?>display:none<?php endif; ?>">De waarde is te groot voor dit veld</div><?php endif; ?>
				</div>
			</div>
			<div style="padding:10px;" class="block">
				<div style="margin:0;" class="buttons">
					<?php if (! $this->_tpl_vars['form']['freeze']): ?>
					<button type="submit" class="next" name="show" value="1">Opslaan en publiceren</button>
					<button type="submit" class="next" name="show" value="0">Opslaan als concept</button>
					<?php else: ?>
					<button type="submit" class="next" name="submit"><?php echo $this->_tpl_vars['form']['submitText']; ?>
</button>
					<?php endif; ?>
					<?php if (strlen ( $this->_tpl_vars['form']['extraButton'] )): ?>
					<button type="submit" class="next" name="submit_vote"><?php echo $this->_tpl_vars['form']['extraButton']; ?>
</button>
					<?php endif; ?>
					<button type="submit" class="prev" name="cancel">Annuleren</button>
				</div>
			</div>
		</form>
        
    </div>
    
    <div class="rightcol">
        <p>
            Stap 1: Bij de eerste stap vult u de algemene informatie met betrekking tot het raadsstuk in.
            <br/><strong>Tip 1:</strong> Hou de titel kort en verwerk extra informatie in de subtitel
            <br/><strong>Tip 2:</strong> In het veld "externe informatie" kunt u de link naar het stuk in uw raadssysteem opgeven
        </p>
        <p>
            Stap 2: Hier kiest u debeleidsvelden. U kunt extra beleidsvelden toevoegen door op het 'plus'-teken te klikken.
        </p>
        <p>
            Stap 3: Hier kiest u het relevante onderwerp. Met behulp van het 'plus'-teken kunt u meerdere onderwerpen toevoegen.
            <br/><strong>Tip 3:</strong> Als u de eerste letter typt, laat het systeem vervolgens alle bijpassende suggesties in het systeem zien, deze kunt u aanklikken</p>
        <p>
            Stap 4: kunt de omschrijving van het raadsvoorstel invoeren. Met behulp van de beschikbare knoppen kunt u de tekst opmaken.
            <br/><strong>Tip 4:</strong> Door een stukje tekst te selecteren en vervolgens op het koppelsymbool te klikken, kunt u een link toevoegen
        </p>
    </div>
</div>