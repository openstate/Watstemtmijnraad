<?php /* Smarty version 2.6.18, created on 2010-12-13 13:05:12
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'urlencode', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html', 20, false),array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html', 36, false),array('modifier', 'nl2br', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html', 36, false),array('modifier', 'truncate', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html', 37, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html', 38, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html', 81, false),array('function', 'cycle', '/var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/content/indexPage.html', 34, false),)), $this); ?>
<div class="big_button"><a href="create/"><img src="/images/add.png" alt="Toevoegen" title="Toevoegen" border="0"/> Voeg een raadstuk toe</a></div>
<?php if ($this->_tpl_vars['archive']): ?>
<div>
    <a href=".">&lt; terug naar ongepubliceerde raadsstukken</a>
</div>
<h2>Raadsstukkenarchief</h2>
<?php else: ?>
<h2>Ongepubliceerde raadsstukken</h2>
<h3>en raadstukken zonder stemming</h3>
<?php endif; ?>



<?php if ($this->_tpl_vars['pager']): ?><p class="pager"><?php echo $this->_tpl_vars['pager']; ?>
</p><?php endif; ?>

<table class="list">
	<tr>
		<?php ob_start(); ?><?php echo ''; ?><?php if ($this->_tpl_vars['archive']): ?><?php echo 'archive=1&amp;'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['get']['code']): ?><?php echo 'code='; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?><?php echo '&amp;'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['get']['title']): ?><?php echo 'title='; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['title'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?><?php echo '&amp;'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['get']['vote_date']): ?><?php echo 'vote_date='; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['vote_date'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?><?php echo '&amp;'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['get']['category']): ?><?php echo 'category='; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['category'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?><?php echo '&amp;'; ?><?php endif; ?><?php echo ''; ?><?php if ($this->_tpl_vars['get']['summary']): ?><?php echo 'summary='; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['summary'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?><?php echo '&amp;'; ?><?php endif; ?><?php echo ''; ?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('query', ob_get_contents());ob_end_clean(); ?>
		<th><a href="?<?php echo $this->_tpl_vars['query']; ?>
sortcol=show&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'show' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'show'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>">Status</a></th>
		<th><a href="?<?php echo $this->_tpl_vars['query']; ?>
sortcol=code&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'code' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'code'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>">Code</a></th>
		<th><a href="?<?php echo $this->_tpl_vars['query']; ?>
sortcol=title&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'title' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'title'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>">Titel</a></th>
		<th><a href="?<?php echo $this->_tpl_vars['query']; ?>
sortcol=vote_date&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'vote_date' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'vote_date'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>">Stemdatum</a></th>
		<th><a href="?<?php echo $this->_tpl_vars['query']; ?>
sortcol=type_name&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'type_name' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'type_name'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>">Soort</a></th>
		<th>Opties</th>
	</tr>
	<?php $_from = $this->_tpl_vars['formdata']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['datarow']):
?>
	<tr class="link<?php echo smarty_function_cycle(array('values' => ', alt'), $this);?>
">
		<td><?php if ($this->_tpl_vars['datarow']->show): ?>gepubliceerd<?php else: ?>ongepubliceerd<?php endif; ?></td>
		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['datarow']->code)) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
		<td><a href="/raadsstukken/edit/<?php echo $this->_tpl_vars['datarow']->id; ?>
"><strong><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['datarow']->title)) ? $this->_run_mod_handler('truncate', true, $_tmp, 120) : smarty_modifier_truncate($_tmp, 120)))) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</strong></a></td>
		<td><?php echo ((is_array($_tmp=$this->_tpl_vars['datarow']->vote_date)) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")); ?>
</td>
		<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['datarow']->type_name)) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
		<td>
			<a class="vote" href="/raadsstukken/vote/<?php echo $this->_tpl_vars['datarow']->id; ?>
"><?php if ($this->_tpl_vars['datarow']->result): ?><?php echo $this->_tpl_vars['datarow']->vote_0; ?>
 voor, <?php echo $this->_tpl_vars['datarow']->vote_1; ?>
 tegen
			<?php elseif ($this->_tpl_vars['datarow']->showVotes()): ?>stemming toevoegen<?php endif; ?></a>
			<a href="/raadsstukken/edit/<?php echo $this->_tpl_vars['datarow']->id; ?>
"><img src="/images/edit.png" alt="Wijzigen" title="Wijzigen" border="0" /></a>
			<?php if (! $this->_tpl_vars['datarow']->show): ?><a href="/raadsstukken/delete/<?php echo $this->_tpl_vars['datarow']->id; ?>
"><img src="/images/delete.png" alt="Verwijderen" title="Verwijderen" border="0" /></a><?php endif; ?>
		</td>
	</tr>
	<?php endforeach; else: ?>
	<tr><td colspan="2">Geen raadsstukken gevonden</td></tr>
	<?php endif; unset($_from); ?>
</table>
<?php if (! $this->_tpl_vars['archive']): ?>
<div class="bg-center">
    <a class="big-font" href="?archive=1">Ga naar het archief</a>
</div>
<?php endif; ?>
<?php if ($this->_tpl_vars['archive']): ?>
<div><form action="" method="get">
<script type="text/javascript"><?php echo '
function removeFilter(name, select) {
	$(name).style.display = \'\';
	$(name+\'_val\').style.display = \'none\';
	if (select)
		$(name+\'_input\').selectedIndex = 0;
	else
		$(name+\'_input\').value = \'\';
	return false;
}
'; ?>
</script>
<input type="hidden" name="archive" val="1"/>
<table>
<tr>
<td colspan="2">
<h3>Filtering</h3>
</td>
</tr
<tr>
<td>
Code:
</td>
<td>
<?php if ($this->_tpl_vars['get']['code']): ?><div id="code_val"><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['code'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <a href="#" onclick="return removeFilter('code');">x</a></div><?php endif; ?>
<div id="code"<?php if ($this->_tpl_vars['get']['code']): ?> style="display:none;"<?php endif; ?>><input type="text" name="code" id="code_input" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['get']['code'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" /></div>
</td>
</tr>
<tr>
<td>
Titel:
</td>
<td>
<?php if ($this->_tpl_vars['get']['title']): ?><div id="title_val"><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['title'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <a href="#" onclick="return removeFilter('title');">x</a></div><?php endif; ?>
<div id="title"<?php if ($this->_tpl_vars['get']['title']): ?> style="display:none;"<?php endif; ?>><input type="text" name="title" id="title_input" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['get']['title'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" /></div>
</td>
</tr>
<tr>
<td>
Datum: 
</td>
<td>
<?php if ($this->_tpl_vars['get']['vote_date']): ?><div id="vote_date_val"><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['vote_date'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <a href="#" onclick="return removeFilter('vote_date');">x</a></div><?php endif; ?>
<div id="vote_date"<?php if ($this->_tpl_vars['get']['vote_date']): ?> style="display:none;"<?php endif; ?>><input type="text" name="vote_date" id="vote_date_input" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['get']['vote_date'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" /></div>
</td>
</tr>
<tr>
<td>
Categorie: 
</td>
<td>

<?php if ($this->_tpl_vars['get']['category']): ?><div id="category_val"><?php $this->assign('cat', $this->_tpl_vars['get']['category']); ?><?php echo ((is_array($_tmp=$this->_tpl_vars['categories'][$this->_tpl_vars['cat']]->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <a href="/raadsstukken/?archive=1">x</a></div><?php endif; ?>
<div id="category"<?php if ($this->_tpl_vars['get']['category']): ?> style="display:none;"<?php endif; ?>><select name="category" id="category_input">
	<option value="">&#160;</option>
<?php $_from = $this->_tpl_vars['categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['cat']):
?>
	<option value="<?php echo $this->_tpl_vars['key']; ?>
"<?php if ($this->_tpl_vars['key'] == $this->_tpl_vars['get']['category']): ?> selected="selected"<?php endif; ?>><?php echo ((is_array($_tmp=$this->_tpl_vars['cat']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</option>
<?php endforeach; endif; unset($_from); ?>
</select></div>
</td>
</tr>
<tr>
<td>
Woord(en) in samenvatting: 
</td>
<td>
<?php if ($this->_tpl_vars['get']['summary']): ?><div id="summary_val"><?php echo ((is_array($_tmp=$this->_tpl_vars['get']['summary'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
 <a href="#" onclick="return removeFilter('summary');">x</a></div><?php endif; ?>
<div id="summary"<?php if ($this->_tpl_vars['get']['summary']): ?> style="display:none;"<?php endif; ?>><input type="text" name="summary" id="summary_input" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['get']['summary'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" /></div>
</td>
</tr>
</table>
<div class="container-buttons"><button class="submit" type="submit">Filteren</button></div>
</form></div>
<?php endif; ?>