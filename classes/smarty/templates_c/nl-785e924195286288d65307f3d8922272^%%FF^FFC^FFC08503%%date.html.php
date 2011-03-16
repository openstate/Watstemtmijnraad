<?php /* Smarty version 2.6.18, created on 2010-12-23 15:40:22
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/date.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlspecialchars', '/var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/date.html', 13, false),)), $this); ?>
<h2>Wizard stap 0</h2>

<div class="wizard">
    
    <div class="content">
        <h3>Deze wizard stelt uw gemeenteraad opnieuw in.</h3>
        <p>Alle bestaande aanstellingen worden op de door u opgegeven datum stopgezet. Met deze wizard kunt u de situatie vanaf die datum instellen.</p>
        
        <form action="" class="date" method="post">
        <h4 style="margin-top:20px;">Ingangsdatum</h4>
        <div>
             
            <input class="text calendar" id="delivery_date" type="text" maxlength="20" readonly="" name="date" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['post']['date'])) ? $this->_run_mod_handler('htmlspecialchars', true, $_tmp) : htmlspecialchars($_tmp)); ?>
" />

			<?php if ($this->_tpl_vars['error']['date']): ?><div class="error">Dit veld is verplicht</div><?php endif; ?>
			<?php if ($this->_tpl_vars['error']['date_invalid']): ?><div class="error">De opgegeven datum is ongeldig</div><?php endif; ?>
            <button class="calendar" type="button" style="display: none;"> </button>
        </div>
        <p>Op deze datum stoppen alle bestaande aanstellingen en starten de door u ingestelde nieuwe aanstellingen</p>
            <label for="checkbox_step0"><input class="checkbox" type="checkbox" id="checkbox_step0" name="check" value="1" />Ik ga ermee akkoord dat op de bovengenoemde datum alle huidige aanstellingen stopgezet worden</label>
			<?php if ($this->_tpl_vars['error']['check']): ?><div class="error">U dient met het bovenstaande akkoord te gaan</div><?php endif; ?>
        <div class="buttons">

            <button class="next" type="submit">Start de wizard</button>
        </div>
        </form>
    </div>
</div>