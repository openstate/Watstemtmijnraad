<?php /* Smarty version 2.6.18, created on 2011-02-11 13:37:11
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/categories/content/indexPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cycle', '/var/www/projects/watstemtmijnraad_hg/pages/admin/categories/content/indexPage.html', 29, false),array('modifier', 'htmlentities', '/var/www/projects/watstemtmijnraad_hg/pages/admin/categories/content/indexPage.html', 30, false),array('modifier', 'nl2br', '/var/www/projects/watstemtmijnraad_hg/pages/admin/categories/content/indexPage.html', 31, false),array('modifier', 'count', '/var/www/projects/watstemtmijnraad_hg/pages/admin/categories/content/indexPage.html', 33, false),)), $this); ?>
<h2>Beleidsvelden</h2>

<?php if ($this->_tpl_vars['error']): ?><p class="error"><?php echo $this->_tpl_vars['error']; ?>
</p>
<?php if ($this->_tpl_vars['apps_del']): ?>
<p>Volgende aanstellingen moeten worden verwijderd voordat deze categorie verwijderd mag worden.</p>
<div style="margin-bottom: 20px;">
	<?php $_from = $this->_tpl_vars['apps_del']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['apps'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['apps']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['app']):
        $this->_foreach['apps']['iteration']++;
?>
		<div><?php echo $this->_foreach['apps']['iteration']; ?>
. <a href="/appointments/<?php echo $this->_tpl_vars['app']['politician']; ?>
?all" title="Naar appointment"><?php echo $this->_tpl_vars['app']['party']; ?>
 in <?php echo $this->_tpl_vars['app']['region']; ?>
</a></div>
	<?php endforeach; endif; unset($_from); ?>
</div>
<?php endif; ?>
<?php endif; ?>

<p><a class="add" href="create/">Toevoegen</a></p>

<a name="CategoryList"></a>
<form action="" name="CategoryList">
	<div id="accordion">
		<table class="list">
			<tr>
				<th><a href="?sortcol=name&amp;sort=<?php if ($this->_tpl_vars['formsort']['col'] == 'name' && $this->_tpl_vars['formsort']['dir'] == 'asc'): ?>desc<?php else: ?>asc<?php endif; ?>" class="<?php if ($this->_tpl_vars['formsort']['col'] == 'name'): ?>current <?php echo $this->_tpl_vars['formsort']['dir']; ?>
<?php else: ?>asc<?php endif; ?>">Naam</a></th>
				<th>Beschrijving</th>
                <th>Niveau(s)</th>
                <th>Aantal raadsstukken</th>
				<th>Opties</th>
			</tr>
			<?php $_from = $this->_tpl_vars['formdata']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['dataloop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['dataloop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['datarow']):
        $this->_foreach['dataloop']['iteration']++;
?>
			<?php if ($this->_tpl_vars['datarow']['id'] > 0): ?>
			<tr class="link<?php echo smarty_function_cycle(array('values' => ', alt'), $this);?>
">
				<td><?php echo ((is_array($_tmp=$this->_tpl_vars['datarow']['category_name'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')); ?>
</td>
				<td><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['datarow']['description'])) ? $this->_run_mod_handler('htmlentities', true, $_tmp, 2, 'UTF-8') : htmlentities($_tmp, 2, 'UTF-8')))) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</td>
                <td>
                    <?php if (count($this->_tpl_vars['levels'][$this->_tpl_vars['datarow']['id']]) > 0): ?>
                        <?php $_from = $this->_tpl_vars['levels'][$this->_tpl_vars['datarow']['id']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['levels'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['levels']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['level']):
        $this->_foreach['levels']['iteration']++;
?>
                            <?php if (($this->_foreach['levels']['iteration'] == $this->_foreach['levels']['total']) == false): ?>
                                <?php echo $this->_tpl_vars['level']; ?>
,
                            <?php else: ?>
                                <?php echo $this->_tpl_vars['level']; ?>

                            <?php endif; ?>
                        <?php endforeach; endif; unset($_from); ?>
                    <?php else: ?>
                        <?php echo $this->_tpl_vars['levels'][$this->_tpl_vars['datarow']['id']]; ?>

                    <?php endif; ?>
                </td>
                <td><?php echo $this->_tpl_vars['counts'][$this->_tpl_vars['datarow']['id']]; ?>
</td>
				<td>
					<a href="edit/<?php echo $this->_tpl_vars['datarow']['id']; ?>
" class="edit"><img src="/images/edit.png" alt="Edit" title="Edit" border="0" /></a>
                        <?php if (! $this->_tpl_vars['counts'][$this->_tpl_vars['id']]): ?>
                            <a href="delete/<?php echo $this->_tpl_vars['datarow']['id']; ?>
" onclick="return confirm('Weet u zeker dat u dit item wilt verwijderen?');"><img src="/images/delete.png" alt="Verwijderen" title="Verwijderen" border="0" /></a>
                        <?php endif; ?>
					<a href="levels/<?php echo $this->_tpl_vars['datarow']['id']; ?>
"><img src="/images/page_white_text.png" border="0" alt="Wijzig Niveaus" title="Wijzig Niveaus" /></a>
				</td>
			</tr>
			<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		</table>
	</div>
</form>