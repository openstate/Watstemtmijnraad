<?php /* Smarty version 2.6.18, created on 2010-12-09 13:19:10
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/watstemtmijnraad/home/content/indexPage.html */ ?>

<?php echo '
<script>
jQuery(document).ready(function(){
	handleTextFields(jQuery(\'form#search_pol input\'));
	handleTextFields(jQuery(\'form#search_gem input\'));
});
</script>
'; ?>

<?php if (! $this->_tpl_vars['noflash']): ?>
<?php echo '
<SCRIPT LANGUAGE=JavaScript1.1>
<!--
var MM_contentVersion = 6;
var plugin = (navigator.mimeTypes && navigator.mimeTypes["application/x-shockwave-flash"]) ? navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin : 0;
if ( plugin ) {
		var words = navigator.plugins["Shockwave Flash"].description.split(" ");
	    for (var i = 0; i < words.length; ++i)
	    {
		if (isNaN(parseInt(words[i])))
		continue;
		var MM_PluginVersion = words[i];
	    }
	var MM_FlashCanPlay = MM_PluginVersion >= MM_contentVersion;
}
else if (navigator.userAgent && navigator.userAgent.indexOf("MSIE")>=0
   && (navigator.appVersion.indexOf("Win") != -1)) {
	document.write(\'<SCR\' + \'IPT LANGUAGE=VBScript\\> \\n\'); //FS hide this from IE4.5 Mac by splitting the tag
	document.write(\'on error resume next \\n\');
	document.write(\'MM_FlashCanPlay = ( IsObject(CreateObject("ShockwaveFlash.ShockwaveFlash." & MM_contentVersion)))\\n\');
	document.write(\'</SCR\' + \'IPT\\> \\n\');
}
if (!MM_FlashCanPlay) {
	window.location.replace("/?noflash=1");
}
//-->

</SCRIPT>

'; ?>

<?php endif; ?>

<div class="line">
	<div class="col size2of4">
		<div class="mod nav-mun">
			<h2>Kies een provincie</h2>
			<?php if ($this->_tpl_vars['noflash']): ?>
			<ul>
				<?php $_from = $this->_tpl_vars['provinces']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['id'] => $this->_tpl_vars['name']):
?>
				<li><a href="/regions/region/<?php echo $this->_tpl_vars['id']; ?>
"><?php echo $this->_tpl_vars['name']; ?>
</a></li>
				<?php endforeach; else: ?>
				<li>Er zijn geen provincies gevonden</li>
				<?php endif; unset($_from); ?>
			</ul>
            <img src="/images/nederland_kaart.gif" width="331" height="376" alt="Kaart van Nederland" />
			<?php else: ?>
			<object width="470" height="410">
	            <param name="movie" value="/flash/nederland_kaart.swf">
	            <embed src="/flash/nederland_kaart.swf" width="470" height="410">
	            </embed>
        	</object>
        	<?php endif; ?>
		</div>
	</div>
	<div class="col size2of4">
		<div class="mod wsmr-info">
			<!--<p>
				<?php echo $this->_tpl_vars['page']->content; ?>

			</p>-->
            <h2>Volg het stemgedrag van uw gemeenteraad</h2>
            <p>Maakt een partij haar standpunten waar als het op stemmen aankomt? Is een bepaald raadslid consequent in woord en (stem)daad? Check dit en nog veel meer zelf op deze site.</p>
			<div class="object-container">
            	<div class="moviecontent">
                  <div id="movie">
                    <a href="http://server.rijksoverheidsvideo.nl/mp4/ICTU-091209-1614.mp4"><img src="http://server.rijksoverheidsvideo.nl/foto/ICTU-091209-1614.jpg" alt="fragment uit video"  /></a>
                    <a href="/home/download?file=/library/subtitles/WSMR.srt" class="download-subs">Download ook de ondertiteling voor deze video</a>
                    <!-- This div is shown when no movie can be played or Javascript is turned off.
                            We use the image as a teaser for the movie -->
                  </div>
                  <script type="text/javascript">
	// <![CDATA[
	var movie = new GovVid("uniqueid", "466", "262");
	movie.addImage("http://server.rijksoverheidsvideo.nl/foto/ICTU-091209-1614.jpg"); // is used by flashplayer
	movie.addMovie("http://server.rijksoverheidsvideo.nl/mp4/ICTU-091209-1614.mp4");
	movie.addMovie("http://server.rijksoverheidsvideo.nl/wmv/ICTU-091209-1614.wmv");
	movie.addMovie("http://server.rijksoverheidsvideo.nl/3gp/ICTU-091209-1614.3gp");
	movie.addMovie("http://server.rijksoverheidsvideo.nl/flash/ICTU-091209-1614.flv"); //path calculated from flash file location, or url
	movie.addCaption("flv","/library/subtitles/WSMR.srt"); //caption for flv file
    movie.addCaption("mp4","/library/subtitles/WSMR.srt");
    movie.addCaption("3gp","/library/subtitles/WSMR.srt");
    movie.addCaption("wmv","/library/subtitles/WSMR.srt");
	//movie.addAudio("_external/audio/tandarts.mp3");
	movie.write("movie"); // writes movie to divID

	 // uncomment or delete if you use content-disposition on apache server, or if you use external movie files (like pilot video)
	 // this function can be placed in your init function if you use iis with asp (window.onload)
	 GovVid_openBinary(); // function to open your alternative movies in content-disposition.asp


	// ]]>
</script>
                </div>

            </div>
		</div>
		<div class="mod mun-search search">
			<h2>Ga direct naar uw gemeente</h2>
			<form method="get" action="/regions/searchRegion/" id="search_gem">
              	<div class="field">
                	<label for="terms"><span>Gemeente of plaats</span></label>
                    <input type="text" id="CityLocal" name="region" class="text" autocomplete="off" />
                    <input type="hidden" name="type" value="4" />                	</div>

				<button type="submit" id="submit" name="submit" value="Ga">Ga</button>
			</form>
		</div>

            <div class="mod pol-search search">
			<h2 class="pol-photo">Zoek een politicus</h2>
			<form method="get" action="/politicians/searchPolitician/" id="search_pol">
              	<div class="field">
      	        	<label for="terms"><span>Naam en eventueel plaats</span></label>
	                <input type="text" id="mod_terms" name="politician" autocomplete="off" class="text" />
                </div>

				<button type="submit" id="submit" name="submit" value="Zoek">Zoek</button>
			</form>
		</div>

	</div>

</div>

<!-- result windows -->
<div id="city_result" class="voteresults" style="z-index:100; display: none; position: absolute; width: 38.80em; overflow: hidden; background-color: #fff; color: #000; border: 1px solid #000;">
    <ul class="results_ul">

    </ul>
    <div class="more_results"><a href="#">Meer regio's...</a></div>
</div>
<div id="politician_result" class="voteresults" style="z-index:100; display: none; position: absolute; width: 38.80em; overflow: hidden; background-color: #fff; color: #000; border: 1px solid #000;">
    <ul class="results_ul">

    </ul>
    <div class="more_results"><a href="#">Meer politici...</a></div>
</div>


<div class="line">
	<div class="col size4of4">
		<div class="mod votings home-votings">
			<h2>D&aacute;t stemt de raad...</h2>
            <p class="white">Actuele raadsvoorstellen, moties, amendementen, burgerinitiatieven, initiatiefvoorstellen &eacute;n
raadsbesluiten.</p>
			<?php $_from = $this->_tpl_vars['recent']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['voting']):
?>
				<div class="voting">
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['smartyData']['contentDir'])."/../../raadsstukken/includable/voting.html", 'smarty_include_vars' => array('region' => 'test')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</div>
			<?php endforeach; endif; unset($_from); ?>
			<a href="/search/submit/Link" class="mod-more-big">Meer recente stemmingen</a>
		</div>
	</div>
</div>

<div id="debug"></div>