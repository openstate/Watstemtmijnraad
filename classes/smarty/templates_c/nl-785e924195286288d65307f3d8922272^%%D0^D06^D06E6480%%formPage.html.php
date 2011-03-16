<?php /* Smarty version 2.6.18, created on 2010-12-13 13:05:22
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/header/formPage.html */ ?>

<script type="text/javascript" src="/javascripts/formValidation.js"></script>
<?php if (! $this->_tpl_vars['form']['freeze']): ?>
<script type="text/javascript" src="/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="/javascripts/tinymce_init.js"></script>
<script type="text/javascript" src="/javascripts/mootools/Autocompleter.js"></script>
<script type="text/javascript" src="/javascripts/mootools/Observer.js"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
var allTags = <?php echo $this->_tpl_vars['allTags']; ?>
;
var tags = <?php echo $this->_tpl_vars['tags']; ?>
;
var cats = <?php echo $this->_tpl_vars['cats']; ?>
;
var catNames = <?php echo $this->_tpl_vars['catNames']; ?>
;

<?php echo '
var currentDate;
var unrestrictParent;

var tag_button;
var tag_text;
var tag_list;
var cat_button;
var cat_select;
var cat_list;

window.addEvent(\'domready\', function() {
	currentDate = $(\'vote_date\').getValue();
	unrestrictParent = $(\'unrestrict_parent\').getValue();
/*
	$(\'preview\').addEvent(\'click\', function(e) {
		new Event(e).stop();
		tinyMCE.triggerSave();
		new Ajax(\''; ?>
<?php echo $_SERVER['REQUEST_URI']; ?>
<?php echo '?preview\', { method: \'post\', data: $(\'rsForm\'), onComplete: function(text) {
			window.open(text, \'preview\');
		}}).request();
	});
*/
	tag_button = $$(\'.tag_add\');
	tag_text = $(\'tag_text\');
	tag_list = $(\'tag_list\');
	cat_button = $$(\'.cat_add\');
	cat_select = $(\'cat_select\');
	cat_list = $(\'cat_list\');

/*	tags.each(function(item) {
		addTag(item);
	});

	cats.each(function(item, key) {
		addCat(item, catNames[key]);
	});*/

	tag_button.addEvent(\'click\', function() {
		addTag();
		/*if (Validator.notWhitespace(tag_text.getValue()) && !tags.contains(tag_text.getValue())) {
			addTag(tag_text.getValue());
		}*/
	});

	cat_button.addEvent(\'click\', function() {
		addCat();
		/*var value = parseInt(cat_select.getValue());
		if (cat_select.value > 0 && !cats.contains(value)) {
			addCat(value, cat_select.options[cat_select.selectedIndex].label);
		}*/
	});

	var type = $(\'type\');
	type.addEvent(\'change\', function(e) {
		$ES(\'div\', \'sub_el\').each(function(el) {
			toggleSubmitter(el, false);
		});
		
		//FUCK!!! this was bound to type.selectedIndex instead of .value
		//the options are from database, fetched without sorting... any change in database
		//will result in reordering the records (because of dead tuples)... you get "working"
		//drop-dowm menu with inconsistent (label -> action) link... this is sick!
		switch (\'\' + type.options[type.selectedIndex].value) {
			case \'1\': //Raadsvoorstel
				toggleSubmitter(\'sub_el_rs\');
				$(\'parent_row\').setStyle(\'display\', \'none\');
				break;
				
			case \'2\': //Initiatiefvoorstel
				toggleSubmitter(\'sub_el_members\');
				$(\'parent_row\').setStyle(\'display\', \'none\');
				break;
				
			case \'3\': //Amendement
			case \'4\': //Motie
				$(\'parent_row\').setStyle(\'display\', \'\');
				toggleSubmitter(\'sub_el_members\');
				break;
				
			case \'5\': //Burgerinitiatief
				$(\'parent_row\').setStyle(\'display\', \'none\');
				toggleSubmitter(\'sub_el_citizen\');
				break;

			default: //Onbekend
				$(\'parent_row\').setStyle(\'display\', \'none\');
				toggleSubmitter(\'sub_el_onbekend\');
		}
	});

	$ES(\'select\', \'sub_el\').each(function (el) {
		el.disabled = (el.getParent().getStyle(\'display\') == \'none\');
	});
	
	$(\'vote_date\').addEvent(\'change\', dateOnChange);
	
	$$(\'.tag_autocomplete\').each(function(e) { autocomplete_tags(e); });

    /* Used in \'/wizard\' \'/raadsstukken\' */
    if ($(\'vote_date\') && $(\'vote_date\').value == \'\') {
        $$(\'div.vote_date\').setStyle(\'display\', \'none\'); // Hide by default, if no value pre-defined (posted)
    }

    // Adding a date picker with disabled weekends (0 - for Sunday and 6 for Saturday)
    deliveryDate = new Calendar(
        { vote_date: \'d-m-Y\' },
        { blocked:  [\'0 * * 0,6\'],
          tweak : {x: 180, y: -150},
          days: [\'Zondag\', \'Mandag\', \'Dinsdag\', \'Woensdag\', \'Donderdag\', \'Vrijdag\', \'Zaterdag\'],
          months: [\'januari\', \'februari\', \'maart\', \'april\', \'mei\', \'juni\', \'juli\', \'augustus\', \'september\', \'oktober\', \'november\', \'december\']});
});

function autocomplete_tags(el) {
	new Autocompleter.Local(el, allTags, {
		\'delay\': 0,
		\'maxChoices\': 10,
		\'filterTokens\': function() {
			var regex = new RegExp(\'^\'+this.queryValue.escapeRegExp(), \'i\');
			return this.tokens.filter(function(tag) {
				return regex.test(tag);
			});
		},
		\'injectChoice\': function(choice) {
			var el = new Element(\'li\').setHTML(this.markQueryValue(choice));
			el.inputValue = choice;
			this.addChoiceEvents(el).injectInside(this.choices);
		}
	});
}

function toggleSubmitter(el, toggle) {
	if (null == toggle) toggle = true;
	var sel = $E(\'select\', el);
	if (sel) sel.disabled = !toggle;
	$(el).setStyle(\'display\', toggle ? \'\' : \'none\');
}

function addTag() {
	if ($(\'tag_clone\').style.display == \'none\') {
		$(\'tag_empty\').style.display = \'none\';
		$(\'tag_clone\').style.display = \'\';
	} else {
		clone = $(\'tag_clone\').clone();
		$ES(\'input\', clone).each(function (e) { e.value = \'\'; autocomplete_tags(e); });
		clone.injectBefore($(\'tag_add\'));
	}
}

function addCat() {
	clone = $(\'cat_clone\').clone();
	$ES(\'select\', clone).each(function (e) { e.selectedIndex = -1; });
	clone.inject(\'cat_add\', \'before\');
}

function dateOnChange() {
	date = $(\'vote_date\').getValue();
	if (date == currentDate)
		return;
	currentDate = date;

	var request = new Ajax(\'/raadsstukken/submitters/\', {method: \'get\',
		\'data\': \'date=\'+date+\'&s=\'+$(\'submitters\').getValue(),
		onComplete: function(text, xml) {
			if (text == \'\')
				alert(\'Interne fout\');
			else
				$(\'sub_el_members\').empty().setHTML(text);
	}});

	request.request();

	if (!$(\'unrestrict_parent\').getValue()) {
		var request = new Ajax(\'/raadsstukken/parents/\', {method: \'get\',
			\'data\': \'date=\'+date+\'&s=\'+$(\'parent\').getValue()'; ?>
<?php if ($this->_tpl_vars['formdata']['id']): ?>+'&ex=<?php echo $this->_tpl_vars['formdata']['id']; ?>
'<?php endif; ?><?php echo ',
			onComplete: function(text, xml) {
				if (text == \'\')
					alert(\'Interne fout\');
				else
					$(\'parent_el\').empty().setHTML(text);
		}});

		request.request();
	}
}

function unrestrictParentOnChange() {
	if (unrestrictParent == $(\'unrestrict_parent\').getValue())
		return;
	unrestrictParent = $(\'unrestrict_parent\').getValue();

	var request = new Ajax(\'/raadsstukken/parents/\', {method: \'get\',
		\'data\': (!unrestrictParent ? \'date=\'+currentDate+\'&\' : \'\')+\'s=\'+$(\'parent\').getValue()'; ?>
<?php if ($this->_tpl_vars['formdata']['id']): ?>+'&ex=<?php echo $this->_tpl_vars['formdata']['id']; ?>
'<?php endif; ?><?php echo ',
		onComplete: function(text, xml) {
			if (text == \'\')
				alert(\'Interne fout\');
			else
				$(\'parent_el\').empty().setHTML(text);
	}});

	request.request();
}

'; ?>

//--><!]]>
</script>
<?php else: ?>

<script type="text/javascript">
<!--//--><![CDATA[//><!--

<?php echo '
window.addEvent(\'domready\', function() {
	$(\'preview\').addEvent(\'click\', function(e) {
		new Event(e).stop();
		window.open(\''; ?>
<?php echo $this->_tpl_vars['preview_link']; ?>
<?php echo '\', \'preview\');
	});
});
'; ?>

//--><!]]>
</script>
<?php endif; ?>