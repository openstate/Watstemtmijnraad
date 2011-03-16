<?php /* Smarty version 2.6.18, created on 2010-12-30 09:55:45
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/politicians/content/politician_profile.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', '/var/www/projects/watstemtmijnraad_hg/pages/admin/politicians/content/politician_profile.html', 27, false),)), $this); ?>
<h2><?php echo $this->_tpl_vars['politician']->formatName(); ?>
</h2>

<h3>Persoonsgegevens<a class="fontsize" href="/politicians/edit/<?php echo $this->_tpl_vars['politician']->id; ?>
">Wijzigen</a></h3>
<table style="margin-bottom:20px;">
    <tr><th>Naam</th><td><?php echo $this->_tpl_vars['politician']->formatName(false); ?>
</td></tr>
    <tr><th>Geslacht</th><td><?php if ($this->_tpl_vars['politician']->gender_is_male): ?>Man<?php else: ?>Vrouw<?php endif; ?></td></tr>
    <tr><th>Emailadres</th><td><?php echo $this->_tpl_vars['politician']->email; ?>
</td></tr>
    <tr><th>Extern ID</th><td><?php echo $this->_tpl_vars['politician']->extern_id; ?>
</td></tr>
</table>
<br clear="all" />
<h3>Aanstellingen van <?php echo $this->_tpl_vars['politician']->formatName(false); ?>
</h3>
<div>
    <a class="add" style="float:left; margin-right:5px;" href="/appointments/create/?politician=<?php echo $this->_tpl_vars['politician']->id; ?>
">Aanstelling toevoegen</a>
    </div>
<div class="pol-appointments">
    

	<?php $_from = $this->_tpl_vars['appointments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['appointment']):
?>
	<?php $this->assign('app_id', $this->_tpl_vars['appointment']->id); ?>
		<div class="pol-appointment">
            <h3><?php echo $this->_tpl_vars['appointment']->level_name; ?>
 <?php echo $this->_tpl_vars['appointment']->region_name; ?>
</h3>
	    	<p><?php echo $this->_tpl_vars['appointment']->party_name; ?>
<?php if ($this->_tpl_vars['app_parties'][$this->_tpl_vars['app_id']]->short_form): ?> (<?php echo $this->_tpl_vars['app_parties'][$this->_tpl_vars['app_id']]->short_form; ?>
)<?php endif; ?></p>
	        <p><strong><?php echo ((is_array($_tmp=$this->_tpl_vars['appointment']->time_start)) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %B %Y') : smarty_modifier_date_format($_tmp, '%e %B %Y')); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['appointment']->time_end)) ? $this->_run_mod_handler('date_format', true, $_tmp, '%e %B %Y') : smarty_modifier_date_format($_tmp, '%e %B %Y')); ?>
: </strong><?php echo $this->_tpl_vars['appointment']->cat_name; ?>
</p>
	        <?php if ($this->_tpl_vars['role'] == $this->_tpl_vars['appointment']->region): ?>
                <a style="margin-right:20px;" class="edit" href="/appointments/edit/<?php echo $this->_tpl_vars['appointment']->id; ?>
?localparty=<?php echo $this->_tpl_vars['appointment']->party; ?>
">Aanstelling wijzigen</a>
                <a class="remove" href="/appointments/delete/<?php echo $this->_tpl_vars['appointment']->id; ?>
?localparty=<?php echo $this->_tpl_vars['appointment']->party; ?>
">Aanstelling verwijderen</a>
            <?php endif; ?>
	    </div>
    <?php endforeach; else: ?>
    	Geen aanstellingen gevonden
    <?php endif; unset($_from); ?>
</div>

<div style="margin-top:5px;">
    <a class="add" style="float:left; margin-right:5px;" href="/appointments/create/?politician=<?php echo $this->_tpl_vars['politician']->id; ?>
">Aanstelling toevoegen</a>
</div>