<?php /* Smarty version 2.6.18, created on 2011-01-19 09:31:40
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/search/header/indexPage.html */ ?>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
<?php echo '

window.addEvent(\'domready\', function() {
	var search = $(\'searchBlock\');
	var searchImage = $(\'searchImage\');

    $(\'searchImage\').setStyle(\'display\', \'\');

	search.open = function() {
		search.setStyle(\'display\', \'\');
		searchImage.setProperty(\'src\', \'/images/close.png\');
	}

	search.close = function() {
		search.setStyle(\'display\', \'none\');
		searchImage.setProperty(\'src\', \'/images/open.png\');
	}

	search.isOpen = function() {
		return search.getStyle(\'display\') != \'none\';
	}

	$(\'search_toggle\').addEvent(\'click\', function() {
		if (search.isOpen())
			search.close();
		else
			search.open();
	});

	search.close();
});

'; ?>

//--><!]]>
</script>