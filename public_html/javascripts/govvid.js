/*
 * GovVid object, developed by the Dutch Ministry of Health, Welfare and Sport and 'Werkgroep Stijlgids' of the New Media Commission (CNM)
 * inspired on: 
 *    - swfobject (http://blog.deconcept.com/swfobject/)
 *    - GTObject (http://blog.deconcept.com/2005/01/26/web-standards-compliant-javascript-quicktime-detect-and-embed/)
 *
 * Authors:			Marc Gerritsen (m.gerritsen@minvws.nl) and Michael Wijnakker (m.wijnakker@minvws.nl)
 * Version:			0.8 (22-07-2008)
 * Works on:		Internet Explorer 6/7, Firefox 1.5/2/3, Safari 2/3, Opera 9
 * Description:		Can detect flash version and sees if Quicktime is installed
 *					Generates correct HTML for embedded movie and audio
 *					Can replace a HTML element with the movie/audio html
 *
 *
 * Usage:
 *     myMovie = new GovVid("id", width, height);
 *     myMovie.addMovie("filename"); // supports mov, mp4, wmv, flv, smil
 *     mymovie.addImage("filename"); // adds start image for flv player, only for movies
 *     for flv use path from flvplayer file
 *     myMovie.addCaption("flv","filename"); //adds caption file for flv player, only for movies
 *     myMovie.addAudio("filename"); //supports mp3, can display single mp3 file without movie, or add mp3 as audio description for flvmovie
 *     myMovie.write("htmlelementid"); // script puts movie html inside html element
 *
 * Changes:
 *  (5-11-2007)		- added movieObject.addFlashVars("usecaptions","false"); on line 149
 *  (11-12-2007)	- added addAudio function for adding mp3 files
 *  				- changed function names to prevent problems when using javascript frameworks like prototype.js 
 *  				- replaced Flvplayer for Mediaplayer from Jeroen Wijering (can also play single mp3 files)
 *  (22-07-2007)    - Changed innerHTML for correct DOM scripting. 
 *                  - added support for embedded smil (so you can use mp4/mov with captions in the quicktime player ) 
 */

 // change these filenames if you want to use different files
 // leave empty if you don't need asp files
var contentDispositionFile = "content-disposition.asp";
var statisticsFile = "statistics/statistics.asp";
var mediaplayerFile = "flash/mediaplayer.swf";
var govvidOpera = window.opera ? true : false;
var govvidIE = (document.all && document.getElementById && !govvidOpera) ? true : false;
var govvidSafari = (navigator.userAgent.toLowerCase().indexOf('safari') >= 0) ? true : false;
var debug = false;


var arFileTypes = new Array();
arFileTypes["jwmedia"]		= new Array("flv","mp3");
arFileTypes["quicktime"]	= new Array("smil","mp4", "mov","mp3");
arFileTypes["winmedia"]		= new Array("wmv","mp3","wma");



 //GovVid object
 GovVid = function(id, width, height) {
	this.id = id;
	this.width = width;
	this.height = height;
	this.movies = new Object();
	this.audioFiles = new Object();
	this.captions = new Object();
	this.image = "";
 }

//add movie function, adds movie to object, myMovie.addMovie("filename");
GovVid.prototype.addMovie = function(filename) {
   var temp = filename.split("/");
   var thefile = temp[temp.length - 1];
   var file = thefile.split(".");
   var type = file[file.length - 1];
   this.movies[type] = filename;
}

//add movie function, adds movie to object, myMovie.addMovie("filename");
GovVid.prototype.addAudio = function(filename) {
   var temp = filename.split("/");
   var thefile = temp[temp.length - 1];
   var file = thefile.split(".");
   var type = file[file.length - 1];
   this.audioFiles[type] = filename;
}


//adds image to object, myMovie.addImage("filename");
GovVid.prototype.addImage = function(filename) {
	this.image = filename;
}

//adds caption to object, myMovie.addCaption("flv","filename");
GovVid.prototype.addCaption = function(type, filename) {
	this.captions[type] = filename;
}

GovVid.prototype.inArray = function (needleArray,haystackArray) {
	var key;
	var key2;
	for(key in haystackArray)
		for(key2 in needleArray)
			if(key == needleArray[key2])
				return key;
	return false;
}

//writes correct movie to html element
GovVid.prototype.write = function(elementid, movietype) {

		movietype = typeof(movietype) != 'undefined' ? movietype : "choose";
	    //get element with id
        var element = document.getElementById(elementid);
		if (element && movietype == "choose") {
			//set with to parentnode - TODO: maybe only on li elements
			element.parentNode.style.width = parseInt(this.width) + "px";
		    GovVid_makeListToggle(elementid);
		} else if (movietype != "choose") {
			//delete vieuwCaptions element if exists
			var viewCap = document.getElementById('govvid_viewwithcaptions');
			if(viewCap) viewCap.parentNode.removeChild(viewCap);
		}

		var display = false;
		var version = GovVid_getFlashPlayerVersion();
		var height;
		var testarray = new Array();
	
		if(version.major > 7 && (movietype == "choose" || GovVid_in_array(arFileTypes["jwmedia"],movietype))) {
			if(mediaplayerFile == undefined || mediaplayerFile == "") {
				alert("voor het afspelen met de flash plugin, is de jwmediaplayer vereist.\n vul de variable mediaplayerFile met de juiste waarde.");
				return false;
			}
			height = parseInt(this.height) + 20;
			if(display = this.inArray(arFileTypes["jwmedia"],this.movies)) {
				
				var movieObject = new GovVidMovie(mediaplayerFile, this.id, this.width, height);
				movieObject.addFlashVars("autostart", "false");
			 	// set image if there is a image defined
			 	if(this.image != "") movieObject.addFlashVars("image", this.image); 
			 	// set caption if there is caption
			 	if (this.captions["flv"] != undefined) movieObject.addFlashVars("captions", this.captions["flv"]);
				if (this.audioFiles["mp3"] != undefined) movieObject.addFlashVars("audio", this.audioFiles["mp3"]);
				
				movieObject.addFlashVars("usecaptions","false");
             	movieObject.addFlashVars("overstretch","false");
				movieObject.addFlashVars("file", this.movies[display]); //path from flash file location
				
				if(statisticsFile != undefined && statisticsFile != "") {
			    	movieObject.addFlashVars("callback",statisticsFile);
			   		movieObject.addFlashVars("logevents",statisticsFile);
			 	}
			 
			 	movieObject.addFlashVars("showdigits","total");
		     	movieObject.addFlashVars("showbuttons","true");
				movieObject.addFlashVars("showstop","true");				
			 	movieObject.addFlashVars("bufferlength","5");
			 	movieObject.addFlashVars("backcolor","0x000000");
			 	movieObject.addFlashVars("frontcolor","0xFFFFFF");
			 	movieObject.addFlashVars("lightcolor","0xFFFFFF");
			 	movieObject.showMovie(elementid,"flash");
				//element.innerHTML = movieObject.getFlashHTML();
				return true;
			}
			else if(display = this.inArray(arFileTypes["jwmedia"],this.audioFiles)) {
				var audioObject = new GovVidMovie(mediaplayerFile, this.id, this.width, height);
				audioObject.addFlashVars("file", this.audioFiles[display]); //path from flash file location

				if(statisticsFile != undefined && statisticsFile != "") {
			    	audioObject.addFlashVars("callback",statisticsFile);
			    	audioObject.addFlashVars("logevents",statisticsFile);
			 	}
			 
			 	audioObject.addFlashVars("showdigits","total");
		     	audioObject.addFlashVars("showbuttons","true");
				audioObject.addFlashVars("showstop","true");				
			 	audioObject.addFlashVars("bufferlength","5");
			 	audioObject.addFlashVars("backcolor","0x000000");
			 	audioObject.addFlashVars("frontcolor","0xFFFFFF");
			 	audioObject.addFlashVars("lightcolor","0xFFFFFF");
			 	audioObject.showMovie(elementid,"flash");
				return true;
			}
		}
		if(!GovVid_isQTInstalled() && GovVid_in_array(arFileTypes["quicktime"],movietype)) alert("Geen instalatie van quicktime gevonden.");
		if (GovVid_isQTInstalled() && !display && (movietype == "choose" || GovVid_in_array(arFileTypes["quicktime"],movietype))) {
			// Toon quicktime
			height = parseInt(this.height) + 16;
			if(movietype != "choose") display = movietype;
			else display = this.inArray(arFileTypes["quicktime"],this.movies); 
			if(display) {
				if(display == "smil") height = parseInt(this.height) + 115;
				var movieObject = new GovVidMovie(this.movies[display], this.id, this.width, height);
			}
			else if(display = this.inArray(arFileTypes["quicktime"],this.audioFiles)) {
				var movieObject = new GovVidMovie(this.audioFiles[display], this.id, this.width, height);
			}
			
			if (movieObject) {
				movieObject.showMovie(elementid, "quicktime");
				return true
			}
		} 
		if (!display && (movietype == "choose" || GovVid_in_array(arFileTypes["winmedia"],movietype))) {
			// Toon windows media player
			height = parseInt(this.height) + 46;
			if(display = this.inArray(arFileTypes["winmedia"],this.movies)) {
				var movieObject = new GovVidMovie(this.movies[display], this.id, this.width, height);
			}
			else if(display = this.inArray(arFileTypes["winmedia"],this.audioFiles)) {
				var movieObject = new GovVidMovie(this.audioFiles[display], this.id, this.width, height);
			}
			if (movieObject) {
				movieObject.showMovie(elementid, "winmedia");
				
				if(this.movies["asx"]) {
					//add link
					GovVid_addLinkToList(element, this.movies["asx"], "Bekijk deze video met ondertiteling");
				}
				
				return true;
			}
		}

		return false;
  
}

// movie object and functions
GovVidMovie = function(filename, id, width, height) {
	this.filename = filename;
	this.id = id;
	this.width = width;
	this.height = height;
	this.flashvars = new Object();
};

// function to add flash variables to object
GovVidMovie.prototype.addFlashVars = function(name, value) {
	this.flashvars[name] = value;
}

// function that is used by other functions
GovVidMovie.prototype.getFlashVars = function() {
    return this.flashvars;
}


/*
 * HTML generator functions
 *  
 *
 */

// adds a param tag
GovVidMovie.prototype.insertParamTag = function(objEl, strName, strValue) {
	var objParam = document.createElement("PARAM");
	objParam.setAttribute('name', strName);
	objParam.setAttribute('value', strValue);
	objEl.appendChild(objParam);
}

 // function that generates quicktime html
GovVidMovie.prototype.getQuicktimeHTML = function() {

	// IE cannot handle adding type attribute with setAttribute
	if (govvidIE) {
		var objObject = document.createElement("<object type='video/quicktime' classid='clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B' />");
	}
	else {
		var objObject = document.createElement("object");
		objObject.setAttribute('type', 'video/quicktime');
	}

	objObject.setAttribute('id', this.id);
	objObject.setAttribute('width', this.width);
	objObject.setAttribute('height', this.height);

	if(govvidIE || govvidSafari)
		this.insertParamTag(objObject, 'src', this.filename);
	else
		objObject.setAttribute('data', this.filename);

	this.insertParamTag(objObject, 'autoplay', 'false');

	return objObject;
}

 // function that generates windows media player html
GovVidMovie.prototype.getWinmediaHTML = function() {

	// IE cannot handle adding type attribute with setAttribute
	if(govvidIE)
		objObject = document.createElement("<object type='video/x-ms-wmv' />");
	else {
		var objObject = document.createElement("object");
		objObject.setAttribute('type', 'video/x-ms-wmv');
	}

	objObject.setAttribute('id', this.id);
	objObject.setAttribute('width', this.width);
	objObject.setAttribute('height', this.height);

	if(govvidIE || govvidSafari || govvidOpera)
		this.insertParamTag(objObject, 'src', this.filename);
	else
		objObject.setAttribute('data', this.filename);

	this.insertParamTag(objObject, 'autostart', '0');

	return objObject;
}

 // function that generates flash html, different for ie or navigator browsers
GovVidMovie.prototype.getFlashHTML = function() {
	var flashVars = "";

	// do flashvars
	if(this.flashvars)
		for (var param in this.getFlashVars())
			flashVars += param + '=' + this.flashvars[param] + '&';

	// IE cannot handle adding type attribute with setAttribute
	if(govvidIE)
		var objObject = document.createElement("<object type='application/x-shockwave-flash' />");
	else {
		var objObject = document.createElement("object");
		objObject.setAttribute('type', 'application/x-shockwave-flash');
	}
	objObject.setAttribute('id', this.id);
	objObject.setAttribute('width', this.width);
	objObject.setAttribute('height', this.height);

	if(govvidIE || govvidSafari)
		this.insertParamTag(objObject, 'src', this.filename);
	else
		objObject.setAttribute('data', this.filename);

	this.insertParamTag(objObject, 'flashvars', flashVars);
	this.insertParamTag(objObject, 'allowfullscreen', 'true');

	return objObject;
}

// replace element with movie html, the get+movie+HTML functions
GovVidMovie.prototype.showMovie = function(elementID, filetype) {
	var element = document.getElementById(elementID);
	if(filetype == 'flash') {
		element = GovVid_EmptyNode(element);
		element.appendChild(this.getFlashHTML());
	} else if (filetype == 'quicktime') {
		element = GovVid_EmptyNode(element);
		element.appendChild(this.getQuicktimeHTML());
	} else if (filetype == 'winmedia') {
		element = GovVid_EmptyNode(element);
		element.appendChild(this.getWinmediaHTML());
	}
}

/*
 * Detector functions
 *  
 *
 */

// Function gets flashplayer version, if flash is not installed it returns major:0 minor:0 revision:0
function GovVid_getFlashPlayerVersion(){

	var version = new FlashPlayerVersion([0,0,0]);

	if(navigator.plugins && navigator.mimeTypes.length){
		var x=navigator.plugins["Shockwave Flash"];
		if(x && x.description)
			version = new FlashPlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));
	} else {
		try {
			var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
		} catch(e) {
			try {
				var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
				version = new FlashPlayerVersion([6,0,21]);
				axo.AllowScriptAccess="always";
			} catch(e){
				if(version.major==6){return version;}
			}

			try {
				axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
			} catch(e){}
		}

		if(axo!=null)
			version=new FlashPlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));
	}

   return version;
};
// object used by the getFlashPlayerVersion function
FlashPlayerVersion = function(player){
	this.major = player[0]!=null?parseInt(player[0]):0;
	this.minor = player[1]!=null?parseInt(player[1]):0;
	this.rev   = player[2]!=null?parseInt(player[2]):0;
};

// function checks if quicktime is installed
function GovVid_isQTInstalled() {
	var qtInstalled = false;
	var qtObj       = false;

	if (navigator.plugins && navigator.plugins.length) {
		var navigatorLength = navigator.plugins.length;
		for (var i=0; i < navigatorLength; i++ ) {
			var plugin = navigator.plugins[i];
			if (plugin.name.indexOf("QuickTime") > -1)
				qtInstalled = true;
		}
	} else {
	
		if (window.ActiveXObject) {
			var control = null;
			try {
				control = new ActiveXObject('QuickTime.QuickTime');
			} catch (e) {
			// Do nothing
			}
			if (control)
				// In case QuickTimeCheckObject.QuickTimeCheck does not exist
				qtInstalled = true;
		}
		else
			alert("no active X");
	}
	return qtInstalled;
}


/*
 * General functions / html functions
 *  
 *
 */

 // function hides or shows an element with stylesheet classes
function GovVid_toggle(objid) {
	var el = document.getElementById(objid);
	if ( el.className == 'close' )
		el.className = 'open';
	else
		el.className = 'close';
}

 // function hides or shows an element with stylesheet classes
function GovVid_toggle_both(objid) {
	var strMenu = objid.replace(/ToggleLink/,"ToggleMenu");
	var strLink = objid.replace(/ToggleMenu/,"ToggleLink");

	GovVid_toggle(strMenu);
	GovVid_toggle(strLink);
}

// removes all childnodes from a node
function GovVid_EmptyNode(objNode) {
	for(var i = objNode.childNodes.length-1; i >= 0; i--)
		objNode.removeChild(objNode.childNodes[i]);
	return objNode;
}

// do stuff with links to binary files (not needed for pilot video files)
function GovVid_openBinary() {

	if(contentDispositionFile != undefined && contentDispositionFile != "") {
		var filetypes = new Array("pdf", "wmv", "mov", "mp4", "flv", "3gp","mp3", "srt");
		var myAnchors = document.getElementsByTagName('a');
		var myAnchorsLength = myAnchors.length;

		var baseUrl = new String(document.location);
		var splitUrl = baseUrl.split("/");
		var strBase = "";
		if(baseUrl.indexOf("http://") == 0 && splitUrl.length > 2)
			strBase = "http://" + splitUrl[2];

		for (i=0; i<myAnchorsLength ;i++)  {
			var anchor = myAnchors[i];
			var url = String(anchor.getAttribute('href'));
			var arUrl = url.split(".");
			var filetype = arUrl[arUrl.length-1];

			if(GovVid_in_array(filetypes, filetype.toLowerCase())) {
				var myfile = anchor.href.substr(strBase.length);
				anchor.href = contentDispositionFile + "?File=" + myfile; 
				anchor.onclick = function () {
					this.href = this.href;
				}
			} else if (anchor.className == "transcription") {
				anchor.onclick = function () {
					return newWin(this.href,'500','500');
				}
			}
		}
	}
}


function GovVid_addLinkToList(element, strUrl, strLinkText){

	if (element) {
		var parent = GovVid_getParentByClassname("moviecontent", element);
		if (!parent) 
			parent = GovVid_getParentByClassname("audiocontent", element);
		if (parent) {
			var elements = parent.getElementsByTagName("UL");
			
			if (strUrl) {
				var objLi = document.createElement("LI");
				objLi.setAttribute('id','govvid_viewwithcaptions');
				var objA = document.createElement("A");
				objA.setAttribute('href',strUrl);
				var objT = document.createTextNode(strLinkText);
				objA.appendChild(objT);
				objLi.appendChild(objA);
				
				var elementsLI = elements[0].getElementsByTagName("LI");
				elements[0].insertBefore(objLi,elementsLI[0]);
			}
		}
	}
}
// toggle list 
function GovVid_makeListToggle(liID) {		
  var element = document.getElementById(liID);

   if(element) {
	   var parent = GovVid_getParentByClassname("moviecontent", element);
	   if(!parent) parent = GovVid_getParentByClassname("audiocontent", element);
	   if(parent) 
	     var elements = GovVid_getElementsByClassName("toggle","LI",parent);
	   else
	     var elements = GovVid_getElementsByClassName("toggle","LI");
	   //loop elements
	   var elementsLength = elements.length;
	   for(var i = 0; i < elementsLength; i++) {
		    var ulID = "govVid_ToggleMenu_" + i + liID;	
		    var toggleEl = "";
		    var header  = "";
            
			var childNodesLength = elements[i].childNodes.length;
			for (var j = 0; j < childNodesLength ; j++ ){
				 var tagName = elements[i].childNodes[j].tagName;

				 if ((tagName == "H2" || tagName == "H3" || tagName == "H4" || tagName == "H5" || tagName == "H6" || tagName == "SPAN" || tagName == "A") && header == "") {
					 header = elements[i].childNodes[j];
					 var headerID = "govVid_ToggleLink_" + i +  liID;

					 // add link to download video header
					 if(tagName == "A") {
						header.id = headerID;
					    header.onclick = function() {
							GovVid_toggle_both(this.id);
							return false;
						}
					 } else {
						var objA = document.createElement("A");
						objA.setAttribute('href','#');
						objA.setAttribute('id',headerID);
						header.appendChild(objA);
						objA.appendChild(header.firstChild)
						objA.onclick = function() {
							GovVid_toggle_both(this.id);
							return false;
						}
					 }
					 GovVid_toggle(headerID);
				 } 
				 if((tagName == "UL" || tagName == "DIV" || tagName == "P") && toggleEl == "") {
					 toggleEl = elements[i].childNodes[j];
					 toggleEl.id = ulID;
					 GovVid_toggle(ulID);
				 }
			}
	   }
   }
   else alert("id '" + liID + "'not found!");
}

// funtion to check if a value is in a array, returns true or false
function GovVid_in_array(myArray, value) {
     var myArrayLength = myArray.length;
     for(var i=0; i < myArrayLength; i++)
           if(myArray[i] == value)
			   return true;
	 return false;
}

function GovVid_getElementsByClassName(className, tag, elm){
	var testClass = new RegExp("(^|\\s)" + className + "(\\s|$)");
	var tag = tag || "*";
	var elm = elm || document;
	var elements = (tag == "*" && elm.all)? elm.all : elm.getElementsByTagName(tag);
	var returnElements = [];
	var current;
	var length = elements.length;
	for(var i=0; i<length; i++){
		current = elements[i];
		if(testClass.test(current.className))
			returnElements.push(current);
	}
	return returnElements;
}

function GovVid_getParentByClassname(className, elm) {
	var testClass = new RegExp("(^|\\s)" + className + "(\\s|$)");
	
	while(elm.tagName != "BODY") {
		if(testClass.test(elm.className))
			return elm;
		elm = elm.parentNode;
	}
	return false;
}


// flvplayer functions
	var currentPosition;
	var currentVolume;

	function loadFile(caps) { thisMovie("mediaplayer").loadFile(fil); };

	function thisMovie(movieName) {
	    if(navigator.appName.indexOf("Microsoft") != -1) {
			return window[movieName];
		} else {
			return document[movieName];
		}
	};

	function sendEvent(typ,prm) { 
		thisMovie("mediaplayer").sendEvent(typ,prm); 
	};

	function getUpdate(typ,pr1,pr2) {
		if(typ == "time") { currentPosition = pr1; }
		else if(typ == "volume") { currentVolume = pr1; }
	};