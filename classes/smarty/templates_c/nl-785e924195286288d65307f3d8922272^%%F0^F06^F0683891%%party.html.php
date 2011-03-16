<?php /* Smarty version 2.6.18, created on 2010-12-23 15:41:17
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/party.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'in_array', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/party.html', 16, false),array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/party.html', 16, false),)), $this); ?>
<h2>Wizard stap 1</h2>

<div class="wizard">
   	<ul class="steps">
        <li><a class="active">1: partijen</a></li>
        <li><a>2: politici</a></li>
        <li><a>3: logo</a></li>
    </ul>
    
    <div class="content">
        <h3>Welkom bij <strong>Wat stemt mijn raad</strong></h3>
        <p>Welke politieke partijen zitten er in uw gemeenteraad?</p>
        
        <form action="" class="checkbox" method="post">
			<?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['p']):
?>
            <div class="checkbox"><label for="p<?php echo $this->_tpl_vars['p']->id; ?>
"><input type="checkbox" id="p<?php echo $this->_tpl_vars['p']->id; ?>
" name="party[]" value="<?php echo $this->_tpl_vars['p']->id; ?>
"<?php if (((is_array($_tmp=$this->_tpl_vars['p']->id)) ? $this->_run_mod_handler('in_array', true, $_tmp, $this->_tpl_vars['current']) : in_array($_tmp, $this->_tpl_vars['current']))): ?> checked="checked"<?php endif; ?> /><?php echo ((is_array($_tmp=$this->_tpl_vars['p']->name)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
<?php if ($this->_tpl_vars['p']->short_form): ?> (<?php echo ((is_array($_tmp=$this->_tpl_vars['p']->short_form)) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
)<?php endif; ?></label></div>
			<?php endforeach; endif; unset($_from); ?>

        <a class="add" href="/wizard/addParty">Andere partij toevoegen</a>
        
        <div class="buttons">
            <button class="next" type="submit">Volgende stap</button>
        </div>
        </form>

    </div>
</div>