<?php /* Smarty version 2.6.18, created on 2010-12-09 13:10:46
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/iframe/content/explanationPage.html */ ?>
<h1>Raadstukken op uw pagina</h1>

<?php if ($this->_tpl_vars['filter']['party'] == null && $this->_tpl_vars['filter']['politician'] == null): ?>
    <p>&nbsp;</p>
    <p>Om deze raadstukken op uw pagina te krijgen dient u onderstaande HTML code in uw HTML pagina te zetten:</p><br/>
    <pre>
        &lt;iframe src ="http://www.watstemtmijnraad.nl/iframe/region/<?php echo $this->_tpl_vars['filter']['region']; ?>
" width="600px" height="300px"&gt;
            &lt;p&gt;Uw browser ondersteund geen iframes&lt;/p&gt;
        &lt;/iframe&gt;
    </pre><br/>
    <p>U kunt de hoogte en breedte aanpassen door de eigenschappen 'width' en 'height' aan te passen zodat deze iframe in uw pagina past. Onderaan vind u een voorbeeld.</p>
    <p>Ook kunt u het kleuren schema zelf uit kiezen in 2 verschillende schema's, namelijk donker en licht. Voeg onderstaande toe om uw schema zelf uit te kiezen:</p>
    <ul>
        <li>&theme=light - Voor een licht schema</li>
        <li>&theme=dark - Voor een donker schema</li>
    </ul>
    <p>Ook kunt u het limiet van het aantal raadstukken aanpassen door '&limit=' toe te voegen aan het adres met daarachter het gewenste aantal. Bijvoorbeeld: '&limit=15' voor maximaal 15 raadstukken</p>
<?php endif; ?>

<?php if ($this->_tpl_vars['filter']['party']): ?>
    <p>&nbsp;</p>
    <p>Om deze raadstukken op uw pagina te krijgen dient u onderstaande HTML code in uw HTML pagina te zetten:</p><br/>
    <pre>
        &lt;iframe src ="http://www.watstemtmijnraad.nl/iframe/party/<?php echo $this->_tpl_vars['filter']['party']; ?>
<?php if ($this->_tpl_vars['filter']['region']): ?>?region=<?php echo $this->_tpl_vars['filter']['region']; ?>
<?php endif; ?>" width="600px" height="300px"&gt;
            &lt;p&gt;Uw browser ondersteund geen iframes&lt;/p&gt;
        &lt;/iframe&gt;
    </pre><br/>
    <p>U kunt de hoogte en breedte aanpassen door de eigenschappen 'width' en 'height' aan te passen zodat deze iframe in uw pagina past. Onderaan vind u een voorbeeld.</p>
    <p>Ook kunt u het kleuren schema zelf uit kiezen in 2 verschillende schema's, namelijk donker en licht. Voeg onderstaande toe om uw schema zelf uit te kiezen:</p>
    <ul>
        <li>&theme=light - Voor een licht schema</li>
        <li>&theme=dark - Voor een donker schema</li>
    </ul>
    <p>Ook kunt u het limiet van het aantal raadstukken aanpassen door '&limit=' toe te voegen aan het adres met daarachter het gewenste aantal. Bijvoorbeeld: '&limit=15' voor maximaal 15 raadstukken</p>
<?php endif; ?>

<?php if ($this->_tpl_vars['filter']['politician']): ?>
    <p>&nbsp;</p>
    <p>Om deze raadstukken op uw pagina te krijgen dient u onderstaande HTML code in uw HTML pagina te zetten:</p><br/>
    <pre>
        &lt;iframe src ="http://www.watstemtmijnraad.nl/iframe/politician/<?php echo $this->_tpl_vars['filter']['politician']; ?>
<?php if ($this->_tpl_vars['filter']['region']): ?>?region=<?php echo $this->_tpl_vars['filter']['region']; ?>
<?php endif; ?>" width="600px" height="300px"&gt;
            &lt;p&gt;Uw browser ondersteund geen iframes&lt;/p&gt;
        &lt;/iframe&gt;
    </pre><br/>
    <p>U kunt de hoogte en breedte aanpassen door de eigenschappen 'width' en 'height' aan te passen zodat deze iframe in uw pagina past. Onderaan vind u een voorbeeld.</p>
    <p>Ook kunt u het kleuren schema zelf uit kiezen in 2 verschillende schema's, namelijk donker en licht. Voeg onderstaande toe om uw schema zelf uit te kiezen:</p>
    <ul>
        <li>&theme=light - Voor een licht schema</li>
        <li>&theme=dark - Voor een donker schema</li>
    </ul>
    <p>Ook kunt u het limiet van het aantal raadstukken aanpassen door '&limit=' toe te voegen aan het adres met daarachter het gewenste aantal. Bijvoorbeeld: '&limit=15' voor maximaal 15 raadstukken</p>
<?php endif; ?>

<p>&nbsp;</p>