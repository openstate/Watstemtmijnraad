<?php /* Smarty version 2.6.18, created on 2010-12-09 12:43:40
         compiled from /var/www/projects/watstemtmijnraad_hg/templates/watstemtmijnraad.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'regex_replace', '/var/www/projects/watstemtmijnraad_hg/templates/watstemtmijnraad.html', 58, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/templates/watstemtmijnraad.html', 80, false),array('modifier', 'default', '/var/www/projects/watstemtmijnraad_hg/templates/watstemtmijnraad.html', 80, false),)), $this); ?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" type="image/png" href="/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="/stylesheets/libraries.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/stylesheets/css.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/stylesheets/grids.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/stylesheets/mod.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/stylesheets/base.css" media="screen" />
<?php if ($this->_tpl_vars['skin']): ?><link rel="stylesheet" type="text/css" href="/images/skins/custom/<?php echo $this->_tpl_vars['skin']; ?>
/skin.css" media="screen" /><?php endif; ?>

	<!--[if lte IE 7]>
		<link rel="stylesheet" type="text/css" href="/stylesheets/ie67.css" media="screen" />
		<?php if ($this->_tpl_vars['skin']): ?><link rel="stylesheet" type="text/css" href="/images/skins/custom/<?php echo $this->_tpl_vars['skin']; ?>
/skin_ie.css" media="screen" /><?php endif; ?>
	<![endif]-->
    
	<!--[if IE 6]>
		<link rel="stylesheet" type="text/css" href="/stylesheets/ie6.css" media="screen" />
	<![endif]-->

		<script src="/javascripts/govvid.js" type="text/javascript"></script>
		<script src="/javascripts/mootools/moo.tools.v1.11.js" type="text/javascript"></script>
		<script type="text/javascript" src="/javascripts/jquery.js"></script>
		<script type="text/javascript" src="/javascripts/main.js"></script>		<script type="text/javascript" src="/javascripts/Swiff.base.js"></script>
                <script type="text/javascript" src="/javascripts/autocomplete.js"></script>
		<link rel="icon" href="/favicon.ico" type="image/x-icon"/>
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"/>

		<?php if ($this->_tpl_vars['smartyData']['headerFile'] != ''): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['smartyData']['headerFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>

        
<title>Wat stemt mijn raad</title>

</head>
<body>

<div id="pushFooter">
    <div id="header">
        <div class="mod branding">
            <h1 class="groningen">
            	<a href="http://www.watstemtmijnraad.nl" class="wsmr">WSMR</a>
            	<?php if ($this->_tpl_vars['header']): ?>
            		<a href="/" class="wsmr-header"><?php echo $this->_tpl_vars['header']; ?>
</a>
            	<?php else: ?>	
            		<a href="/" class="wsmr-logo">Wat stemt mijn raad</a>
            	<?php endif; ?>
            
            </h1>
        </div>
    
        <div class="mod nav-breadcrumbs">
            <?php if ($this->_tpl_vars['crumbs']): ?>
                <ul>
                    <li class="root"><a href="http://<?php echo ((is_array($_tmp=$_SERVER['HTTP_HOST'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, '/^.*?\./', 'www.') : smarty_modifier_regex_replace($_tmp, '/^.*?\./', 'www.')); ?>
/">Wat stemt mijn raad</a></li>
                    <?php $_from = $this->_tpl_vars['crumbs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['crumbs'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['crumbs']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['page']):
        $this->_foreach['crumbs']['iteration']++;
?>
                        <?php if (($this->_foreach['crumbs']['iteration'] == $this->_foreach['crumbs']['total']) || $this->_tpl_vars['page']['title'] == 'Zoekresultaten'): ?>
                            <li><?php echo $this->_tpl_vars['page']['title']; ?>
</li>
                        <?php else: ?>
                            <li><a href="http://<?php echo ((is_array($_tmp=$_SERVER['HTTP_HOST'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, '/^.*?\./', 'www.') : smarty_modifier_regex_replace($_tmp, '/^.*?\./', 'www.')); ?>
<?php echo $this->_tpl_vars['page']['url']; ?>
"><?php echo $this->_tpl_vars['page']['title']; ?>
</a></li>
                        <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div id="body">
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['smartyData']['contentFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    </div>
</div>
<div id="footer">
	<div class="wrap">
    	<div class="col size1of4">
            <ul class="extra_links">
                <li class="first"><h4>Aanvullende informatie</h4></li>
                <?php $_from = $this->_tpl_vars['footer_pages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['page']):
?>
					<li><a href="http://<?php echo $this->_tpl_vars['global']->subdomain; ?>
.<?php echo $this->_tpl_vars['global']->domain; ?>
.<?php echo $this->_tpl_vars['global']->tld; ?>
/page/<?php echo $this->_tpl_vars['page']->region; ?>
/<?php echo $this->_tpl_vars['page']->url; ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['page']->title)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['page']->linkText)) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['page']->title) : smarty_modifier_default($_tmp, @$this->_tpl_vars['page']->title)))) ? $this->_run_mod_handler('default', true, $_tmp, @$this->_tpl_vars['page']->url) : smarty_modifier_default($_tmp, @$this->_tpl_vars['page']->url)))) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
</a></li>
				<?php endforeach; endif; unset($_from); ?>
            </ul>
        </div>
    	<div class="col size1of4">
            <ul class="extra_links">
                <li class="first"><h4>Overig</h4></li>
                <li><a href="/rss/"><img style="float:left;margin:3px 3px 0 0;" src="/images/rss_feed_icon.png" width="12" height="12" alt="RSS Feed" /> RSS feed</a></li>
                <li><a href="http://www.watstemtmijnraad.nl/page/2/widget">Hyves widget</a></li>

            </ul>
        </div>
        <div class="col size2of4">

           
            <div class="voting-behaviour">
                <h4>Wilt u stemuitslagen laten zien op uw hyves pagina - installeer dan <a href="http://www.watstemtmijnraad.nl/page/2/widget">deze</a> widget</h4>
            </div>
            
            <p style="margin-top:15px;">Wat Stemt Mijn Raad is (door) ontwikkeld door het ICTU-programma <a href="http://www.burgerlink.nl" target="_blank">Burgerlink</a>, in opdracht van het <a href="http://www.minbzk.nl" target="_blank">ministerie van Binnenlandse Zaken en Koninkrijksrelaties</a>.</p>
            
            <a class="drempelvrij" href="http://www.qualityhouse.nl/index.php?pageId=95" alt="www.watstemtmijnraad.nl drempelvrij">www.watstemtmijnraad.nl drempelvrij</a>
            
<!--            <a class="logo" href="http://www.burgerlink.nl" target="_blank"><img src="#" width="" height="" /></a>
-->
        </div>

    </div>
</div>
<script src="https://www.google-analytics.com/ga.js" type="text/javascript">
</script>
<script type="text/javascript">
  var pageTracker = _gat._getTracker("UA-5098830-1");
  pageTracker._setDomainName("<?php echo ((is_array($_tmp=$_SERVER['HTTP_HOST'])) ? $this->_run_mod_handler('regex_replace', true, $_tmp, '/^.*?\./', 'www.') : smarty_modifier_regex_replace($_tmp, '/^.*?\./', 'www.')); ?>
");
  pageTracker._trackPageview();
</script> 
</body>
</html>