<?php /* Smarty version 2.6.18, created on 2010-12-23 15:45:21
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/politician.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/politician.html', 44, false),array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/politician.html', 46, false),array('function', 'cycle', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/politician.html', 53, false),)), $this); ?>
<script type="text/javascript"><?php echo '
function toggle(el) {
	el = $(el);
	var row = el.getParent().getParent();
	var row2 = row.getNext();
	var img = el.getFirst();
	if (row.hasClass(\'hover\')) {
		row.removeClass(\'hover\');
		row.addClass(\'fold\');
		row2.style.display = \'\';
		img.src = img.src.replace(\'expand\', \'collapse\');
	} else {
		row.addClass(\'hover\');
		row.removeClass(\'fold\');
		row2.style.display = \'none\';
		img.src = img.src.replace(\'collapse\', \'expand\');
	}
	return false;
}
'; ?>
</script>
<h2>Wizard stap 2</h2>

<div class="wizard">
   	<ul class="steps">
        <li><a href="/wizard/party">1: partijen</a></li>
        <li><a class="active">2: politici</a></li>
        <li><a>3: logo</a></li>
    </ul>
    
    <div class="content">
        <h3>Welke politici zitten er in uw gemeenteraad?</h3>
		<form action="" method="post">
		<table class="list">
			<?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['p']):
?>
				<?php $this->assign('count', 0); ?>
				<?php ob_start(); ?>
					<tr class="strong"><td>Naam</td><td>Categorie</td><td>Aanvang</td><td>Einde</td><td width="15px">&nbsp;</td></tr>
					<?php $this->assign('last', ''); ?>
					<?php $_from = $this->_tpl_vars['appointments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['a']):
?>
						<?php if ($this->_tpl_vars['a']->party == $this->_tpl_vars['p']->party): ?>
							<?php $this->assign('count', $this->_tpl_vars['count']+1); ?>
							<?php $this->assign('id', $this->_tpl_vars['a']->politician); ?>
							<tr>
								<td><a href="/politicians/profile/<?php echo $this->_tpl_vars['politicians'][$this->_tpl_vars['id']]->id; ?>
"><?php $this->assign('name', ((is_array($_tmp=$this->_tpl_vars['politicians'][$this->_tpl_vars['id']]->formatName())) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp))); ?><?php if ($this->_tpl_vars['name'] != $this->_tpl_vars['last']): ?><?php echo $this->_tpl_vars['name']; ?>
<?php $this->assign('last', $this->_tpl_vars['name']); ?><?php else: ?>&#160;<?php endif; ?></a></td>
								<td><?php echo ((is_array($_tmp=$this->_tpl_vars['a']->cat_name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</td>
								<td><?php if ($this->_tpl_vars['a']->time_start == '-infinity'): ?>geen<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['a']->time_start)) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %B %Y') : smarty_modifier_date_format($_tmp, '%e %B %Y')); ?>
<?php endif; ?></td>
								<td><?php if ($this->_tpl_vars['a']->time_end == 'infinity'): ?>geen<?php else: ?><?php echo ((is_array($_tmp=$this->_tpl_vars['a']->time_end)) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %B %Y') : smarty_modifier_date_format($_tmp, '%e %B %Y')); ?>
<?php endif; ?></td>
								<td class="right"><a href="/appointments/delete/<?php echo $this->_tpl_vars['politicians'][$this->_tpl_vars['id']]->id; ?>
?localparty=<?php echo $this->_tpl_vars['p']->id; ?>
"><img src="/images/delete.png" alt="##form_delete##" title="##form_delete##" border="0" /></a></td>
							</tr>
						<?php endif; ?>
					<?php endforeach; endif; unset($_from); ?>
				<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('pols', ob_get_contents());ob_end_clean(); ?>
	        	<tr class="<?php if ($this->_tpl_vars['open'] == $this->_tpl_vars['p']->party): ?>fold<?php else: ?>hover<?php endif; ?><?php echo smarty_function_cycle(array('values' => ' alt,'), $this);?>
">
	        		<td width="15px"><a href="#" onclick="toggle(this);"><img src="/images/<?php if ($this->_tpl_vars['open'] == $this->_tpl_vars['p']->party): ?>collapse<?php else: ?>expand<?php endif; ?>.gif" width="16" height="16" /></a></td>
	        		<td><a href="#"><?php echo ((is_array($_tmp=$this->_tpl_vars['p']->party_name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
<?php if ($this->_tpl_vars['p']->party_short): ?> (<?php echo ((is_array($_tmp=$this->_tpl_vars['p']->party_short)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
)<?php endif; ?></a></td>
	        		<td class="right"><?php if ($this->_tpl_vars['count'] == 0): ?>nog geen<?php else: ?><?php echo $this->_tpl_vars['count']; ?>
<?php endif; ?> politic<?php if ($this->_tpl_vars['count'] == 1): ?>us<?php else: ?>i<?php endif; ?></td>
	        		<td width="120px" class="right"><a class="add" href="/wizard/newPolitician/?party=<?php echo $this->_tpl_vars['p']->party; ?>
">Toevoegen</a></td>
	        	</tr>
	        	<tr class="fold"<?php if ($this->_tpl_vars['open'] != $this->_tpl_vars['p']->party): ?> style="display:none;"<?php endif; ?>>
					<td colspan="4">
	                 	<table>
							<?php echo $this->_tpl_vars['pols']; ?>

								                    </table>
                 	</td>
	             </tr>
			 <?php endforeach; endif; unset($_from); ?>
        </table>       
        
        <div class="buttons">
        	<button class="prev" name="prev" type="submit">Terug naar partijen</button>
            <button class="next" name="next" type="submit">Volgende stap</button>
        </div>
		</form>
    </div>
</div>