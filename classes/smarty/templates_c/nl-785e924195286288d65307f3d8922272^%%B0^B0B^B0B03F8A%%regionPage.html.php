<?php /* Smarty version 2.6.18, created on 2010-12-15 14:44:27
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/header/regionPage.html */ ?>
<?php echo '
<script type="text/javascript">
<!--//--><![CDATA[//><!--

function toggleParty(el) {
    jQuery(el).parents(\'.party_row\').toggleClass(\'open\');
    return true;
}

jQuery(document).ready(function() {
	jQuery(\'.atvie_toggle\').click(function() {
		jQuery(\'.pol_expired\', jQuery(this).parents(\'.party_row\')).addClass(\'pol_open\');
		jQuery(\'.expired_toggle\', jQuery(this).parent()).show()
		jQuery(this).hide();
	});
	
	jQuery(\'.expired_toggle\').click(function() {
		jQuery(\'.pol_expired\', jQuery(this).parents(\'.party_row\')).removeClass(\'pol_open\');
		jQuery(\'.atvie_toggle\', jQuery(this).parent()).show();
		jQuery(this).hide();
	});

	$$(\'tr.party_content\').addEvent(\'click\', function() {
		document.location.href = $E(\'a.edit\', this).href;
	});
});

//--><!]]>
</script>
'; ?>


<?php echo '
<style type="text/css">

.party_expired {
	display: none;
}

.party_row .party_content {
	display: none;
}

.party_row .cross_image {
	background-image: url(/images/expand.gif);
	background-position: center center;
	background-repeat: no-repeat;
	width: 20px;
}

.party_row.open tr.party_content {
	display: table-row;
    *display: block;
}

.party_row.open .cross_image {
	background-image: url(/images/collapse.gif);
}

.party_row .pol_expired {
	display: none;
}

.party_row.open tr.pol_expired {
	display: none;
}

.party_row.open tr.pol_expired.pol_open {
	display: table-row;
    *display: block;
}
</style>
'; ?>
