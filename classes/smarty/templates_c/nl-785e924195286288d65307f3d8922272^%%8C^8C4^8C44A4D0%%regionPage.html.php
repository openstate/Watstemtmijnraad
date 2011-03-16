<?php /* Smarty version 2.6.18, created on 2010-12-15 14:44:27
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/regionPage.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', '/var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/regionPage.html', 21, false),)), $this); ?>

<form action="" name="PartyList">
<h2>Aanstellingen <?php echo $this->_tpl_vars['region']->formatName(); ?>
</h2>
<div class="big_button">
    <a href="/appointments/addParty/?region=<?php echo $this->_tpl_vars['region']->id; ?>
">
    <img border="0" title="Toevoegen" alt="Toevoegen" src="/images/add.png"/>
    Voeg een partij toe
    </a>
</div>
<p>&nbsp;</p>
	<table class="list">
		<tr>
            <th>Lijst</th>
            <th width="230px">Naam</th>
            <th>Aantal politici</th>
            <th></th>
            <th></th>
            <th width="90px">Acties</th>
		</tr>
		
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'inc_regionPage.html') : smarty_modifier_cat($_tmp, 'inc_regionPage.html')), 'smarty_include_vars' => array('parties' => $this->_tpl_vars['parties']['current'],'class' => 'current')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
		<tr class="party_expired"><td colspan="6" style="text-align: center"><h3>Inactive partijen</h3></td></tr>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ((is_array($_tmp=$this->_tpl_vars['template_path'])) ? $this->_run_mod_handler('cat', true, $_tmp, 'inc_regionPage.html') : smarty_modifier_cat($_tmp, 'inc_regionPage.html')), 'smarty_include_vars' => array('parties' => $this->_tpl_vars['parties']['expired'],'class' => 'expired')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		
		<tr>
			<td colspan="6" style="text-align: center;">
				<a id="party_atvie_toggle" href="javascript: void(0);" onclick="jQuery('.party_expired, #party_expired_toggle').show(); jQuery(this).hide();">Toon inactive partijen</a>
				<a id="party_expired_toggle" href="javascript: void(0);" onclick="jQuery('.party_expired, #party_expired_toggle').hide(); jQuery('#party_atvie_toggle').show();" style="display: none;">Verberg inactive partijen</a>
			</td>
		</tr>
	</table>