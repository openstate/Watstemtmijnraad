<?php /* Smarty version 2.6.18, created on 2011-01-31 15:04:48
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/regions/header/createPage.html */ ?>
<?php echo '<script type="text/javascript">

var regions = new Array();
'; ?>
<?php $_from = $this->_tpl_vars['regions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['region']):
?>
regions[<?php echo $this->_tpl_vars['region']['id']; ?>
] = new Array();
regions[<?php echo $this->_tpl_vars['region']['id']; ?>
]['id'] = <?php echo $this->_tpl_vars['region']['level']; ?>
;
regions[<?php echo $this->_tpl_vars['region']['id']; ?>
]['name'] = '<?php echo $this->_tpl_vars['region']['name']; ?>
';
<?php endforeach; endif; unset($_from); ?><?php echo '

function setLevel(selected) {
		$(\'level\').value = regions[selected].id;
		$(\'level_name\').innerHTML = regions[selected].name;
}
window.addEvent(\'domready\', function() {
	setLevel($(\'parent\').value);
});
</script>'; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['headerDir'])."/createPageBase.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>