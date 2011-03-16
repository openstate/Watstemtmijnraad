<?php /* Smarty version 2.6.18, created on 2011-01-19 16:50:49
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/wizard/content/style.html */ ?>
<h2>Wizard stap 3</h2>

<div class="wizard">
   	<ul class="steps">
        <li><a href="/wizard/party">1: partijen</a></li>
        <li><a href="/wizard/politician">2: politici</a></li>
        <li><a class="active" href="#">3: logo</a></li>
    </ul>
    
    <div class="content">
        <h3>Het logo van uw gemeente</h3>
        <p>Voor de herkenbaarheid van uw gemeente op onze site, vragen wij u om het logo van uw gemeente.</p>
        <p>U kunt het gemeentelogo ook later uploaden. U vind deze optie in het menu 'Opmaak'.</p>

		<h4 style="margin-top:20px;">Gemeente logo</h4>               
        <form action="" class="logo" enctype="multipart/form-data" method="post">
			<input type="file" name="logo" />
			<?php if ($this->_tpl_vars['error']['logo_invalid']): ?><div class="error">Dit bestand is ongeldig</div><?php endif; ?>
        
            <p>Deze bestandsformaten worden geaccepteerd: <strong>jpg, gif, png</strong></p>
            
            <div class="buttons">
                <button class="prev" name="prev" type="submit">Terug naar politici</button>         
                <button class="next" name="next" type="submit">Voltooien</button>
                <button class="next" name="next" type="submit">Deze stap overslaan</button>
            </div>
       	</form>
    </div>
</div>