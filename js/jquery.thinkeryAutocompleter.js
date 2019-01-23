;(function($) {
	$.fn.thinkeryAutocompleter = function(options) {
		if (typeof options != "object") options = {};
		return this.each(function() {
			$(this).on("tag-autocompleter keyup", function(e) {
				return autocompleter.apply(this, [e, options]);
			}).on("tag-autocompleted", function(e, tagName) {
				return autocomplete_it.apply(this, [e, tagName, options]);
			}).on("keydown", function(e) {
				return autocompleter_keydown.apply(this, [e, options]);
			}).on("hide-autocomplete", function(e) {
				return autocompleter_hide.apply(this, [e, options]);
			}).prop("autocomplete", "off");
		});
	};

	var slStart;

	function check_tag_autocomplete_text(sval, options) {
		var i, l, tmp;

		if (slStart) {
			tmp = $.trim(sval);

			tmp = tmp.substr(0, slStart + 1);

			if (tmp[tmp.length - 1] == " ") return false;

			tmp = tmp.split(" ");
		} else {
			tmp = $.trim(sval).split(" ");
		}

		tmp.reverse();
		if (options.mixed) {
			for (i = 0, l = tmp.length; i < l; i++) {
				if (tmp[i].substr(0, 1) == "#") {
					return tmp[i].substr(1);
				} else {
					return false;
				}
			}
		} else if (tmp.length) return tmp[0];

		return false;
	}

	function check_tag_autocomplete(inp, options) {
		try {
			slStart = inp.get(0).selectionStart || slStart;
		} catch(err) {}

		var sval = inp.val();
		if (sval[sval.length-1] == " ") return false;

		return check_tag_autocomplete_text(sval, options);
	}

	function tag_autocomplete_render(tagName, autocompleteUL, options) {
		$("<li>" + tagName + " <span style=\"float: right; color: #8c8c8c;\">" + options.tags[tagName].count + "</span></li>").data("tag", tagName).appendTo(autocompleteUL);
	}

	function autocompleter(e, options) {
		var $this = $(this);
		var oldv = $this.data('oldv'), v = $this.val();

		if (oldv == v) return;

		var tagToComplete = check_tag_autocomplete($this, options);
		var autocompleteUL = $this.next(".tag-autocomplete");
		$this.data('oldv', v);
		if (tagToComplete === false) {
			autocompleteUL.hide();
			return;
		}
		tagToComplete = tagToComplete.toLowerCase();
		var tl = tagToComplete.length;

		if (!autocompleteUL.size()) {
			autocompleteUL = $('<ul class="tag-autocomplete edit">').on("click", "li", function(e) {
				$this.trigger("tag-autocompleted", $(this).data("tag"), options);
			}).insertAfter($this);

			autocompleteUL.parents('#jqibox,form,#container').on("mousedown", function(e) {
				if (e.target && $(e.target).parent(".tag-autocomplete").size()) {
					return false;
				}
				autocompleteUL.hide();
			});
		}

		if (autocompleteUL.closest('#thinkeryInfobox').length) {
			$(window).on("resize", function() {
				var d = {
					textAlign: "left",
					marginTop: ".3em"
				};
				var this_offset = $this.offset();
				var div_offset = $this.closest("div").offset();
				if (
					typeof this_offset != "undefined" && typeof this_offset.left != "undefined" &&
					typeof div_offset != "undefined" && typeof div_offset.left != "undefined"
				) {
					d.marginLeft = this_offset.left - div_offset.left - 30;
				}
				autocompleteUL.css(d);
			}).trigger("resize");
		}


		var alreadyUsed_pre = autocompleteUL.data("already-used") || {};
		var tag, i, l, selectedTags = [], alreadyUsed = {}, updateAlreadyUsed = false;
		for (tag in alreadyUsed_pre) {
			if (v.indexOf(tag) == -1) {
				updateAlreadyUsed = true;
				continue;
			}
			alreadyUsed[tag] = true;
		}
		if (updateAlreadyUsed) autocompleteUL.data("already-used", alreadyUsed);

		var preselectedTags = autocompleteUL.data("preselected") || false;
		if (autocompleteUL.data("tl") > tl) preselectedTags = false;
		if (false !== preselectedTags) {
			if (preselectedTags.length > 0) {
				var l1 = Math.max(1, tl - 1);
				if (preselectedTags[0].substr(0, l1) != tagToComplete.substr(0, l1)) preselectedTags = false;
			}
		}

		autocompleteUL.empty().hide();
		if (false !== preselectedTags) {
			for (i = 0, l = preselectedTags.length; i < l; i++) {
				tag = preselectedTags[i];
				if (alreadyUsed[tag]) continue;
				if (tag.substr(0, tl) != tagToComplete) continue;
				selectedTags.push(tag);
				tag_autocomplete_render(tag, autocompleteUL, options);
			}
		} else {
			l = 0;
			for (tag in options.tags) {
				if (alreadyUsed[tag]) continue;
				if (tag.substr(0, tl).toLowerCase() != tagToComplete) continue;
				selectedTags.push(tag);
				l += 1;
				if (l > 15) continue;
				tag_autocomplete_render(tag, autocompleteUL, options);
			}
		}
		autocompleteUL.data("preselected", selectedTags);
		autocompleteUL.data("tl", tl);

		if (autocompleteUL.find('li').size()) {
			autocompleteUL.find('li:first').addClass('active');
			autocompleteUL.show();
		} else {
			autocompleteUL.hide();
		}
	}

	function autocompleter_hide(e, tagName, options) {
		return $(this).next(".tag-autocomplete").hide();
	}

	function autocomplete_it(e, tagName, options) {
		var $this = $(this), i, l;
		var tmp = $.trim($this.val()).split(" ");
		var autocompleteUL = $this.next(".tag-autocomplete");

		//slStart = this.selectionStart || slStart;
		//if (slStart > -1) {
		//}
		if (options.onlyone) {
			$this.val(tagName);
		} else {
			tmp.reverse();
			for (i = 0, l = tmp.length; i < l; i++) {
				if (options.mixed && tmp[i].substr(0, 1) == "#") {
					tmp[i] = "#" + tagName;
				} else if (tagName.substr(0, tmp[i].length) == tmp[i]) {
					tmp[i] = tagName;
				}
				break;
			}
			tmp.reverse();
			$this.val(tmp.join(" ") + " ");
		}

		var alreadyUsed = autocompleteUL.data("already-used") || {};
		alreadyUsed[tagName] = true;
		autocompleteUL.data("already-used", alreadyUsed);

		autocompleteUL.empty().hide();
		$this.focus();
	}

	function autocompleter_keydown(e, options) {
		if (!e) return;
		var keyCode = e.which, $this = $(this);
		var keyPressed = String.fromCharCode(keyCode);
		var autocompleteUL = $this.next(".tag-autocomplete");

		$this.trigger('tag-autocompleter');

		var li_active = autocompleteUL.find('li.active');
		switch (keyCode) {
			case 9: // tab
				if (e.shiftKey) {
					autocompleteUL.hide();
					li_active.removeClass("active");
					return true;
				}
				if (li_active.size()) {
					li_active.trigger('click');
					return false;
				}
				return true;

			case 13: // enter
				if (li_active.size()) {
					li_active.trigger('click');
					return false;
				}
				return true;

			case 32: // space
				var tagName = check_tag_autocomplete_text($this.val(), options);
				var alreadyUsed = autocompleteUL.data("already-used") || {};
				alreadyUsed[tagName] = true;
				autocompleteUL.data("already-used", alreadyUsed);

				return true;

			case 27: // esc
				autocompleteUL.hide();
				li_active.removeClass("active");
				return false;

			case 38: // up
				if (li_active.prev().size()) {
					li_active.removeClass('active').prev().addClass('active');
				} else {
					li_active.trigger('click');
				}
				if (li_active.size()) return false;
				return true;

			case 40: // down
				if (li_active.next().size()) {
					li_active.removeClass('active').next().addClass('active');
				} else {
					li_active.trigger('click');
				}
				if (li_active.size()) return false;
				return true;
		}
	}
})(jQuery);
