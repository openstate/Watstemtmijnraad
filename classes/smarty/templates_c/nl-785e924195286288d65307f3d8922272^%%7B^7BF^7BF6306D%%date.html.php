<?php /* Smarty version 2.6.18, created on 2010-12-23 15:40:22
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/header/date.html */ ?>
<script type="text/javascript">
<!--//--><![CDATA[//><!-- <?php echo '
window.addEvent(\'domready\', function() {
    /* Used in \'/bestelstatus/cart\' */
    if ($(\'delivery_date\') && $(\'delivery_date\').value == \'\') {
        $$(\'div.delivery_date\').setStyle(\'display\', \'none\'); // Hide by default, if no value pre-defined (posted)
    }

    // Adding a date picker with disabled weekends (0 - for Sunday and 6 for Saturday)
    deliveryDate = new Calendar(
        { delivery_date: \'d-m-Y\' },
        { blocked:  [\'0 * * 0,6\'],
          tweak : {x: 180, y: -150},
          days: [\'Zondag\', \'Mandag\', \'Dinsdag\', \'Woensdag\', \'Donderdag\', \'Vrijdag\', \'Zaterdag\'],
          months: [\'januari\', \'februari\', \'maart\', \'april\', \'mei\', \'juni\', \'juli\', \'augustus\', \'september\', \'oktober\', \'november\', \'december\']});
}); '; ?>

//--><!]]>
</script>