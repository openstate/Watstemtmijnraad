/*
 * Inline Form Validation Engine, jQuery plugin
 * 
 * Copyright(c) 2009, Cedric Dugas
 * http://www.position-relative.net
 *	
 * Form validation engine witch allow custom regex rules to be added.
 * Licenced under the MIT Licence
 */

jQuery(document).ready(function() {

	// SUCCESS AJAX CALL, replace "success: false," by:     success : function() { callSuccessFunction() }, 
	jQuery("[class^=validate]").validationEngine({
		success :  false,
		failure : function() {}
	})
});

jQuery.fn.validationEngine = function(settings) {
	if(jQuery.validationEngineLanguage){					// IS THERE A LANGUAGE LOCALISATION ?
		allRules = jQuery.validationEngineLanguage.allRules
	}else{
		allRules = {"required":{    			  // Add your regex rules here, you can take telephone as an example
							"regex":"none",
							"alertText":"* Dit is een verplicht veld",
							"alertTextCheckboxMultiple":"* Please select an option",
							"alertTextCheckboxe":"* This checkbox is required"},
						"length":{
							"regex":"none",
							"alertText":"* Tussen de ",
							"alertText2":" en ",
							"alertText3": " karakters toegestaan"},
						"minCheckbox":{
							"regex":"none",
							"alertText":"* Checks allowed Exceeded"},	
						"confirm":{
							"regex":"none",
							"alertText":"* Your field is not matching"},		
						"telephone":{
							"regex":"/^[0-9\-\(\)\ ]+$/",
							"alertText":"* Invalid phone number"},	
						"email":{
							"regex":"/^[a-zA-Z0-9_\.\-]+\@([a-zA-Z0-9\-]+\.)+[a-zA-Z0-9]{2,4}$/",
							"alertText":"* Invalid email address"},	
						"date":{
                             "regex":"/^[0-9]{4}\-\[0-9]{1,2}\-\[0-9]{1,2}$/",
                             "alertText":"* Invalid date, must be in YYYY-MM-DD format"},
						"onlyNumber":{
							"regex":"/^[0-9\ ]+$/",
							"alertText":"* Numbers only"},	
						"noSpecialCaracters":{
							"regex":"/^[0-9a-zA-Z]+$/",
							"alertText":"* No special caracters allowed"},	
						"onlyLetter":{
							"regex":"/^[a-zA-Z\ \']+$/",
							"alertText":"* Letters only"}
					}	
	}

 	settings = jQuery.extend({
		allrules:allRules,
		inlineValidation: true,
		success : false,
		failure : function() {}
	}, settings);	

 	jQuery("form").bind("submit", function(caller){   // ON FORM SUBMIT, CONTROL AJAX FUNCTION IF SPECIFIED ON DOCUMENT READY
		if(submitValidation(this) == false){
			if (settings.success){
				settings.success && settings.success(); 
				return false;
			}
		}else{
			settings.failure && settings.failure(); 
			return false;
		}
	})
	if(settings.inlineValidation == true){ 		// Validating Inline ?
		
		jQuery(this).not("[type=checkbox]").bind("blur", function(caller){loadValidation(this)})
		jQuery(this+"[type=checkbox]").bind("click", function(caller){loadValidation(this)})
	}
	var buildPrompt = function(caller,promptText,showTriangle) {			// ERROR PROMPT CREATION AND DISPLAY WHEN AN ERROR OCCUR
		var divFormError = document.createElement('div')
		var formErrorContent = document.createElement('div')
		var arrow = document.createElement('div')
		
		
		jQuery(divFormError).addClass("formError")
		jQuery(divFormError).addClass(jQuery(caller).attr("id"))
		jQuery(formErrorContent).addClass("formErrorContent")
		jQuery(arrow).addClass("formErrorArrow")

		jQuery("body").append(divFormError)
		jQuery(divFormError).append(arrow)
		jQuery(divFormError).append(formErrorContent)
		
		if(showTriangle == true){		// NO TRIANGLE ON MAX CHECKBOX AND RADIO
			jQuery(arrow).html('<div class="line10"></div><div class="line9"></div><div class="line8"></div><div class="line7"></div><div class="line6"></div><div class="line5"></div><div class="line4"></div><div class="line3"></div><div class="line2"></div><div class="line1"></div>');
		}
		jQuery(formErrorContent).html(promptText)
	
		callerTopPosition = jQuery(caller).offset().top;
		callerleftPosition = jQuery(caller).offset().left;
		callerWidth =  jQuery(caller).width()
		callerHeight =  jQuery(caller).height()
		inputHeight = jQuery(divFormError).height()

		callerleftPosition = callerleftPosition + callerWidth -30
		callerTopPosition = callerTopPosition  -inputHeight -10
	
		jQuery(divFormError).css({
			top:callerTopPosition,
			left:callerleftPosition,
			opacity:0
		})
		jQuery(divFormError).fadeTo("fast",0.8);
	};
	var updatePromptText = function(caller,promptText) {	// UPDATE TEXT ERROR IF AN ERROR IS ALREADY DISPLAYED
		updateThisPrompt =  jQuery(caller).attr("id")
		jQuery("."+updateThisPrompt).find(".formErrorContent").html(promptText)
		
		callerTopPosition  = jQuery(caller).offset().top;
		inputHeight = jQuery("."+updateThisPrompt).height()
		
		callerTopPosition = callerTopPosition  -inputHeight -10
		jQuery("."+updateThisPrompt).animate({
			top:callerTopPosition
		});
	}
	var loadValidation = function(caller) {		// GET VALIDATIONS TO BE EXECUTED
		
		rulesParsing = jQuery(caller).attr('class');
		rulesRegExp = /\[(.*)\]/;
		getRules = rulesRegExp.exec(rulesParsing);
		str = getRules[1]
		pattern = /\W+/;
		result= str.split(pattern);	
		
		var validateCalll = validateCall(caller,result)
		return validateCalll
		
	};
	var validateCall = function(caller,rules) {	// EXECUTE VALIDATION REQUIRED BY THE USER FOR THIS FIELD
		var promptText =""	
		var prompt = jQuery(caller).attr("id");
		var caller = caller;
		var callerName = jQuery(caller).attr("name");
		isError = false;
		callerType = jQuery(caller).attr("type");
		
		for (i=0; i<rules.length;i++){
			switch (rules[i]){
			case "optional": 
				if(!jQuery(caller).val()){
					closePrompt(caller)
					return isError
				}
			break;
			case "required": 
				_required(caller,rules);
			break;
			case "custom": 
				 _customRegex(caller,rules,i);
			break;
			case "length": 
				 _length(caller,rules,i);
			break;
			case "minCheckbox": 
				 _minCheckbox(caller,rules,i);
			break;
			case "confirm": 
				 _confirm(caller,rules,i);
			break;
			default :;
			};
		};
		if (isError == true){
			var showTriangle = true
			if(jQuery("input[name="+callerName+"]").size()> 1 && callerType == "radio") {		// Hack for radio group button, the validation go the first radio
				caller = jQuery("input[name="+callerName+"]:first")
				showTriangle = false
				var callerId ="."+ jQuery(caller).attr("id")
				if(jQuery(callerId).size()==0){ isError = true }else{ isError = false}
			}
			if(jQuery("input[name="+callerName+"]").size()> 1 && callerType == "checkbox") {		// Hack for radio group button, the validation go the first radio
				caller = jQuery("input[name="+callerName+"]:first")
				showTriangle = false
				var callerId ="div."+ jQuery(caller).attr("id")
				if(jQuery(callerId).size()==0){ isError = true }else{ isError = false}
			}
			if (isError == true){ // show only one
				(jQuery("div."+prompt).size() ==0) ? buildPrompt(caller,promptText,showTriangle)	: updatePromptText(caller,promptText)
			}
		}else{
			if(jQuery("input[name="+callerName+"]").size()> 1 && callerType == "radio") {		// Hack for radio group button, the validation go the first radio
				caller = jQuery("input[name="+callerName+"]:first")
			}
			if(jQuery("input[name="+callerName+"]").size()> 1 && callerType == "checkbox") {		// Hack for radio group button, the validation go the first radio
				caller = jQuery("input[name="+callerName+"]:first")
			}
			closePrompt(caller)
		}		
		
		/* VALIDATION FUNCTIONS */
		function _required(caller,rules){   // VALIDATE BLANK FIELD
			callerType = jQuery(caller).attr("type")
			
			if (callerType == "text" || callerType == "password" || callerType == "textarea"){
				
				if(!jQuery(caller).val()){
					isError = true
					promptText += settings.allrules[rules[i]].alertText+"<br />"
				}	
			}
			if (callerType == "radio" || callerType == "checkbox" ){
				callerName = jQuery(caller).attr("name")
		
				if(jQuery("input[name="+callerName+"]:checked").size() == 0) {
					isError = true
					if(jQuery("input[name="+callerName+"]").size() ==1) {
						promptText += settings.allrules[rules[i]].alertTextCheckboxe+"<br />" 
					}else{
						 promptText += settings.allrules[rules[i]].alertTextCheckboxMultiple+"<br />"
					}	
				}
			}	
			if (callerType == "select-one") { // added by paul@kinetek.net for select boxes, Thank you
					callerName = jQuery(caller).attr("id");
				
				if(!jQuery("select[name="+callerName+"]").val()) {
					isError = true;
					promptText += settings.allrules[rules[i]].alertText+"<br />";
				}
			}
			if (callerType == "select-multiple") { // added by paul@kinetek.net for select boxes, Thank you
					callerName = jQuery(caller).attr("id");
				
				if(!jQuery("#"+callerName).val()) {
					isError = true;
					promptText += settings.allrules[rules[i]].alertText+"<br />";
				}
			}
		}
		function _customRegex(caller,rules,position){		 // VALIDATE REGEX RULES
			customRule = rules[position+1]
			pattern = eval(settings.allrules[customRule].regex)
			
			if(!pattern.test(jQuery(caller).attr('value'))){
				isError = true
				promptText += settings.allrules[customRule].alertText+"<br />"
			}
		}
		function _confirm(caller,rules,position){		 // VALIDATE FIELD MATCH
			confirmField = rules[position+1]
			
			if(jQuery(caller).attr('value') != jQuery("#"+confirmField).attr('value')){
				isError = true
				promptText += settings.allrules["confirm"].alertText+"<br />"
			}
		}
		function _length(caller,rules,position){    // VALIDATE LENGTH
		
			startLength = eval(rules[position+1])
			endLength = eval(rules[position+2])
			feildLength = jQuery(caller).attr('value').length

			if(feildLength<startLength || feildLength>endLength){
				isError = true
				promptText += settings.allrules["length"].alertText+startLength+settings.allrules["length"].alertText2+endLength+settings.allrules["length"].alertText3+"<br />"
			}
		}
		function _minCheckbox(caller,rules,position){    // VALIDATE CHECKBOX NUMBER
		
			nbCheck = eval(rules[position+1])
			groupname = jQuery(caller).attr("name")
			groupSize = jQuery("input[name="+groupname+"]:checked").size()
			
			if(groupSize > nbCheck){	
				isError = true
				promptText += settings.allrules["minCheckbox"].alertText+"<br />"
			}
		}
		return(isError) ? isError : false;
	};
	var closePrompt = function(caller) {	// CLOSE PROMPT WHEN ERROR CORRECTED
		closingPrompt = jQuery(caller).attr("id")

		jQuery("."+closingPrompt).fadeTo("fast",0,function(){
			jQuery("."+closingPrompt).remove()
		});
	};
	var submitValidation = function(caller) {	// FORM SUBMIT VALIDATION LOOPING INLINE VALIDATION
		var stopForm = false
		jQuery(caller).find(".formError").remove()
		var toValidateSize = jQuery(caller).find("[class^=validate]").size()
		
		jQuery(caller).find("[class^=validate]").each(function(){
			var validationPass = loadValidation(this)
			return(validationPass) ? stopForm = true : "";	
		});
		if(stopForm){							// GET IF THERE IS AN ERROR OR NOT FROM THIS VALIDATION FUNCTIONS
			destination = jQuery(".formError:first").offset().top;
			jQuery("html:not(:animated),body:not(:animated)").animate({ scrollTop: destination}, 1100)
			return true;
		}else{
			return false
		}
	};
};