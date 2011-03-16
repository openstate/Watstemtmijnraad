<?php /* Smarty version 2.6.18, created on 2010-12-10 00:47:31
         compiled from /var/www/projects/watstemtmijnraad_hg/templates/backoffice.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Watstemtmijnraad.nl Backoffice</title>
	<link rel="stylesheet" href="/stylesheets/main.css" type="text/css" />
    <link rel="stylesheet" href="/stylesheets/datepicker.css"/>
    
    <!--[if IE 6]>
		<link rel="stylesheet" type="text/css" href="/stylesheets/ie6.css" media="screen" />
	<![endif]-->
    
	<script src="/javascripts/mootools/moo.tools.v1.11.js" type="text/javascript"></script>
	<script src="/javascripts/ie.js" type="text/javascript"></script>
	<script src="/javascripts/formlib.js" type="text/javascript"></script>

	<script type="text/javascript" src="/javascripts/jquery.js"></script>
        <script src="/javascripts/datepicker.js" type="text/javascript"></script>
	<script type="text/javascript">
		jQuery.noConflict();
	</script>

	<?php if ($this->_tpl_vars['smartyData']['headerFile'] != ''): ?><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['smartyData']['headerFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><?php endif; ?>
</head>
<body>
    <div id="wrapper">
			<div id="header">
                <div class="login">
                    <?php if ($this->_tpl_vars['global']->user->loggedIn): ?><a href ="/files/Handleiding_backoffice_wsmr_V1.0_hstl.pdf"> handleiding </a> -<?php endif; ?>
                    <?php if ($this->_tpl_vars['global']->user->loggedIn): ?><a href="/logout/">logout</a><?php endif; ?>
                </div>
                <a href="/"><img src="/images/wsmr-logo-text.gif" height="27" width="248" class="logotext" alt="Wat stemt mijn raad?" /><img src="/images/wsmr-logo.png" height="44" width="44" class="logo" alt="Wat stemt mijn raad?"/></a>
                
                <?php if ($_SESSION['regionTitle']): ?>
                <div class="menu-select">
                    <h1><?php echo $_SESSION['regionTitle']; ?>

                    	<?php if ($this->_tpl_vars['global']->user->rights['selection']->access): ?><a href="/selection/">andere griffie selecteren</a><?php endif; ?>
                    </h1>
                </div>
                <?php endif; ?>
                
				<?php ob_start(); ?>
					<?php if (isset ( $_SESSION['role'] )): ?>
						<?php if ($this->_tpl_vars['global']->user->isSuperAdmin()): ?><li><a href="/party/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'party'): ?>class="current_item"<?php endif; ?>>Partijen</a></li><?php endif; ?>
						<?php if ($this->_tpl_vars['global']->user->isSuperAdmin()): ?><li><a href="/politicians/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'politicians'): ?>class="current_item"<?php endif; ?>>Politici</a></li><?php endif; ?>
                        <?php if ($this->_tpl_vars['global']->user->rights['categories']->access): ?><li><a href="/categories/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'categories'): ?>class="current_item"<?php endif; ?>>Beleidsvelden</a></li><?php endif; ?>
					<?php else: ?>
						<?php if ($this->_tpl_vars['global']->user->loggedIn): ?><li><a href="/selection/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'selection'): ?>class="current_item"<?php endif; ?>>Regio selectie</a></li><?php endif; ?>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['global']->user->rights['user']->access): ?><li><a href="/user/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'user'): ?>class="current_item"<?php endif; ?>>Gebruikers</a></li><?php endif; ?>
									<?php $this->_smarty_vars['capture']['admin_menu'] = ob_get_contents(); ob_end_clean(); ?>

				<?php if (! preg_match ( '/^\s+$/' , $this->_smarty_vars['capture']['admin_menu'] )): ?>
					<div class="admin-menu">
	                	<ul>
	                    	<li class="bg"><p>Admin navigatie:</p></li>
	                    	<?php echo $this->_smarty_vars['capture']['admin_menu']; ?>

	                    </ul>
	                </div>
                <?php endif; ?>

                <div class="menu">
                    <?php ob_start(); ?>
                        <?php if ($this->_tpl_vars['smartyData']['role']): ?>	
                        	<?php if ($this->_tpl_vars['smartyData']['role'] instanceof BOUserRoleClerk): ?>
                                <?php if ($this->_tpl_vars['global']->user->rights['raadsstukken']->access): ?><li><a href="/raadsstukken/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'raadsstukken'): ?>class="current_item"<?php endif; ?>>Raadsstukken</a></li><?php endif; ?>
                            <?php endif; ?>
                            <?php if ($this->_tpl_vars['global']->user->rights['appointments']->access): ?><li><a href="/appointments/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'appointments'): ?>class="current_item"<?php endif; ?>>De raad</a></li><?php endif; ?>
							<?php if ($this->_tpl_vars['global']->user->rights['style']->access): ?><li><a href="/style/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'style'): ?>class="current_item"<?php endif; ?>>Huisstijl</a></li><?php endif; ?>
							<?php if ($this->_tpl_vars['global']->user->rights['pages']->access): ?><li><a href="/pages/" <?php if ($this->_tpl_vars['global']->pageInfo['module'] == 'pages'): ?>class="current_item"<?php endif; ?>>Statische Pagina's</a></li><?php endif; ?>
                            <li><a href="/statistics/">Statistieken</a></li>
                            <li><a href="/forum">Forum</a></li>
                            <?php if ($_SESSION['regionHidden']): ?>
                            <li><a href="http://www.<?php echo $this->_tpl_vars['global']->domain; ?>
.<?php echo $this->_tpl_vars['global']->tld; ?>
/regions/region/<?php echo $_SESSION['regionID']; ?>
?preview=1">Preview</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php $this->_smarty_vars['capture']['menu'] = ob_get_contents(); ob_end_clean(); ?>
                    <?php if (! preg_match ( '/^\s+$/' , $this->_smarty_vars['capture']['menu'] )): ?>
                    <ul>
                        <?php echo $this->_smarty_vars['capture']['menu']; ?>

                    </ul>
                    <?php endif; ?>
                </div>
			</div>
            					
			<div id="content">
				<div class="leftColumn">

					<div class="block" style="zoom: 1;">
							<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['smartyData']['contentFile'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    </div>
                    
                </div>
            </div>
        </div>

<!-- 
  -->
</body>
</html>