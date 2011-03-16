jQuery.noConflict();

function handleTextFields(textfield) {
	textfield.prev().addClass('overlay').end().focus(function() {
		jQuery(this).prev().addClass('focus');
	}).blur(function() {
		jQuery(this).prev().removeClass('focus');
		if (jQuery(this).val() != '') {
			jQuery(this).prev().addClass('hastext');
		} else {
			jQuery(this).prev().removeClass('hastext');
		}
	}).keyup(function() {
		if (jQuery(this).val() != '') {
			jQuery(this).prev().addClass('hastext');
		} else {
			jQuery(this).prev().removeClass('hastext');
		}
	}).keypress(function() {
		if (jQuery(this).val() != '') {
			jQuery(this).prev().addClass('hastext');
		} else {
			jQuery(this).prev().removeClass('hastext');
		}
	});
	textfield.each(function() {
		if (jQuery(this).val() != '') {
			jQuery(this).prev().addClass('hastext');
		}
	});

}


function fillSelect(select, text) {
	var lines = text.split('\n');
	var selected = select.value;
	select.options.length = 0;
	for (var i = 0; i < lines.length; i++) {
		keyval = lines[i].split('||');
		option = new Option();
		option.value = keyval[0];
		option.label = keyval[1];
		option.selected = keyval[0] == selected;
		option.text = keyval[1];
		select.options[i] = option;
	}
}
