<?php /* Smarty version 2.6.18, created on 2010-12-13 13:08:25
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/raadsstukken/header/votePage.html */ ?>
<!--  -->

<script type="text/javascript">
<!--//--><![CDATA[//><!--
<?php echo '

var text = [\'Voor\', \'Tegen\', \'Onthouden\', \'Afwezig\', \'Kies een stem\'];
var resultText = [\'in behandeling\', \'Aangenomen\', \'Afgewezen\'];
var resultClass = [\'notVoted\', \'voor\', \'tegen\'];

var VoteItem = new Class({
	className: \'vote-item\',

	initialize: function(el) {
		this.element = el;
		this.text = el.getFirst();
	},

	setText: function(text) {
		return this.text.setText(text);
	},

	setClass: function(klass) {
		this.element.setProperty(\'class\', this.className + \' \' + klass);
	}
});

var ResultItem = VoteItem.extend({
	className: \'result-item\'
});

var Votable = new Class({
	_vote: null,

	vote: function(value) {
		if ($defined(this.children)) {
			this.children.each(function(obj) {
				if(obj.getVote() != 3){
					obj.vote(value);
				}
			});
		}
		this.setValue(value);
	},

	getVote: function() {
		return this._vote;
	},

	setValue: function(value) {
		this.element.value = value;
		this.vote_item.setClass(text[value].toLowerCase())
		this.vote_item.setText(text[value]);
		this._vote = value;
	},

	clear: function() {
		this.element.value = \'\';
		this.vote_item.setClass(\'Verdeeld\'.toLowerCase());
		this.vote_item.setText(\'Verdeeld\');
		this._vote = null;
	},

	calculateParentVote: function(value) {
		if ($defined(this.parent)) {
			var p = this.parent;
			var v = [0, 0, 0, 0];
			p.children.each(function(obj) {
				v[obj.getVote()]++;
			});
			if (v[0] && v[1]) {
				p.clear();
			} else {
				v.some(function(item, index) {
					if (item) p.setValue(index);
					return item;
				});
			}
			this.parent.calculateParentVote(value);
		}
	},

	calculateResult: function() {
		var count = [0,0];
		var total = 0;
		$$(\'input.politician\').each(function(el) {
			if (el.value == 0 || el.value == 1) {
				total++;
				count[el.value]++;
			}
		});
		total /= 2;
		if (count[0] > total) {
			$(\'result\').setValue(1);
		} else if (count[1] > total) {
			$(\'result\').setValue(2);
		} else {
			$(\'result\').setValue(0);
		}
	},

	register: function() {
		this.element.object = this;
		this.vote_item = new VoteItem(this.element.getParent().getFirst().getNext().getNext().getNext());
	}
});

var Council = Votable.extend({
	initialize: function(el) {
		this.element = el;
		this.children = [];
		$$(\'input.party[name^=party]\').each(function(el) {
			var id = el.name.substring(6, el.name.indexOf(\']\'));
			this.children.push(new Party(id, el, this));
		}, this);
		this.register();
	}
});

var Party = Votable.extend({
	initialize: function(id, el, council) {
		this.id = id;
		this.element = el;
		this.parent = council;
		this.children = [];
		$ES(\'input.politician[name^=politician]\').filterByClass(el.name).each(function(el) {
			var id = el.name.substring(11, el.name.indexOf(\']\'));
			this.children.push(new Politician(id, el, this));
		}, this);
		this.register();
	}
});

var Politician = Votable.extend({
	initialize: function(id, el, party) {
		this.id = id;
		this.element = el;
		this.parent = party;
		this.register();
	}
});

function initBox(boxes, type) {
	var box = $(type + \'-box\');
	box.element = $E(\'.\' + type + \'-item\');
	box.open = function (el) {
		boxes.each(function (el) {
			// Close all other boxes
			if (box != el) {
				el.close();
				el.element.fireEvent(\'mouseout\');
			}
			$$(\'.vote-item\').each(function (e) { e.setStyle(\'z-index\', 1); });
			$$(\'.result-item\').each(function (e) { e.setStyle(\'z-index\', 1); });
		});
		if (this.element != el) {
			this.element.setStyle(\'width\', \'9em\');
		}
		var pos1 = el.getCoordinates();
		var pos2 = $(\'voteForm\').getCoordinates();
		this.setStyles({
			top: pos1.top - pos2.top + 75,
			left: pos1.left - pos2.left
		});
		el.setStyle(\'z-index\', 100);
		this.setStyle(\'display\', \'\');
		this.element = el;
		if (type == \'vote\') {
			this.setValue(el.getPrevious().getPrevious().getPrevious().value);
			this.setMessage(el.getPrevious().getPrevious().value);
		} else {
			this.setValue(el.getPrevious().value);
		}
	}

	box.close = function (el, cancel) {
		this.setStyle(\'display\', \'none\');
		if (type == \'vote\' && !cancel)
			this.saveMessage(this.element.getPrevious().getPrevious())
	}

	box.isOpen = function (el) {
		return this.element == el && this.style.display == \'\';
	}

	box.radios = $ES(\'input\', box);

	box.setValue = function(value) {
		if (value == null || value == \'\')
			box.radios.each(function(el) { el.checked = false });
		else
			box.radios[value].checked = true;
	};
	
	box.setMessage = function(value) {
		if (value == null || value == \'\' || value == 0) {
			$(\'vote_message_input\').value = \'\';
			$(\'vote_message\').style.display = \'none\';
			$(\'vote_message_link\').style.display = \'\';
		} else {
			$(\'vote_message_input\').value = value;
			$(\'vote_message\').style.display = \'\';
			$(\'vote_message_link\').style.display = \'none\';
		}
	};
	
	box.saveMessage = function(el) {
		el.value = $(\'vote_message_input\').getValue();
		$(\'vote_message_input\').value = \'\';
		el.getNext().setAttribute(\'title\', el.value);
		el.getNext().getNext().setAttribute(\'title\', el.value);
		if (el.value)
			el.getNext().style.display = \'\';
		else
			el.getNext().style.display = \'none\';
	};

	/*
	$$(\'.vote-item\').each (function(el) {
		el.slide = new Fx.Style(el, \'width\', { transition: Fx.Transitions.Quad.easeOut, duration: 300 });
	});
	*/

	$$(\'.\' + type + \'-item\').addEvent(\'click\', function(e) {
		if (box.isOpen(this)) {
			box.close(this);
		} else {
			box.open(this);
		}
	}).addEvent(\'mouseover\', function(e) {
		this.caption.addClass(\'highlight\');
		if (!box.isOpen(this)) {
			this.setStyle(\'width\', \'9em\');
		}
	}).addEvent(\'mouseout\', function(e) {
		this.caption.removeClass(\'highlight\');
		if (!box.isOpen(this)) {
			this.setStyle(\'width\', \'9em\');
		}
	}).each(function(el) {
			el.caption = el.getParent().getPrevious().getFirst();
	});
	
	if (type == \'vote\') {
		$(\'vote_message_input\').addEvent(window.ie ? \'keydown\' : \'keypress\', function(e) {
			if (e.key == \'enter\')
				box.close(this);
			else if (e.key == \'esc\')
				box.close(this, true);
		}.bindWithEvent(this));
		
		$(\'vote_message_ok\').addEvent(\'click\', function(e) {
			box.close(this);
			e.stop();
		}.bindWithEvent(this));

		$(\'vote_message_cancel\').addEvent(\'click\', function(e) {
			box.close(this, true);
			e.stop();
		}.bindWithEvent(this));
	}
}

window.addEvent(\'domready\', function() {
	window.council = new Council($$(\'input.raad\')[0]);
	var result = $(\'result\');

/*
	$(\'preview\').addEvent(\'click\', function(e) {
		new Event(e).stop();
		new Ajax(\''; ?>
<?php echo $_SERVER['REQUEST_URI']; ?>
<?php echo '?preview\', { method: \'post\', data: $(\'voteForm\'), onComplete: function(text) {
			window.open(text, \'preview\');
		}}).request();
	});
*/

	$$(\'input.vote\').each(function(el) {
		if (el.value != \'\' && el.value) {
			el.object.vote(el.value);
			el.object.calculateParentVote(el.value);
		}
	});

	result.object = new ResultItem(result.getNext());
	result.setValue = function(value) {
		if (!$defined(value)) value = result.value;
		else result.value = value;
		result.object.setText(resultText[value]);
		result.object.setClass(resultClass[value]);
	}

	if (result.value != \'\') {
		result.setValue();
	}

	var boxes = [$(\'vote-box\'), $(\'result-box\')];

	initBox(boxes, \'vote\');
	initBox(boxes, \'result\');

	// Radios are not in dom-order
	$(\'result-box\').radios = [$(\'result-box-item-0\'),
														$(\'result-box-item-1\'),
														$(\'result-box-item-2\')];

	$$(\'.vote-box-item\').addEvent(\'click\', function(e) {
		var obj = this.getParent().element.getPrevious().getPrevious().getPrevious().object;
		//console.log(obj);
		var value = this.getFirst().value;
		obj.vote(value);
		obj.calculateParentVote(value);
		obj.calculateResult();
		$(\'vote-box\').close();
		obj.vote_item.element.fireEvent(\'mouseout\');
	});

	$$(\'.result-box-item\').addEvent(\'click\', function(e) {
		var result = $(\'result\');
		result.setValue(this.getFirst().value);
		$(\'result-box\').close();
		result.object.element.fireEvent(\'mouseout\');
	});
});

function toggleParty(el) {
	if (el.getAttribute(\'status\') == \'closed\') {
        el.setAttribute(\'status\', \'open\');
        el.setAttribute(\'class\', \'hover alt active\');
        $$(\'.image_\'+el.getAttribute(\'party\')).each(function (e) { e.setAttribute(\'src\', \'/images/collapse.gif\') });
		$$(\'.party_\'+el.getAttribute(\'party\')).each(function (e) { e.style.display = \'\'; });
	} else {
        el.setAttribute(\'status\', \'closed\');
        el.setAttribute(\'class\', \'hover alt\');
        $$(\'.image_\'+el.getAttribute(\'party\')).each(function (e) { e.setAttribute(\'src\', \'/images/expand.gif\') });
        $$(\'.party_\'+el.getAttribute(\'party\')).each(function (e) { e.style.display = \'none\';});
    }
	return false;
}

function showMessageInput() {
	$(\'vote_message_link\').style.display = \'none\';
	$(\'vote_message\').style.display = \'\';
}

//--><!]]>
</script>'; ?>
