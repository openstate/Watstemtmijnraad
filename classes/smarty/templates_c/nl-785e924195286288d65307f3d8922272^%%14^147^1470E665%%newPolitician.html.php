<?php /* Smarty version 2.6.18, created on 2010-12-23 15:45:28
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/newPolitician.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/newPolitician.html', 2, false),array('modifier', 'strtolower', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/newPolitician.html', 2, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/newPolitician.html', 119, false),array('modifier', 'trim', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/newPolitician.html', 119, false),)), $this); ?>
<h2>Een politicus toevoegen aan</h2>
<h4>de <?php if ($this->_tpl_vars['party']->short_form): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['party']->short_form)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['party']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
<?php endif; ?>-fractie in de <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['region']->level_name)) ? $this->_run_mod_handler('strtolower', true, $_tmp) : strtolower($_tmp)))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['region']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</h4>
<div class="forms">
    <div class="leftcol">
		<form action="" method="post">
			<div class="block">
				<h1>1. De politicus</h1>
            	<div class="field field-achternaam">
                    <label for="name">Typ de achternaam van de politicus</label>
                    <input id="name" name="last_name" type="text" maxlength="255" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['last_name'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" />
					<input id="politician_id" name="politician_id" type="hidden" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['politician_id'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" />
					<?php if ($this->_tpl_vars['error']['last_name']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
					<script type="text/javascript" src="/javascripts/mootools/moo.tools.v1.11.js"></script>
					<script type="text/javascript" src="/javascripts/mootools/Observer.js"></script>
					<script type="text/javascript" src="/javascripts/mootools/Autocompleter.js"></script>
					<script type="text/javascript"><?php echo '
						window.addEvent(\'domready\', function() {
							autocomplete = new Autocompleter.Ajax.Json(\'name\', \'/wizard/newPolitician\', {
								\'postVar\': \'autocomplete\',
								\'injectChoice\': function(choice, i) {
									var el = new Element(\'li\').setHTML(this.markQueryValue(choice.name+(choice.description ? \' (\'+choice.description+\')\' : \'\')));
									el.inputValue = choice.name;
									el.id = choice.id
									el.description = choice.description;
									this.addChoiceEvents(el).injectInside(this.choices);
								}
							});
							autocomplete.markQueryValue = function(txt) {
								if (txt == \' (Nieuwe politicus toevoegen)\') return \'Nieuwe politicus toevoegen\';
								return (this.options.markQuery && this.queryValue) ? txt.replace(new RegExp(\'(\' + this.queryValue.escapeRegExp() + \')\', \'i\'), \'<span class="autocompleter-queried">$1</span>\') : txt;
							}
							autocomplete.choiceSelect = function(el) {
								$(\'politician_id\').value = el.id;
								this.hideChoices();
								if (el.inputValue)
									this.observer.value = this.element.value = el.inputValue;
								$(\'new\').style.visibility = el.inputValue ? \'hidden\' : \'\';
								this.fireEvent(\'onSelect\', [this.element], 20);
							};
							autocomplete.element.addEvent(window.ie ? \'keydown\' : \'keypress\', function(e) {
								if (!e.shift && e.key == \'backspace\' && $(\'politician_id\').value != \'\') {
									this.observer.value = this.element.value = \'\';
									$(\'politician_id\').value = \'\';
								} else if ($(\'politician_id\').value != \'\' && (e.shift || (e.key != \'enter\' && e.key != \'up\' && e.key != \'down\' && e.key != \'esc\'))) {
									e.stop()
								}
							}.bindWithEvent(autocomplete));
							autocomplete.element.addEvent(\'blur\', function(e) {
								if (this.selected)
									this.choiceSelect(this.selected)
								if (this.element.value && !$(\'politician_id\').value)
									$(\'new\').style.visibility = \'\';
							}.bindWithEvent(autocomplete));
						});
					'; ?>
</script>
    			</div>
                <div id="new"<?php if ($this->_tpl_vars['post']['politician_id'] || ! $this->_tpl_vars['post']['last_name']): ?> style="visibility: hidden;"<?php endif; ?>>
					<div class="field field-voornaam">
						<label for="voornaam">Voornaam</label>
						<input id="voornaam" name="first_name" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['first_name'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" maxlength="255" />
						<?php if ($this->_tpl_vars['error']['first_name']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
					</div>
					
					<div class="field field-titel">
						<label for="titel">Titel</label>
						<input id="titel" name="title" type="text" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['title'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" maxlength="255" />
					</div>       
					
					<div class="field field-geslacht">
						<label for="geslacht">Geslacht</label>
						<label class="geslacht" for="geslacht_m"><input class="radio" type="radio" id="geslacht_m" name="gender" value="m"<?php if ($this->_tpl_vars['post']['gender'] == 'm'): ?> checked="checked"<?php endif; ?> /> Man</label>     
						<label class="geslacht" for="geslacht_v"><input class="radio" type="radio" id="geslacht_v" name="gender" value="f"<?php if ($this->_tpl_vars['post']['gender'] == 'f'): ?> checked="checked"<?php endif; ?> /> Vrouw</label>  
						<?php if ($this->_tpl_vars['error']['gender']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
					</div>    
					
					<div class="field field-email">           
						<label for="email">Emailadres</label>
						<input id="email" name="email" type="text" maxlength="255" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['email'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" /> 
						<?php if ($this->_tpl_vars['error']['email']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
						<?php if ($this->_tpl_vars['error']['email_invalid']): ?><div class="error">Deze waarde is ongeldig</div><?php endif; ?>
					</div>           
					
					<div class="field field-externid"> 
						<label for="externid">Extern ID</label>
						<input id="externid" name="extern_id" type="text" maxlength="10" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['extern_id'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" />  
						<?php if ($this->_tpl_vars['error']['extern_id_invalid']): ?><div class="error">Deze waarde is ongeldig</div><?php endif; ?>
					</div>          
				</div>
			</div>
			
			<div class="block">
				<h1>2. Het beleidsveld</h1>
            
				<p style="margin-top:5px;">Op welk beleidsveld is de politicus actief voor de <?php if ($this->_tpl_vars['party']->short_form): ?><?php echo ((is_array($_tmp=$this->_tpl_vars['party']->short_form)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['party']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
<?php endif; ?>-fractie in de <?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['region']->level_name)) ? $this->_run_mod_handler('strtolower', true, $_tmp) : strtolower($_tmp)))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['region']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
?</p>
				<select style="height:25px; padding:3px;" name="category">
					<?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['cat']):
?>
                    <option value="<?php echo $this->_tpl_vars['key']; ?>
"<?php if ($this->_tpl_vars['post']['category'] == $this->_tpl_vars['key']): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['cat']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</option>
					<?php endforeach; endif; unset($_from); ?>
                </select>
				<?php if ($this->_tpl_vars['error']['category']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
            			</div>

			<div class="block">
				<h1>3. De periode</h1>
				<p style="margin-top:5px;">Voor welke periode heeft de politicus deze aanstellingen?</p>
				<h4>Aanvangdatum</h4>
                <div class="field field-kiesperiode">
                	<table class="date">
                        <tr>
                            <td class="first">
                                <label class="kiesperiode" for="kiesperiode"><input class="radio" type="radio" id="kiesperiode" name="time_start_default" value="1"<?php if ($this->_tpl_vars['post']['time_start_default']): ?> checked="checked"<?php endif; ?> />aanvang kiesperiode</label>
                            </td>
                            <td>
                    <strong>(<?php echo ((is_array($_tmp=((is_array($_tmp=$_SESSION['setup_wizard']['date'])) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %B %Y') : smarty_modifier_date_format($_tmp, '%e %B %Y')))) ? $this->_run_mod_handler('trim', true, $_tmp) : trim($_tmp)); ?>
)</strong>
                            </td>
                        
                        
                        </tr>
                    </table>
                </div>
                
                <div class="field field-kiesperiode">
                	<table class="date">
                        <tr>
                            <td class="first">
                            <label class="kiesperiode" for="kiesperiode-other"><input class="radio" type="radio" id="kiesperiode-other" name="time_start_default" value="0"<?php if (! $this->_tpl_vars['post']['time_start_default']): ?> checked="checked"<?php endif; ?>/>andere datum</label>
                            </td>
                            <td>
                                <div>
            		<input class="text calendar" type="text" name="time_start" id="delivery_date" readonly="" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['time_start'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" maxlength="10" />
                    <button type="button" style="display:none;" class="calendar"> </button>
					<?php if ($this->_tpl_vars['error']['time_start']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
					<?php if ($this->_tpl_vars['error']['time_start_invalid']): ?><div class="error">Deze waarde is ongeldig</div><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

				<?php if (! $this->_tpl_vars['post']['time_end']): ?>
                <div class="field field-kiesperiode" id="time_end_link">
					<a onclick="$('time_end_link').style.display = 'none'; $('time_end_input').style.display = ''; return false;" class="add" href="#">Ook een einddatum toevoegen</a>
				</div>
				<?php endif; ?>
           
                <div class="field field-kiesperiode" id="time_end_input"<?php if (! $this->_tpl_vars['post']['time_end']): ?> style="display: none;"<?php endif; ?>>
					<h4>Einddatum</h4>

					<input type="text" class="text calendar" id="delivery_date2" readonly="" name="time_end" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['time_end'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" maxlength="10" />
                    <button type="button" style="display:none;" class="calendar"> </button>
					<?php if ($this->_tpl_vars['error']['time_end_invalid']): ?><div class="error">Deze waarde is ongeldig</div><?php endif; ?>
				</div>
				
			</div>
        
			<div style="padding:10px;" class="block">
				<div style="margin:0;" class="buttons">
					<button type="submit" class="prev" name="prev">Terug naar partijen</button>
					<button type="submit" class="next" name="next">Deze politicus toevoegen</button>
				</div>
			</div>
		</form>
        
    </div>
    
    <div class="rightcol">
        <p>Toelichting voor stap 1 Vestibulum ut porttitor mi. Sed suscipit, turpis at facilisis molestie, turpis nibh ultricies augue, sed hendrerit purus libero eu nisi.</p>
        <p>Suspendisse id velit ac nibh consectetur pulvinar. Nulla at felis in lorem dignissim tristique. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
    </div>
</div>