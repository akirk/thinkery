var scrollIntoView = function(el) {
	if (!el || !el.length) return;
	var h = window.innerHeight - $("ul.things").position().top, eh = el.height();
	var p = el.position().top, b = $(window).scrollTop();
	if (p + eh > b + h || p < b) {
		$("html,body").stop().animate({scrollTop: Math.floor(p + eh - h / 2) + "px"}, 400);
	}
};

var implodeTextList = function(list) {
	var startTag = "<b>", endTag = "</b>";
	if (typeof list[0] != "string") return list;
	if (list.length == 1) return startTag + list[0] + endTag;
	var tempList = list.slice();
	var last = startTag + tempList.pop() + endTag;
	return startTag + tempList.join(endTag + ", " + startTag) + endTag + " and " + last;
};

var isTouchDevice = (function() {
	var android = navigator.userAgent.indexOf('Android') != -1;
	return android || !!('createTouch' in document);
	//return typeof Touch == "object";
})();
var isIPad = navigator.userAgent.match(/iPad/i) !== null;

var Thinkery = (function($) {
	var username, tag, subtag, data, originalData, ckeditor;
	var showingArchived = location.search == '?archived';

	if (typeof jQuery.prompt != 'undefined') jQuery.prompt.setDefaults({
		persistent: false,
		opacity: 0.7,
		show: "fadeIn",
		overlayspeed: "fast",
		promptspeed: "fast",
		top: "25%"
	});

	// impromptu for mobile devices
	if (typeof jQuery.prompt != 'undefined' && isTouchDevice) jQuery.prompt.setDefaults({
		persistent: false,
		opacity: 0.7,
		show: "show",
		overlayspeed: "fast",
		promptspeed: "fast",
		top: "5%"
	});

	function escapeHtml(text) {
		return text
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	var updateTimeLeft = function() {
		var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
		function timeLeft(secs, shortDate) {
			if (typeof shortDate == "undefined") shortDate = true;

			var ago = false;
			if (secs < 0) {
				secs = -secs;
				ago = true;
			}

			var x, s, t = [];

			if (secs >= 3456000) { // 40 days
				s = new Date(new Date() - secs * 1000);
				return months[s.getMonth()] + " " + s.getDate() + ", " + s.getFullYear();
			}
			if (secs >= 86400) {
				s = Math.floor(secs / 86400);
				x = s + " d" + (shortDate ? "" : "ay");
				if (!shortDate && s > 1) x += "s";
				t.push(x);
				secs -= s * 86400;
			}

			if (secs >= 3600) {
				s = Math.floor(secs / 3600);
				x = s + " h" + (shortDate ? "r" : "our");
				if (s > 1) x += "s";
				t.push(x);
				secs -= s * 3600;
			}

			if (secs >= 60) {
				s = Math.floor(secs / 60);
				x = s + " min" + (shortDate ? "" : "ute");
				if (s > 1) x += "s";
				t.push(x);
				secs -= s * 60;
			}

			if (t.length === 0) {
				if (!secs) {
					t.push("just before");
					ago = false;
				}
				else t.push(parseInt(secs, 10) + "s");
			}

			var ret = "";
			s = "";
			for (var i = Math.min(1, t.length - 1); i >= 0; i--) {
				ret = t[i] + s + ret;
				s = (shortDate || s) ? ", " : " and ";
			}

			return ret + (ago ? " ago" : "");
		}
		$("span.dateTime").each(function() {
			var $this = $(this);
			var t = $this.attr("data-t");
			if (!t) return;
			t = t.split(",");
			var time = new Date();
			time.setUTCHours(parseInt(t[3], 10));
			time.setUTCMinutes(parseInt(t[4], 10));
			time.setUTCSeconds(parseInt(t[5], 10));
			time.setUTCMonth(0);
			time.setUTCFullYear(t[0]);
			time.setUTCDate(parseInt(t[2], 10));
			time.setUTCMonth(parseInt(t[1], 10) - 1);

			var diff = (time.getTime() - new Date()) / 1000;
			$this.text(timeLeft(diff, !$this.hasClass("long")));
		});
	};

	setInterval(updateTimeLeft, 30000);

	var lastTag = false;
	var loadTag = function(_tag, _subtag, initialLoad, nonEmpty) {
		if (typeof initialLoad == "undefined") initialLoad = false;
		if (typeof _subtag == "undefined") _subtag = false;
		if (!Thinkery.mainListUrl) return false;
		if (_tag == lastTag) return false;
		lastTag = _tag + _subtag;

		var tagUrlPart = "";
		if (_tag) tagUrlPart += "tag=" + encodeURIComponent(_tag);
		if (_subtag) tagUrlPart += "&subtag=" + encodeURIComponent(_subtag);
		if (showingArchived) tagUrlPart += "&archived=1";
		$.getJSON(Thinkery.mainListUrl + tagUrlPart, function(r) {

			if (initialLoad && data.length === 0) {
				location.href = "/" + username;
				return;
			}
			$("nav#menu ul li ul").remove();
			var tags = $("nav#menu ul li");
			tags.removeClass("active");

			if (typeof _tag == "undefined" || !_tag) {
				if (typeof history.pushState == "function") history.pushState({tag: ""}, username, "/" + username);
				tags.filter("li:contains(All)").addClass("active");
				tag = false;
			} else {
				_tag = String(_tag);
				var f = "li[data-tag='" + _tag.replace("'", "\\'") + "']";

				var t = tags.filter(f);

				if (_tag.substr(0, 1) != ":" && typeof Thinkery.tags[_tag] != "undefined") {
					var _subtags = Thinkery.tags[_tag].subtags;
					if (typeof _subtags != "undefined") {
						var html = "<ul>";
						var c = 0, min_count = 3, subtag_found = _subtag ? true : false;
						for (var s in _subtags) {
							if (c === 0 && _subtags[s] < min_count) min_count = 1;
							if (_subtags[s] < min_count || c++ > 4) {
								if (subtag_found) break;
								if (s != _subtag) continue;
							}
							html += "<li data-tag='" + _tag + "' data-subtag='" + s + "'>";
							html += "<a href='" + Thinkery.tags[_tag].url + "+" + s + "'>";
							html += s;
							html += ' <small class="num">' + _subtags[s] + "</small>";
							html += "</a></li>";
						}
						html += "</ul>";
						t.append(html);
					}
				}
				var archived = showingArchived ? "?archived" : "";

				if (_subtag) {
					f += "[data-subtag='" + _subtag.replace("'", "\\'") + "']";
					if (typeof history.pushState == "function") history.pushState({tag: _tag}, username + " - " + _tag + " " + _subtag, "/" + username + "/" + _tag + "+" + _subtag + archived);
				} else {
					if (typeof history.pushState == "function") history.pushState({tag: _tag}, username + " - " + _tag, "/" + username + "/" + _tag + archived);

				}
				$("nav#menu ul " + f).removeClass("active").eq(0).addClass("active");
				tag = _tag;
				subtag = _subtag;
			}

			if (typeof _gaq != "undefined") _gaq.push(['_trackPageview', baseCustomPageView + "/" + tag]);

			originalData = data = r;
			$("section#things ul").show(function() {
				var searchAdd = $("#searchadd");
				if (nonEmpty === true) {

				} else if (searchAdd.val() !== "") {
					searchAdd.val("").keyup();
				}
				if (!$("button.ir.force-add").length) {
					$("button.ir").addClass("search").removeClass("add");
				}
				$("sub.results, sub.hint").addClass("hidden");
				updateList();
				$("section#things ul li:first").addClass("active");
				$("section#things ul").fadeIn();
				if ($("#bulkControls").is(":visible")) showBulk();

				$("#currentTag").val((_tag ? _tag : "") + (_subtag ? " " + _subtag : ""));

				$(window).trigger('changePinned');

			});
			retrieveThings();
			registerLazyLoad();
			updateFlyout(0, false, true);
		});
		return false;
	};

	var retrieveQueue = 0, retrieveTimeout = false;
	// update items that have not been retrieved
	var retrieveThings = function() {
		return;
		if (retrieveTimeout) {
			clearTimeout(retrieveTimeout);
			if (retrieveQueue > 3) {
				retrieveTimeout = setTimeout(retrieveThings, 3000);
				return;
			}
		}
		var l = data.length;
		for (var i = 0; i < l; i++) {
			if (data[i].retrieved) continue;
			if (retrieveQueue > 3) {
				retrieveTimeout = setTimeout(retrieveThings, 3000);
				break;
			}
			retrieveQueue += 1;
			data[i].thinking = true;
			updateList(i);
			$.ajax({
				url: "/retrieve.php",
				type: "post",
				data: {id: data[i]._id},
				success: function(r) {
					retrieveQueue -= 1;
					if (typeof r._id == "undefined") return false;
					for (var i = 0, l = data.length; i < l; i++) {
						if (data[i]._id != r._id) continue;
						data[i] = r;
						if ($("section#things li.active").index() == i) {
							updateFlyout(i, $('section#flyout .content.edit').is(":visible"));
						}
						updateList(i);
						break;
					}
				},
				error: function() {
					retrieveQueue -= 1;
				}
			});
		}
	};

	/* Check if more things can be loaded */
	var checkMore = function() {
		var l = $('section#things li:not(.no-thing)').length;
		var s = $('#searchadd');
		var num = parseInt($('#menu li.active > a>.num').eq(0).text(), 10);
		if (isNaN(num)) num = 0;

		if (!l || l >= num || (s.length !== 0 && s.val() !== '')) {
			$('.loadmore').addClass('inactive').find('a').text('');
			return false;
		}

		$('.loadmore').removeClass('inactive').find('a').text('Show more ↓');
		return true;
	};

	/* init Lazy Loading */
	var registerLazyLoad = function(initLoad) {
		if (typeof initLoad == 'undefined') initLoad = false;

		//page init
		$('section#things').data('page', 0);
		checkMore();

		$(window).unbind('scroll').bind('scroll', function() {
			if ($(window).scrollTop() != $(document).height() - $(window).height()) return;

			setTimeout(function() {
				if ($(window).scrollTop() != $(document).height() - $(window).height()) return;
				loadThings(initLoad);
			}, 300);
		});

		$('.loadmore a').unbind('click').on('click', function(e) {
			e.preventDefault();
			loadThings(initLoad);
		});
	};

	/* load more things */
	var loadThings = function(noargs) {
		if (!checkMore()) return;
		var _data;

		//show loader, increment page count
		$('section#things, .loadmore').addClass('loading');
		$('section#things').data('page', parseInt($('section#things').data('page'), 10) + 1);

		if (typeof noargs !== 'undefined' && noargs) {
			_data = {
				'page': 0
			};
		} else {
			var tagli = $('#menu ul li.active');
			var tag = tagli.attr('data-tag');
			var subtag = tagli.attr('data-subtag');
			var page = $('section#things').data('page');

			_data = {
				'page': page
			};
			if (typeof tag !== 'undefined' && tag !== '') {
				_data.tag = tag;
			}
			if (typeof subtag !== 'undefined' && subtag !== '') {
				_data.subtag = subtag;
			}
			if (showingArchived) {
				_data.archived = true;
			}
			if ($('#search h4 a').text() !== "") {
				_data.username = $('#search h4 a').text();
			}
		}
		var url, _type = "get";
		if (getPref("currentQuery")) {
			url = '/search.php';
			_type = "post";
		} else if (typeof Thinkery.mainListUrl == "undefined") {
			url = '/mainlist.php';
		} else {
			url = Thinkery.mainListUrl;
		}
		$.ajax({
			url: url,
			dataType: 'json',
			data: _data,
			type: _type,
			success: function(r) {
				if (r === '') {
					$('.loadmore').addClass('inactive').find('a').text('');
				} else {
					updateData(r);
					retrieveThings();
				}
				$('section#things, .loadmore').removeClass('loading');
			}
		});
	};

	var reloadSidebar = function(full, data, cb) {
		if (typeof full == "undefined") full = false;
		if (typeof data == "undefined") data = false;

		var sidebarUrl = "/sidebar.php?username=" + encodeURIComponent(username);
		if (tag) sidebarUrl += "&tag=" + encodeURIComponent(tag);
		if (subtag) sidebarUrl += "&subtag=" + encodeURIComponent(subtag);
		if (location.search == "?archived") sidebarUrl += "&archived";
		if (full) sidebarUrl += "&full";

		$("nav#menu").load(sidebarUrl + " nav#menu>*", data, function() {
			if ($("#bulkControls").is(":visible")) {
				$('#toggleBulk').addClass("active");
			}
			$(window).trigger('reloadSidebar');
			if (typeof cb == "function") cb();
		});
	};

	var reloadThings = function() {
		lastTag = false;
		loadTag(tag, subtag);
	};

	var updateData = function(d) {
		if (!d) {
			$('.loadmore').addClass('inactive').find('a').text('');
			return;
		}
		for (i = 0, l = d.length; i < l; i++) {
			data.push(d[i]);
		}
		originalData = data;
		updateList();
	};

	var getPrivacyIcon = function(tmp) {
		return '<span class="icn grey ' + getPrivacyClass(tmp) + ' tip ' + (tmp.canEdit ? 'canEdit' : '') + ' title="' + getPrivacyTitle(tmp) + '"></span>';
	};

	var getPrivacyTitle = function(tmp, html) {
		if (typeof html == "undefined") html = false;
		var privacyTitle = "Private: only you can see it";
		if (!tmp["private"]) {
			privacyTitle = "Public: everybody can see it";
			if (tmp.shared) {
				privacyTitle += '; also shared between ' + implodeTextList(tmp.shared);
			}
		} else if (tmp.shared) {
			privacyTitle = 'Shared between ' + implodeTextList(tmp.shared);
		}
		if (!html) privacyTitle = privacyTitle.replace(/</g, "&lt;").replace(/>/g, "&gt;");
		return privacyTitle;
	};

	var getPrivacyIconText = function(tmp) {
		var friends = tmp.shared ? tmp.shared.length - 1 : 0;
		var privacyIconText = "Private";
		if (!tmp["private"]) {
			privacyIconText = "Public" + (friends > 0 ? " + " + friends + ' Friend' + (friends == 1 ? "" : "s") : "");
		} else if (tmp.shared) {
			privacyIconText = friends + ' Friend' + (friends == 1 ? "" : "s");
		}
		return privacyIconText;
	};

	var getPrivacyClass = function(tmp) {
		var privacyClass = "private";
		if (!tmp["private"]) {
			privacyClass = "public";
		} else if (tmp.shared) {
			privacyClass = "shared";
		}
		return privacyClass;
	};

	var flyoutAutosave = null;
	var saveFlyout = function(autosave) {
		if (typeof autosave == "undefined") autosave = false;
		if (flyoutAutosave) clearInterval(flyoutAutosave);

		if (ckeditor) {
			ckeditor.updateElement();
		}
		var edit = $("section#flyout .content.edit");
		var title = edit.find("input.title").val();

		if ($.trim(title).length === 0) {
			if (!autosave) return;
			$.prompt("The title can't be empty.");
			return false;
		}
		var id = edit.find("input.id").val();
		var tags = $.trim(edit.find("input.tags").val());

		var cb, url = "/save.php";
		if (!autosave) {
			cb = function(r) {
				if (isTouchDevice) return location.reload();

				for (var i = 0, l = data.length; i < l; i++) {
					if (data[i]._id != id) continue;
					data[i] = r;
					updateFlyout(i);
					updateList(i);
					reloadSidebar();
					break;
				}
			};
		} else {
			url = "/save.php?autosave";
			cb = function(r) {
				for (var i = 0, l = data.length; i < l; i++) {
					if (data[i]._id != id) continue;
					data[i] = r;
					updateList(i);

					var d = new Date();
					var h = d.getHours();
					if (h < 10) h = "0" + h;
					var m = d.getMinutes();
					if (m < 10) m = "0" + m;
					var s = d.getSeconds();
					if (s < 10) s = "0" + s;

					edit.find("li.autosaved").text("Autosaved at " + h + ":" + m + ":" + s).css("display", "inline-block");
					break;
				}
			};
		}
		var postData = {id: id, title: title, tags: tags, url: edit.find("input.url").val(), note: edit.find("textarea.note").val() };

		for (var i = 0, l = data.length; i < l; i++) {
			if (data[i]._id != id) continue;
			postData.oldTitle = data[i].title;
			postData.oldTags = data[i].tags;
			break;
		}

		// pass thru auth on a single page (for people who come from the newsletter)
		if (data.length == 1 && typeof data[0].p != "undefined") x.p = data[0].p;
		$.post(url, postData, cb);
		$('section#flyout').css("position", "fixed");
	};

	var updateList = function(i) {
		console.log(i)
		bulkedit = $("#bulkControls").is(":visible");
		var listItem, o, l, tmp;

		if (typeof i != "undefined") {
			tmp = data[i];
			listItem = $("ul.things li").eq(i);
			if (tmp.archived) {
				listItem.addClass("archived");
				listItem.find(".archive.tip").attr("title", "Unarchive");
			} else {
				listItem.removeClass("archived");
				listItem.find(".archive.tip").attr("title", "Move to archive");
			}
			if (tmp.pinned) {
				listItem.addClass("pinned");
				listItem.find(".pin.tip").attr("title", "Pin");
			} else {
				listItem.removeClass("pinned");
				listItem.find(".pin.tip").attr("title", "Unpin");
			}
			var allClasses = listItem[0].className.split(" ");
			for (o = 0, l = allClasses.length; o < l; o++) {
				if (allClasses[o].substr(0, 6) == "thing-") listItem.removeClass(allClasses[o]);
			}
			for (o = 0, l = tmp.classNames.length; o < l; o++) {
				listItem.addClass(tmp.classNames[o]);
			}
			listItem.addClass("thing-list-item");
			$("article", listItem).html(tmp.htmlTitle + ((typeof tmp.thinking != "undefined" && tmp.thinking) ? '<img src="/img/loading.gif" class="loader" /> <span class="smaller grey">thinking...</span>' : "")).highlight(getPref("currentQuery"));
			var tags = tmp.tags;
			if (getPref("underscoreToSpaces")) tags = tags.replace(/>(.*)</g, function(t) {
				return t.replace(/_/g, " ");
			});
			$("span.tags", listItem).html(tags);
			var p = $("span.public, span.private, span.shared", listItem);

			var privacyClass = getPrivacyClass(tmp);
			p.removeClass("private public shared").addClass(privacyClass).attr("title", getPrivacyTitle(tmp, true)).tipTip();
			if (privacyClass == "private") $("div.privacy", listItem).hide();
			else $("div.privacy", listItem).show();

			var bulk = $("input.bulk", listItem);
			bulk.prop("checked", typeof tmp.checked != "undefined" && tmp.checked);

			if (!tmp.canEdit) {
				bulk.hide();
			}
			return;
		}
		var actions, pinActions, active;

		if (typeof i == "undefined" && $('ul.things li.active').length !== 0) {
			active = $("ul.things li.active").index();
		}

		if (data.length === 0) {
			$("section#things ul").text("").append($("ul#noThingTemplate li").clone());
			return;
		}

		var list = $("section#things ul"), html = "", oldFirstId = $("section#flyout input.id").val(), firstId = false;
		for (j = 0, l = data.length; j < l; j++) {
			tmp = data[j];
			listItem = "<li class=\"thing-list-item";
			if (tmp.archived) listItem += ' archived';
			if (tmp.pinned) listItem += ' pinned';
			if (tmp.canEdit) listItem += ' canEdit';
			if (tmp.classNames) listItem += ' ' + tmp.classNames.join(" ");
			if (tmp.todoStatus) listItem += ' checked';
			listItem += '">';
			if (tmp.canEdit) listItem += '<span class="drag"></span>';
			var privacyIcon = getPrivacyIcon(tmp);
			listItem += '<div class="privacy"' + ((!tmp.shared || !tmp.shared.length) && tmp["private"] ? ' style="display: none"' : '') + '>' + privacyIcon + "</div>";
			listItem += '<input type="checkbox" name="bulk[]" value="' + tmp._id + '" class="bulk" ' + (typeof tmp.checked != "undefined" && tmp.checked ? ' checked="checked"' : '') + (tmp.canEdit ? '' : ' disabled="disabled"') + ' />';
			listItem += '<input type="checkbox" class="todo" ' + (typeof tmp.todoStatus != "undefined" && tmp.todoStatus ? ' checked="checked"' : '') + ' />';
			listItem += '<article>' + tmp.htmlTitle + ((typeof tmp.thinking != "undefined" && tmp.thinking) ? '<div class="loader"></div> <span class="smaller grey">thinking...</span>' : "") + "</article>";
			actions = pinActions = "";
			actions += privacyIcon;

			if (tmp.canEdit) {
				pinActions = "<span class=\"actions pin\"><a href=\"\" class=\"icn pin tip\" title=\""+(tmp.pinned ? "Unpin" : "Pin")+"\">&nbsp;</a></span>";
				actions += "<a href=\"\" class=\"icn edit tip\" title=\"Edit\">&nbsp;</a>";
				actions += "<a href=\"\" class=\"icn archive tip\" title=\""+(tmp.archived ? "Unarchive" : "Move to archive")+"\">&nbsp;</a>";
			}
			actions += "<a href=\"\" class=\"icn delete tip\" title=\"Delete\">&nbsp;</a>";

			var tags = tmp.tags;
			if (getPref("underscoreToSpaces")) tags = tags.replace(/>(.*)</g, function(t) {
				return t.replace(/_/g, " ");
			});

			listItem += "<sub class=\"meta\">" +
						"<span class=\"tags\">" + tags + "</span> <span data-t=\"" + tmp.date + "\" class=\"info grey dateTime\">just before</span>" +
						pinActions+
						"<span class=\"actions\">"+ actions + "</span>"+
					"</sub>";
			listItem += "</li>";

			html += listItem;
		}
		list.html(html);

		//check if element and/or edit-mode is active
		if (typeof i == 'undefined') i = active;
		if (typeof i != "undefined") {
			if ($('section#flyout .content.edit').is(":hidden")) {
				updateFlyout(i);
			}
			$('section#things li').eq(i).addClass('active');
		}

		$("section#things li article").highlight(getPref("currentQuery"));
		$("section#things li span.tags").highlight(getPref("currentQuery"));

		checkMore();
		if (typeof data[0] == "undefined") {
			$("section#flyout .content").hide();

		} /*else if (oldFirstId) {
			updateFlyout(0, false, false);
		}*/

		updateTimeLeft();
		$(window).trigger('things-list-updated', data);
	};

	var updatePublicPrivate = function(i) {
		var content = $("section#flyout section.content.display");
		var active = $("section#things li").eq(i).hasClass("active");
		var a = $("nav#sharing ul.internal-sharing>li>a>span"), pp = $("span.publicprivate", content), sh = $(".sharer", content), permalink = $("a.permalink", content);
		var tmp = data[i];
		if (active) {
			if (tmp.canEdit) $("nav#sharing ul.internal-sharing").show(); else $("nav#sharing ul.internal-sharing").hide();
			$("nav#sharing ul.internal-sharing ul a").removeClass("active");
		}
		var friends = tmp.shared ? tmp.shared.length - 1 : 0;

		var text = getPrivacyIconText(tmp);
		var privacyClass = getPrivacyClass(tmp);
		pp.text(text).removeClass("private public shared").addClass(privacyClass).attr("title", getPrivacyTitle(tmp, true)).tipTip();
		sh.removeClass("private public shared").addClass(privacyClass);
		if (!tmp.title) tmp.title = "";
		sh.find(".facebook,.twitter").data("url", location.protocol + "//" + location.host + tmp.permalink).data("href", location.protocol + "//" + location.host + tmp.permalink).data("text", $.trim(tmp.title.replace(/[|]/, "") + " " + ($.trim(tmp.textTags) ? "#" + tmp.textTags.split(" ").join(" #") : "")));
		permalink.attr("title", privacyClass == "private" ? "Private Permalink" : "Public Permalink: use this URL to share it with shared").tipTip();
		if (active) {
			a.text(text).removeClass("private public shared").addClass(privacyClass);
			$("nav#sharing ul.internal-sharing ul a." + privacyClass).addClass("active");
		}
	};

	var updateFlyout = function(i, showEdit, fade) {
		if (typeof showEdit == "undefined") showEdit = false;
		if (typeof data[i] == "undefined") return false;
		if (typeof fade == "undefined") fade = true;
		var tmp = data[i];

		removeQuickEdit();
		$("section#things li").eq(i).addClass("active").siblings().removeClass("active");
		var content = $("section#flyout section.content.display");
		var edit = $("section#flyout section.content.edit");

		edit.find("li.autosaved").hide();

		// save flyout is updated when the edit box is already visible: user has switched to another thing without saving
		if (edit.is(":visible")) saveFlyout(true); // autosave


		var show = showEdit ? edit : content;
		if (showEdit) $("section#flyout").removeClass("show-thing"); else $("section#flyout").addClass("show-thing");
		var showIt = function() {
			if (tmp.url) {
				$("h1.linked a", content).html(tmp.htmlTitle).attr("href", tmp.url).highlight(getPref("currentQuery"));
				$("h1.linked", content).show();
				$("h1.unlinked", content).hide();
			} else {
				$("h1.unlinked", content).html(tmp.htmlTitle).show().highlight(getPref("currentQuery"));
				$("h1.linked", content).hide();
			}
			var html = tmp.html;
			var tags = tmp.tags;
			if (getPref("underscoreToSpaces")) tags = tags.replace(/>(.*)</g, function(t) {
				return t.replace(/_/g, " ");
			});
			$("span.tags", content).html(tags).highlight(getPref("currentQuery"));
			$("span.dateTime", show).attr("data-t", tmp.date);
			if (tmp.htmlNote && $.trim(String(tmp.htmlNote).replace("<br>", "")).length) {
				html = tmp.htmlNote + (html ? "<br/><br/>" + html : "");
			}
			$("div.embed", content).html(html).highlight(getPref("currentQuery"));

			$("a.icn.pin", content).text(tmp.pinned ? "Unpin" : "Pin").attr("title", tmp.pinned ? "Unpin" : "Pin");
			$("a.icn.archive", content).text(tmp.archived ? "Unarchive" : "Archive").attr("title", tmp.archived ? "Unarchive" : "Move to archive");
			$("a.permalink", content).attr("href", tmp.permalink);
			if (tmp.canEdit) $("nav.actions").show(); else $("nav.actions").hide();
			updatePublicPrivate(i);

			$("input.title", edit).val(tmp.title);
			$("input.url", edit).val(tmp.url ? tmp.url : "");
			$("input.tags", edit).val(tmp.textTags + " ");
			$("input.id", edit).val(tmp._id);

			var n;
			if (tmp.note && tmp.html) {
				n = tmp.note + "<br/><br/>" + tmp.html;
			} else if (tmp.note) {
				n = tmp.note;
			} else if (tmp.html) {
				n = tmp.html;
			}

			if (n) {
				n = n.replace("http://www.youtube.com", "https://www.youtube.com");
				n = n.replace("http://vimeo.com", "https://vimeo.com");
			}

			$("textarea.note", edit).val(n);
			$("select[name=private]", edit).eq(0).attr("selectedIndex", tmp["private"] ? 0 : 1);

			updateTimeLeft();
			var afterShow = function() {
				if (showEdit) {
					if (getPref("editor") == "ckeditor") {
						if (!ckeditor) {
							CKEDITOR.replaceAll("note");
						}
						for (var instanceName in CKEDITOR.instances) {
							ckeditor = CKEDITOR.instances[instanceName];
							ckeditor.setData($("textarea.note", edit).val());
						}

						var resizeEditor = function() {
							var d = $("textarea.note");
							if (!ckeditor) return;
							var p = d.show().offset();
							d.hide();
							var h = Math.max(200, $(window).height() + document.body.scrollTop - p.top - 200);
							ckeditor.config.height = h;
							try {
								ckeditor.resize(ckeditor.config.width, ckeditor.config.height, true, false);
							} catch (e) {}
						};

					} else {
						var editor = $("textarea.note", edit).redactor({
							autoresize: false,
							fixed: true,
							buttons: [
								'html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|',
								'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
								'image', 'file', 'table', 'link', '|',
								'fontcolor', 'backcolor', '|', 'alignment', '|', 'horizontalrule'
							],
							allowedTags: ["form", "input", "button", "select", "option", "datalist", "output", "textarea", "fieldset", "legend",
								"section", "header", "hgroup", "aside", "footer", "article", "details", "nav", "progress", "time", "canvas",
								"code", "span", "div", "label", "a", "br", "p", "b", "i", "del", "strike", "u",
								"img", "video", "source", "track", "audio", "iframe", "object", "embed", "param", "blockquote",
								"mark", "cite", "small", "ul", "ol", "li", "hr", "dl", "dt", "dd", "sup", "sub",
								"big", "pre", "code", "figure", "figcaption", "strong", "em", "table", "tr", "td",
								"th", "tbody", "thead", "tfoot", "h1", "h2", "h3", "h4", "h5", "h6", "style"],

							plugins: ['fullscreen']
						});

						editor.redactor("set", $("textarea.note", edit).val());

						var resizeEditor = function() {
							var d = $(".redactor_editor");
							var p = d.offset();
							d.height(Math.max(200, $(window).height() + document.body.scrollTop - p.top - 135));

						};

					}

					if (flyoutAutosave) clearInterval(flyoutAutosave);
					flyoutAutosave = setInterval(function() {
						saveFlyout(true);
					}, 60000); // autosave every minute

					resizeEditor();
					if ($(window).scrollTop() == $(document).height() - $(window).height()) {
						$(window).scrollTop($(window).scrollTop()- 50);
					}
					if (!isTouchDevice) $(window).unbind("resize").resize(resizeEditor);
					$('#flyout .content.edit').scrollTop(0);
					$("input.title", edit).focus();
				}
			};
			if (fade) {
				show.fadeIn(20, afterShow);
			} else {
				show.show();
				afterShow();
			}
		};
		if (fade) {
			$(content).add(edit).filter(":visible").fadeOut(10, showIt);
		} else {
			$(content).add(edit).filter(":visible").hide();
			showIt();
		}
	};

	var pin = function(k, save) {
		lastPinned = -1;
		for (i = 0, l = data.length; i < l; i++) {
			if (!data[i].pinned) break;
			lastPinned = i;
		}
		data[k].pinned = lastPinned + 2;

		for (i = 0; i <= lastPinned; i++) {
			data[i].pinned = lastPinned - i + 1;
		}
		resortThings();

		if (typeof save == "undefined" || save) {
			for (i = 0, l = lastPinned + 1; i <= l; i++) {
				$.post("/save.php", {
					id: data[i]._id,
					pin: data[i].pinned
				}, saveThingCallback);
			}
			updateList();
			$(window).trigger('changePinned');
		}
	};

	var resortThings = function() {
		$("#tiptip_holder").hide();
		if (getPref("ignorePinningUnderAll") && !tag) {
			data.sort(function(a, b) {
				if (a.date == b.date) return 0;
				return a.date > b.date ? -1 : 1;
			});
		} else {
			data.sort(function(a, b) {
				if (a.pinned && !b.pinned) return -1;
				if (!a.pinned && b.pinned) return 1;
				if (a.pinned && b.pinned) return b.pinned - a.pinned;
				if (a.date == b.date) return 0;
				return a.date > b.date ? -1 : 1;
			});
		}
	};

	var showBulk = function() {
		var bulkControls = $("#bulkControls").show();

		var c = 0;
		var unarchive = bulkControls.find("button[name=unarchive]"),
			archive   = bulkControls.find("button[name=archive]"),
			unpin     = bulkControls.find("button[name=unpin]"),
			pin       = bulkControls.find("button[name=pin]"),
			publ      = bulkControls.find("button[name=public]"),
			priv      = bulkControls.find("button[name=private]");

		if ($("section#things li.archived").length) {// has things that are archived
			unarchive.parent().show(); // so offer the unarchive button
			c++;
		} else {
			unarchive.parent().hide();
		}

		if ($("section#things li:not(.archived)").length) { // has things that are not archived
			archive.parent().show(); // so offer the archive button
			c++;
		} else {
			archive.parent().hide();
		}

		if ($("section#things li.pinned").length) {// has things that are pinned
			unpin.parent().show(); // so offer the unpin button
			c++;
		} else {
			unpin.parent().hide();
		}

		if ($("section#things li:not(.pinned)").length) { // has things that are not pinned
			pin.parent().show(); // so offer the pin button
			c++;
		} else {
			pin.parent().hide();
		}

		if ($("section#things li span.private").length) { // has things that are private
			publ.parent().show(); // so offer the public button
			c++;
		} else {
			publ.parent().hide();
		}

		if ($("section#things li span.public").length) { // has things that are public
			priv.parent().show(); // so offer the private button
			c++;
		} else {
			priv.parent().hide();
		}

		if (c > 3) {
			publ.text("public");
			priv.text("private");
		} else {
			publ.text("make public");
			priv.text("make private");
		}

		if (c > 2) {
			$("#closeBulk").text("[x]");
		} else {
			$("#closeBulk").text("[x] close");
		}
		$("body").addClass("bulkedit");
		$("section#things li").addClass("bulkedit");
		$("#toggleAllChecked").prop("checked", false);
		$('#toggleBulk').addClass("active");
	};

	var hideBulk = function() {
		$("#bulkControls").hide();
		$("body").removeClass("bulkedit");
		$("section#things li").removeClass("bulkedit");
		$('#toggleBulk').removeClass("active");
	};

	var saveThingCallback = function (r, cb, err) {
		if (!r || !r._id) {
			if (typeof err == "function") err();
			return;
		}
		var oldPinned = false;
		for (var i = 0, l = data.length; i < l; i++) {
			oldPinned = data[i].pinned;

			if (data[i]._id != r._id) continue;

			data[i] = r;
			if (oldPinned != data[i].pinned) {
				$(window).trigger('changePinned');
			}

			updateFlyout(i);
			updatePublicPrivate(i);
			updateList(i);
		}
		if (typeof cb == "function") cb(r);
	};

	var saveThing = function (thing, cb, err) {
		$.post("/save.php", thing, function(r) {
			saveThingCallback(r, cb, err);
		});
	};

	var saveAfterHighlight = function() {
		var h = $("section#flyout .content.display div.embed");
		var i = $("section#things li.active").index();
		$.post("/save.php", {
			id: data[i]._id,
			title: data[i].title,
			note: h.html()
		}, saveThingCallback);
	};

	var toggleHighlight = function() {
		var h = $("section#flyout .content.display div.embed");
		if (h.hasClass("highlighter")) {
			h.removeClass("highlighter").getHighlighter().destroy();
		} else {
			h.addClass("highlighter").textHighlighter({
				onAfterHighlight: saveAfterHighlight
			});
			h.getHighlighter().doHighlight();
		}
	};
	var highlighterColor = 0;
	var highlighterColors = ["#ffff7b", "#ff6666", "#66ff66", "#6666ff"];

	var removeQuickEdit = function() {
		var h = $("section#flyout .content.display");
		if (h.hasClass("air")) {
			h.find("div.embed").redactor("destroy");
			h.removeClass("air");
			saveAfterHighlight();
		}
	};

	var tags = {};

	var prefs = {};
	var setPref = function(k, v) {
		prefs[k] = v;
	};
	var getPref = function(k, defaultValue) {
		if (typeof prefs[k] == "undefined") {
			if (typeof defaultValue != "undefined") {
				return defaultValue;
			}
			return false;
		}
		return prefs[k];
	};

	return {
		registerLazyLoad: registerLazyLoad,
		loadTag: loadTag,
		loadThings: loadThings,
		updateList: updateList,
		pin: pin,
		movePinned: function(from, to, saveAll) {
			var i, j, l, pin = false, tmp = data[to].pinned, lastPin;
			if (from == to) {
				// save
			} else if (from < to) {
				for (i = from, j = to; i <= j; i++) {
					lastPin = data[i].pinned;
					if (pin !== false) data[i].pinned = pin;
					pin = lastPin;
				}
				i = from; j = to;
			} else {
				for (i = from, j = to; i >= j; i--) {
					lastPin = data[i].pinned;
					if (pin !== false) data[i].pinned = pin;
					pin = lastPin;
				}
				i = to; j = from;
			}
			data[from].pinned = tmp;

			if (typeof saveAll != "undefined" && saveAll) {
				for (i = 0, l = data.length; i < l; i++) {
					if (!data[i].pinned) break;
					j = i;
				}
				i = 0;
			}

			for (; i <= j; i++) {
				$.post("/save.php", {
					id: data[i]._id,
					pin: data[i].pinned
				}, saveThingCallback);
			}


			resortThings();
			updateList();
			$(window).trigger('changePinned');

		},
		reloadSidebar: reloadSidebar,
		reloadThings: reloadThings,
		setPref: setPref,
		setPrefs: function(_prefs) {
			prefs = _prefs;
		},
		findThingById: function (_id) {
			for (var i=0, l = data.length; i < l; i++) {
				if (data[i]._id == _id) return data[i];
			}
			return false;
		},
		saveThing: saveThing,
		removeHighlight: function(el) {
			var h = $("section#flyout .content.display div.embed");
			var is_highlighted = h.hasClass("highlighter");
			if (!is_highlighted) h.textHighlighter();
			h.getHighlighter().removeHighlights(el);
			if (!is_highlighted) h.getHighlighter().destroy();
			saveAfterHighlight();
		},
		toggleHighlight: toggleHighlight,
		rotateColorHighlight: function() {
			var h = $("section#flyout .content.display div.embed");
			var is_highlighted = h.hasClass("highlighter");
			if (!is_highlighted) return;

			h.removeClass("color" + highlighterColor);
			highlighterColor += 1;
			if (highlighterColor >= highlighterColors.length) highlighterColor = 0;
			h.addClass("color" + highlighterColor);

			h.getHighlighter().setColor(highlighterColors[highlighterColor]);
		},
		removeQuickEdit: removeQuickEdit,
		toggleQuickEdit: function() {
			var h = $("section#flyout .content.display");
			if (!h.hasClass("air")) {
				h.addClass("air");
				var editor = h.find("div.embed").redactor({
					air: true,
					// airButtons: ['formatting', '|', 'bold', 'italic', 'deleted'],
					allowedTags: ["form", "input", "button", "select", "option", "datalist", "output", "textarea", "fieldset", "legend",
						"section", "header", "hgroup", "aside", "footer", "article", "details", "nav", "progress", "time", "canvas",
						"code", "span", "div", "label", "a", "br", "p", "b", "i", "del", "strike", "u",
						"img", "video", "source", "track", "audio", "iframe", "object", "embed", "param", "blockquote",
						"mark", "cite", "small", "ul", "ol", "li", "hr", "dl", "dt", "dd", "sup", "sub",
						"big", "pre", "code", "figure", "figcaption", "strong", "em", "table", "tr", "td",
						"th", "tbody", "thead", "tfoot", "h1", "h2", "h3", "h4", "h5", "h6", "style"],
					keyupCallback: function(e) {
						var keyCode = (e === null) ? event.keyCode : e.which;

						switch (keyCode) {
							case 27: // escape
								removeQuickEdit();
								return false;
						}
						return true;
					}
				});
			} else {
				removeQuickEdit();
			}
		},
		load: function(_data, _tags) {
			Thinkery.tags = _tags;
			if (getPref("currentQuery")) {
				$("ul.things li article").highlight(getPref("currentQuery"));
				$("ul.things li span.tags").highlight(getPref("currentQuery"));
			}

			originalData = data = _data;
			$('#main').attr('username', username);

			if (getPref("startInEdit")) {
				updateFlyout(0, true);
			}
			retrieveThings();
			// updateList();

			$(document).on("click", "nav#menu ul li a", function(e) {
				if (!Thinkery.mainListUrl) return true;
				var li = $(e.target).closest("li");
				if (li.closest("nav#filter").length) return true;

				var _tag = li.data("tag");

				if (!li.data("all") && (typeof _tag == "undefined" || !_tag)) {
					if (!tag) return true;
					location.href = "/" + username + "/" + tag + (e.target.search == "?archived" ? "?archived" : "");
					return false;
				}

				if (li.hasClass("smart")) _tag = "smart:" + _tag;
				if (li.data("subtag")) {
					loadTag(_tag, li.data("subtag"));
				} else {
					loadTag(_tag);
				}
				return false;
			});

			$(document).on("click", "button.ir", function(e) {
				var t = $(e.target);
				if (t.hasClass("add")) {
					if (!$.trim($('#search-input input[name=thing]').val()).length) return false;
					t.removeClass("force-add");
					t.closest("form").submit();
					return false;
				}
			});


			$(document).on("click", "nav#menu ul li span.settings, nav#menu #addsmartfolder", function(e) {
				$.prompt($("#smartfolderTemplate").html(), {
					buttons: {"Save": true, "Cancel": false},
					classes: 'share',
					loaded: function(v) {
						var buttons = $("#jqi table button");
						function firstRemoveButton() {
							var rows = $("tr", "#jqi table");
							var firstRemove = rows.find("button.remove");
							if (rows.length == 1) firstRemove.prop("disabled", true);
							else firstRemove.prop("disabled", false);
						}
						firstRemoveButton();

						buttons.on("click", function(r) {
							var button = $(r.target);
							if (button.hasClass("add")) {
								$("#jqi table").append($("#smartfolderTemplate tr").clone());
							} else if (button.hasClass("remove")) {
								button.closest("tr").remove();
							}
							firstRemoveButton();
							return false;
						});

						$(document).on("change", "#jqi table select.type", function() {
							var div, nextTd = $(this).closest("td").next("td");
							switch (this.selectedIndex) {
								case 0: case 1:
									div = nextTd.find("div.tag");
									break;
								case 2: case 3:
									div = nextTd.find("div.date");
									break;
								case 4:
									div = nextTd.find("div.note");
									break;
							}

							div.show().siblings().hide();
						});
					},
					submit: function(e, buttonClicked) {
						if (!buttonClicked) return;

						var rows = $("tr", "#jqi table");
						for (var i = 0, l = rows.length; i < l; i++) {
							var row = rows.eq(i);

						}

						$.post("/smartfolder.php", $("#jqi form").serialize(), function(r) {
							alert(r);
						});

						return false;

					}
				});
				return false;
			});

			$(document).on("click", "a[data-tag]", function(e) {
				if (!Thinkery.mainListUrl) return true;
				var _tag = $(e.target).data("tag");
				if (typeof _tag == "undefined" || !_tag) return true;
				var _subtag = $(e.target).data("subtag");
				if (typeof _subtag != "undefined" || !_subtag) {
					loadTag(_tag, _subtag);
				} else {
					loadTag(_tag);
				}
				return false;
			});

			$(document).on("click", "nav#menu .moretags a", function(e) {
				var $this = $(this);
				var expanded = !$this.hasClass('expanded');
				var p = $this.parent();
				var loaded = p.hasClass('loaded');

				if (expanded) {
					$this.html("Fewer tags &uarr;");
					this.className = "expanded";

					if (!loaded) {
						$this.html("<img src='/img/loading.gif' />");
						reloadSidebar(true);
					} else {
						$("ul#nonFavoriteTags li").show();
					}
				} else {
					$this.html("All tags &darr;");
					this.className = "collapsed";
					$("ul#nonFavoriteTags li:gt(9)").hide();
				}

				$('nav#menu').css("position", expanded ? "absolute" : "fixed");
				return false;
			});

			$(document).on("click", "ul.things li", function(e) {
				var lis = $(this).closest("section").find("li");
				var index = lis.index(this);
				if (e.shiftKey) {
					var i = lis.index("li.active");
					var j = index;
					if (j < i) {
						var k = j;
						j = i;
						i = k;
					}
					var bulks = $("section#things li input.bulk");
					if ($("#bulkControls").is(":hidden")) bulks.prop("checked", false);
					for (; i <= j; i++) {
						if (typeof data == "undefined" || typeof data[i] == "undefined" || typeof data[i].canEdit == "undefined" || !data[i].canEdit) {
							bulks.eq(i).prop("disabled", true);
							continue;
						}
						data[i].checked = true;
						bulks.eq(i).prop("checked", true);
					}
					showBulk();
					return false;
				}

				var flyout = $("section#flyout");
				if (!flyout.length || flyout.is(":hidden")) {
					if (typeof data[index] == "undefined") return;
					location.href = data[index].permalink;
					return;
				}

				updateFlyout(index);
			});

			$(document).on("click", "ul.things li input.todo, section#flyout input.todo", function(e) {
				var li = $(this).closest("section#flyout").length ? $(this).closest("hgroup").find("h1") : $(this).closest("li");
				var lis = $(this).closest("section").find("li");
				var i = $(this).closest("section#flyout").length ? $("ul.things li.active").index() : lis.index($(this).closest("li"));
				data[i].todoStatus = this.checked;
				if (this.checked) li.addClass("checked"); else li.removeClass("checked");
				var postData = {id: data[i]._id, "todo": this.checked ? "checked" : "not-checked" };
				if (typeof data[i].p != "undefined") postData.p = data[i].p; // pass thru auth
				$.post("/save.php", postData, saveThingCallback);
				if (!$("section#flyout").length) { // dashboard
					e.stopPropagation()
				}
			});

			$(document).on("click", "section#things li input.bulk", function(e) {
				data[$(this).closest("li").index()].checked = this.checked;
			});

			if (isTouchDevice) {
				var touchStartThing = false, touchStartTag = false, touchMoves = 0;
				$(document).on("touchstart", "ul.things li", function(e) {
					var lis = $(this).closest("section").find("li");

					touchStartThing = lis.index(this);
					touchMoves = 3;
				});
				$(document).on("touchmove", "ul.things li", function(e) {
					if (--touchMoves > 0) return;
					touchStartThing = false;
				});
				$(document).on("touchend", "ul.things li", function(e) {
					if (touchStartThing === false) return;

					var lis = $(this).closest("section").find("li");
					var index = lis.index(this);

					if (isIPad) {
						updateFlyout(index);
					} else {
						location.href = data[index].permalink;
					}
				});
				$(document).on("touchstart touchmove", "a[data-tag]", function(e) {
					touchStartThing = false;
				});
				$(document).on("touchend", "a[data-tag]", function(e) {
					e.preventDefault();
					if (!Thinkery.mainListUrl) return true;
					touchStartThing = false;
					var _tag = $(e.target).data("tag");
					if (typeof _tag == "undefined" || !_tag) return true;
					var _subtag = $(e.target).data("subtag");
					if (typeof _subtag != "undefined" || !_subtag) {
						loadTag(_tag, _subtag);
					} else {
						loadTag(_tag);
					}
					return false;
				});
			}

			$(document).on("click", "ul.things li a.edit", function(e) {
				var lis = $(this).closest("section").find("li");
				var index = lis.index($(this).closest("li"));

				e.preventDefault();
				if ($("section#flyout").length) {
					updateFlyout(index, /* edit */ true);
				} else {
					location.href = data[index].permalink + "?edit";
				}
				return false;
			});

			$(document).on("click", "section#flyout .content .icn.edit", function(e)  {
				e.preventDefault();
				$("ul.things li.active a.edit").click();
				return false;
			});

			$(document).on("click", "section#flyout .content.edit nav.actions button.cancel", function()  {
				if (isTouchDevice) return location.reload();
				$('section#flyout .content.display').show();
				$('section#flyout .content.edit').hide();
				$('section#flyout').addClass("show-thing");
				return false;
			});

			var updatePrivacy = function(r) {
				for (var i = 0, l = data.length; i < l; i++) {
					if (data[i]._id != r._id) continue;
					data[i] = r;
					updateFlyout(i);
					updateList(i);
					updatePublicPrivate(i);
					break;
				}
			};

			$(document).on("click", "section#flyout .meta .publicprivate, ul.things li span.private, ul.things li span.public, ul.things li span.shared", function(e) {
				var lis = $(this).closest("section").find("li");
				var i = $(this).closest("section#flyout").length ? $("ul.things li.active").index() : lis.index($(this).closest("li"));
				if (!data[i].canEdit) return false;
				var newPrivate = !data[i]["private"];
				var id = data[i]._id;
				$("#tiptip_holder").hide();
				$.post("/save.php", {id: id, "private": newPrivate ? "private" : "public" }, updatePrivacy);
			});

			$(document).on("click", "nav#sharing li", function(e) {
				var $this = $(this);
				if ($this.hasClass("dir")) return false;

				var i = $("ul.things li.active").index();
				if (!data[i].canEdit) return false;

				var newPrivate = true;
				var id = data[i]._id;
				switch ($this.text()) {

					case "Public":
						newPrivate = false;
					case "Private":
						$.post("/save.php", {id: id, "private": newPrivate ? "private" : "public" }, updatePrivacy);
						break;

					case "Friends":
						$.prompt($('#shareTagPopup').html(), {
							buttons: {},
							classes: 'share',
							loaded: function(v) {
								var buttons = $("button", "#jqi");
								$("input[name=users]:visible").focus().keyup(function(e) {
									if (e.altKey || e.metaKey || e.ctrlKey) return true;
									var keyCode = (e === null) ? event.keyCode : e.which;
									if (keyCode == 13) {
										// buttons.eq(0).click();
										return false;
									}
								});
								buttons.eq(0).click(function() {
									var users = $("input[name=users]:visible").val();
									$.post("/share.php", {id: id, users: users, allowChange: $("input[name=allowChange]:visible").is(":checked")}, function(v) {
										$.prompt.close();
										if (typeof v.thing != "undefined") {
											data[i] = v.thing;
											updateFlyout(i);
											updateList(i);
											updatePublicPrivate(i);
										}
										$.prompt("Now these users can view this item: " + v.users.join(", "));
									});
									return false;
								});
								buttons.eq(1).click(function() {
									$.prompt.close();
									return false;
								});
							}
						});
						break;

				}
				return false;
			});

			$(document).on("click", "section#flyout .content.edit nav.actions button.save", function()  {
				saveFlyout();
				return false;
			});

			$(document).on("click", "section#flyout a.delete, ul.things li a.delete, section#flyout a.archive, ul.things li a.archive", function() {
				var question, buttons, del = $(this).hasClass("delete");
				var lis = $(this).closest("section").find("li");
				var i = $(this).closest("section#flyout").length ? $("ul.things li.active").index() : lis.index($(this).closest("li"));
				if (del) {
					question = "<h1 class='border'>Delete</h1>Really delete <i>" + escapeHtml(data[i].title) + "</i>?";
					buttons = {"Yes, delete it": true, "Cancel": false};
				} else {
					if (data[i].archived) {
						question = "<h1 class='border'>Unarchive</h1>Really unarchive <i>" + escapeHtml(data[i].title) + "</i>?";
						buttons = {"Yes, unarchive it": true, "Cancel": false};
					} else {
						question = "<h1 class='border'>Archive</h1>Really archive <i>" + escapeHtml(data[i].title) + "</i>?";
						buttons = {"Yes, archive it": true, "Cancel": false};
					}
				}
				$.prompt(question, {
					buttons: buttons,
					classes: del ? "delete" : "archive",
					submit: function(e, buttonClicked) {
						if (!buttonClicked || typeof data[i] == "undefined") return;
						$.post(del ? "/delete.php" : (data[i].archived ? "/archive.php?unarchive" : "/archive.php"), {id: data[i]._id}, function(r) {
							if (getPref("currentPage") == "dashboard") {
								if (del) { // item doesn't exist anymore, go to all things
									location.reload();
									return;
								}
								data[i].archived = !data[i].archived;
							} else if (getPref("currentPage") == "single") {
								if (del) { // item doesn't exist anymore, go to all things
									location.href = "/" + username;
									return;
								}
								data[i].archived = !data[i].archived;
							} else {
								if (!del && data[i].archived) {
									data[i].archived = false;
								} else {
									data.splice(i, 1);
								}
							}

							reloadSidebar();
							updateList();
							if (!$("ul.things li").length) {
								$("ul.things").append("<li>No Things found.</li>");
								$("section#flyout section.content.display").css("visibility", "hidden");
							} else {
								var el = $("ul.things li").eq(i).click();
								scrollIntoView(el);
							}
						});
					}
				});
				return false;
			});

			$(document).on("click", "section#flyout a.pin, section#things li a.pin", function() {
				var i, l, k = $(this).closest("section#flyout").length ? $("section#things li.active").index() : $(this).closest("li").index();
				if (data[k].pinned) { // unpin
					for (i = 0, l = k; i < l; i++) {
						if (!data[i].pinned) break;
						data[i].pinned -= 1;
					}

					$.post("/save.php", {id: data[k]._id, pin: 0}, function(r) {
						saveThingCallback(r);
						resortThings();
						updateList();
						$(window).trigger('changePinned');
					});

				} else {
					pin(k);
				}

				return false;
			});

			var lastQuery, lastAjax, lastTimeout = null, beforeQuery = null, tmpQuery, ajaxQueryOld, ajaxQueryCurrent;
			var searchInput = $('#search-input'), searchAdd = $("#searchadd"), searchField = $("#search");
			var btns = {
				bir: $("button.ir"),
				hint: $("sub.hint"),
				results: $("sub.results")
			};
			if ($("button.ir.force-add").length) btns.bir = $();


			searchAdd.thinkeryAutocompleter({mixed: true, tags: Thinkery.tags}).on("stop",function() {
				if (lastAjax) lastAjax.abort();
				clearTimeout(lastTimeout);
				searchField.removeClass('searching');
			}).on("keydown keyup tag-autocompleted", function() {

				lastQuery = this.value;

				if ($.trim(this.value) === "") {
					searchAdd.trigger('stop');
					btns.bir.addClass("search").removeClass("add").removeClass("searchadd");
					btns.hint.addClass("hidden");
					btns.results.addClass("hidden");
					data = originalData;
					setPref("currentQuery", false);
					lastQuery = false;
					updateList();
					$(this).trigger("hide-autocomplete");
					return true;
				}
				$("sub.message").remove();

				if (beforeQuery == lastQuery) return;
				tmpQuery = beforeQuery;
				beforeQuery = lastQuery;

				// vorherigen ajax request abbrechen wenn neue suche angefangen wird
				searchAdd.trigger('stop');

				lastTimeout = setTimeout(function() {
					searchAdd.focus();

					ajaxQueryCurrent = lastQuery;
					if (ajaxQueryOld == ajaxQueryCurrent) return;


					searchAdd.trigger('stop');
					searchField.addClass('searching');
					lastAjax = $.post("/search.php", { q: lastQuery }, function(r) {

						ajaxQueryOld = ajaxQueryCurrent;

						// if (lastQuery != r.q) return;
						data = r.result;
						setPref("currentQuery", r.q);
						if (!data || !data.length) {
							btns.bir.addClass("add").removeClass("search").removeClass("searchadd");
							btns.hint.removeClass("hidden");
							btns.results.addClass("hidden");
						} else {
							btns.bir.addClass("searchadd").removeClass("add").removeClass("search");
							btns.hint.addClass("hidden");
							btns.results.removeClass("hidden").text(
								'Search results for "' + (lastQuery.length > 40 ? "..." + lastQuery.substr(lastQuery.length - 20) : lastQuery) + '":'
							);
						}
						updateList();
						retrieveThings();
						searchField.removeClass('searching');

						ajaxQueryOld = ajaxQueryCurrent;

					});

				}, 100);

			});

			$(document).on("click", "#toggleBulk", function(e) {
				var bulkControls = $("#bulkControls");
				if (bulkControls.is(":visible")) {
					hideBulk();
				} else {
					showBulk();
				}
				return false;
			});

			$("#closeBulk").click(function() {
				hideBulk();
				return false;
			});

			$("#toggleAllChecked").click(function(e) {
				var ch = this.checked;
				$("section#things li input.bulk").each(function(i) {
					if (!data[i].canEdit) return true; // continue;
					if (e.shiftKey) this.checked = !this.checked;
					else this.checked = ch;
					data[i].checked = this.checked;
				});
			});

			$("button.bulk").click(function() {
				var checked = $("section#things li:has(input.bulk:checked)");
				var el = this;

				var formSubmit = function(r) {
					if (r == "error" || typeof(r) == "string") {
						$.prompt("An error was reported from the server, please try again." /* + r */);
						return;
					}

					var i, ids = {};
					for (i = 0, l = r.length; i < l; i++) {
						ids[r[i]._id] = r[i];
					}

					var activeIndex = $("section#things li.active").index();
					var updateWholeList = false;
					for (i = data.length - 1; i >= 0; i--) {
						var j = data[i]._id;
						if (typeof ids[j] == "undefined") continue;
						if (typeof ids[j].deleted != "undefined" && ids[j].deleted) {
							data.splice(i, 1);
							updateWholeList = true;
							continue;
						}
						data[i] = ids[j];
						data[i].checked = true;
						updateList(i);
						if (activeIndex == i) {
							updatePublicPrivate(i);
							updateFlyout(i);
						}
					}
					if (updateWholeList) {
						updateList();
					}
					if (!(el.name == "public" || el.name == "private")) {
						reloadSidebar();
					}
					if (data.length) {
						showBulk();
					} else {
						if ($.trim(tag) !== "") loadTag("");
						hideBulk();
					}
				};

				var sendForm = function() {
					var form = $("form[name=bulk]");
					$.post(form.attr("action"), form.serialize() + "&" + el.name + "=" + encodeURIComponent(el.value), formSubmit);
				};
				var t, un;

				switch (this.name) {
					case "add-tag":
						if (!checked.length) {
							action = "add a tag to";
							break;
						}
						$.prompt('<h1 class="border">Add which tag?</h1><input type="text" name="add-tag" data-only-one="1" /><div class="status"></div>', {
							buttons: { "Add tag": true, "Cancel": false},
							loaded: function(v) {
								$("input[name=add-tag]:visible").focus().keyup(function(e) {
									if (e.altKey || e.metaKey || e.ctrlKey) return true;
									var keyCode = (e === null) ? event.keyCode : e.which;
									if (keyCode == 13) {
										$(this).closest(".jqi").find("button").eq(0).click();
									}
								});
							},
							submit: function(e, buttonClicked, message, fields) {
								if (!buttonClicked) return;
								var input = $("input[name=add-tag]:visible");
								if ($.trim(input.val()) === "") {
									input.closest(".jqi").find("div.status").addClass("error").html("Please enter a tag.");
									input.focus();
									return false;
								}
								el.value = fields["add-tag"];
								sendForm();
								return true;
							}
						});
						return false;

					case "remove-tag":
						if (!checked.length) {
							action = "remove a tag from";
							break;
						}

						$.prompt('<h1 class="border">Remove which tag?</h1><input type="text" name="remove-tag" data-only-one="1" /><div class="status"></div>', {
							buttons: { "Remove tag": true, "Cancel": false},
							loaded: function(v) {
								$("input[name=remove-tag]:visible").focus().keyup(function(e) {
									if (e.altKey || e.metaKey || e.ctrlKey) return true;
									var keyCode = (e === null) ? event.keyCode : e.which;
									var jqi = $(this).closest(".jqi");
									if (keyCode == 13) {
										jqi.find("button").eq(0).click();
										return;
									}

									var affected = checked.find("span.tags a[data-tag=\"" + $.trim(this.value).replace('"', '\\"') +  "\"]").length;
									if (!affected) {
										$("div.status", jqi).removeClass("ok").addClass("error").text("None of your selected things have that tag.");
									} else {
										$("div.status", jqi).removeClass("error").addClass("ok").text(affected + " selected thing" + (affected == 1 ? " has" : "s have") + " this tag.");
									}

								});
							},
							submit: function(e, buttonClicked, message, fields) {
								if (!buttonClicked) return;
								var input = $("input[name=remove-tag]:visible");
								input.val($.trim(input.val()));
								if ($.trim(input.val()) === "") {
									input.closest(".jqi").find("div.status").addClass("error").html("Please enter a tag.");
									input.focus();
									return false;
								}
								var affected = checked.find("span.tags a[data-tag=\"" + input.val().replace('"', '\\"') +  "\"]").length;
								if (!affected) return false;
								el.value = fields["remove-tag"];
								sendForm();
								return true;
							}
						});
						return false;

					case "delete":
						if (!checked.length) {
							action = "delete";
							break;
						}
						t = checked.length + " thing" + (checked.length == 1 ? "" : "s");
						var buttons = {};
						buttons["Yes, delete " + t] = true;
						buttons["Cancel"] = false;
						$.prompt("<h1 class='Delete'></h1>This will delete " + t + ". This cannot be undone. Are you sure?", {
							buttons: buttons,
							classes: "delete",
							submit: function(e, buttonClicked) {
								if (!buttonClicked) return;
								sendForm();
							}
						});
						return false;

					case "public": action = "publish"; break;
					case "private": action = "make private"; break;

					case "archive":
					case "unarchive":
						action = this.name;
						if (!checked.length) {
							break;
						}
						t = checked.length + " thing" + (checked.length == 1 ? "" : "s");
						un = checked.filter(".archived").length > 0 ? "un" : "";
						$.prompt(t + " ha" + (checked.length == 1 ? "s" : "ve") + " been " + un + "archived.");
						checked.toggleClass("archived");
						showBulk();
						break;

					case "pin":
					case "unpin":
						action = this.name;
						if (!checked.length) {
							break;
						}
						t = checked.length + " thing" + (checked.length == 1 ? "" : "s");
						un = checked.filter(".pinned").length > 0 ? "un" : "";
						$.prompt(t + " ha" + (checked.length == 1 ? "s" : "ve") + " been " + un + "pinned.");
						checked.toggleClass("archived");
						showBulk();
						break;
				}

				if (!checked.length) {
					$.prompt("You haven't marked any things to " + action + ".");
					return false;
				}

				sendForm();
				return false;
			});
		},
		showBulk: showBulk,
		hideBulk: hideBulk,
		tag: function(t, st) {
			tag = t;
			if (typeof st != "undefined") subtag = st;
		}
	};
})( jQuery );

jQuery( function( $) {

	// Show/Hide Tags in sidebar and save the state in a cookie
	var favoriteTags = $("#favoriteTags");
	var nonFavoriteTags = $("#nonFavoriteTags");
	var toggleElementStatus = "open";

	if ($.cookie("favoriteTags") == 'closed') {
		favoriteTags.hide();
		favoriteTags.prev().toggleClass("closed");
	}

	if ($.cookie("nonFavoriteTags") == 'closed') {
		nonFavoriteTags.hide();
		nonFavoriteTags.prev().toggleClass("closed");
	}

	$(document).on("click", ".toggle", function () {
		$(this).toggleClass("closed").next().slideToggle("fast");

		if ($(this).hasClass("closed")) {
			toggleElementStatus = "closed";
		} else {
			toggleElementStatus = "open";
		}
		$.cookie($(this).next().attr("id"), toggleElementStatus);
	});

	$(document).on("mouseup", ".highlighted", function (e) {
		if (e.altKey) Thinkery.removeHighlight(this);
	});

	if ($("body").is(".targetBlank")) {
		$(document).on("click", "a", function() {
			var $this = $(this);
			if (!$this.attr("href").match(/^https?:/)) return true;
			$this.attr('target','_blank');
		});
	}

	var searchInput = $('#search-input input[name=thing]');
	var placeholder = $("#search-input #placeholder");
	if (!isTouchDevice) {
		placeholder.click(function() {
			$(this).hide();
			searchInput.focus();
			return false;
		});
	}

	if (isIPad) {
		$('#search-input').css("marginLeft", "40px");
		searchInput.css("width", "80%");
	}

	var makeFavoriteTag = function(e) {
		var el = $(e.target);
		var li = el.closest("li");
		var tag = li.data("tag");
		if (li.closest("ul").is("#favoriteTags")) {
			li.appendTo("ul#nonFavoriteTags");
			$.post("/favorite-tag.php", {remove: tag});
		} else {
			li.appendTo("ul#favoriteTags");
			$.post("/favorite-tag.php", {add: tag});
		}
		var ul = $("ul#favoriteTags");
		if (ul.children().length) ul.show(); else ul.hide();
	};

	var tagSettingsHtml = $('#tagSettings').html();
	$('#tagSettings').text("");
	$(document).on("click", "ul.tags .settings", function(e) {
		var li = $(this).closest("li"), _tag = li.data("tag");
		$.prompt(tagSettingsHtml, {
			buttons: {
				Save: true,
				Cancel: false
			},
			loaded: function(v) {
				var jqi = $(".jqi:visible").addClass("tagSettings");
				jqi.find("h3").html("Settings for the tag <strong>" + _tag + "</strong>");
				jqi.find("input[name=favorite]").attr("checked", li.closest("#favoriteTags").length > 0);
				jqi.find("input[name=todo]").attr("checked", li.data("todo"));
				jqi.find("input[name=color]").val(li.data("color")).minicolors({
					control: "wheel"
				});
			},
			submit: function(e, buttonClicked, message, fields) {
				if (!buttonClicked) return;

				var jqi = $(".jqi:visible");
				var data = {
					tag: _tag,
					color: jqi.find("input[name=color]").val()
				};
				if (jqi.find("input[name=favorite]").is(":checked")) data["favorite"] = 1;

				if (jqi.find("input[name=todo]").is(":checked")) data["todo"] = 1;

				var cb;
				if (data["color"] != li.data("color")) {
					cb = function() {
						location.reload();
					};
				}
				if (!cb && (
					(li.data("todo") && typeof data["todo"] == "undefined") || (!li.data("todo") && data["todo"])
				)) {
					cb = function() {
						Thinkery.reloadThings();
					};
				}

				Thinkery.reloadSidebar(false, data, cb);

				return true;
			}
		});
		return false;
	});


	var hidePlaceholderHelper = function(e) {
		if (e.altKey || e.metaKey || e.ctrlKey) return true;
		var keyCode = (e === null) ? event.keyCode : e.which;
		switch (keyCode) {
			case 38: // up
				break;
			case 40: // down
				this.blur();
				//if (keyCode == 40) {
				//	el = $("section#things li.active").next().click();
				//	scrollIntoView(el);
				//}
				return true;
		}

		if ($.trim(this.value) === "") {
			if (!isTouchDevice && placeholder.is(":hidden")) {
				placeholder.show();
			}
			return true;
		}
		if (placeholder.is(":visible")) {
			placeholder.hide();
		}
		return true;
	};

	var getUrlParameter = function(name) {
		name = name.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");
		var regexS = "[\\?&]" + name + "=([^&#]*)";
		var regex = new RegExp(regexS);
		var results = regex.exec(location.href);
		if (!results) return false;
		return results[1];
	};

	searchInput.focus(hidePlaceholderHelper).keyup(hidePlaceholderHelper);
	hidePlaceholderHelper({which: 0});
	searchInput.closest("form").submit(function(e) {
		if (!$.trim(searchInput.val()).length) return false;
		if ($("button.ir.force-add").length && !e.metaKey) {
			return false;
		}

	});

	var hover = {
		"ul.things li": {
			"": {
				"-webkit-transition": "background 250ms linear",
				"-moz-transition": "background 250ms linear",
				"-o-transition": "background 250ms linear",
				transition: "background 250ms linear",
				cursor: "pointer"
			},
			".actions": {
				"-thinkery-timeout": 0,
				display: "inline-block",
				margin: 0,
				opacity: 1
			},
			".actions a": {
				border: 0,
				margin: "0 0.5em 0 0",
				display: "inline-block"
			},
			".drag": {
				"-thinkery-timeout": 400,
				display: "block"
			}
		}
	};
	var selector, subselector, hoverTimeouts = {};
	if (isTouchDevice) { // apply some hovers on touch device
		for (selector in hover) {
			for (subselector in hover[selector]) {
				if (!hover[selector][subselector]['-thinkery-mobile']) continue;
				$(this).find(subselector).css(hover[selector][subselector]).addClass("hover");
			}
		}
	} else {
		for (selector in hover) {
			$(document).on("mouseenter", selector, function() {
				var t = $(this);
				for (subselector in hover[selector]) {
					if (hover[selector][subselector]["-thinkery-timeout"]) {
						(function(selector, subselector) {
							hoverTimeouts[selector + subselector] = setTimeout(function() {
								if (subselector !== "") {
									t.find(subselector).css(hover[selector][subselector]).end();
								}
								else t.css(hover[selector][subselector]);
							}, hover[selector][subselector]["-thinkery-timeout"]);
						})(selector, subselector);
					} else {
						if (subselector !== "") t.find(subselector).css(hover[selector][subselector]).addClass("hover").end();
						else t.css(hover[selector][subselector]).addClass("hover");
					}
				}
			});
			$(document).on("mouseleave", selector, function() {
				for (var subselector in hover[selector]) {
					var css = {};
					for (var c in hover[selector][subselector]) {
						if (c.indexOf("transition") > 0) css[c] = hover[selector][subselector];
						else css[c] = "";
					}
					if (hoverTimeouts[selector + subselector]) clearTimeout(hoverTimeouts[selector + subselector]);
					if (subselector !== "") $(this).find(subselector).css(css).removeClass("hover").end();
					else $(this).css(css).removeClass("hover");
				}
			});
		}
	}


	Thinkery.registerLazyLoad();

	$(document).keydown(function(e) {
		var keyCode = (e === null) ? event.keyCode : e.which;
		var target = $(e.target);
		if (target.is(".redactor_editor")) return true;
		var forceAdd = false;
		if (e.metaKey && keyCode == 13 && $("button.ir.force-add").length) {
			e.metaKey = false;
			forceAdd = true;
		}
		if (e.altKey || e.metaKey || e.ctrlKey) return true;
		var autocompleteDiv = $('.tag-autocomplete:visible');
		var el;

		switch (keyCode) {
			case 27: // escape
				var display = $('section#flyout .content.display');

				if (display.hasClass("air")) {
					Thinkery.removeQuickEdit();
					return false;
				}

				if (display.is(":hidden")) {
					display.show();
					$('section#flyout .content.edit').hide();
				}
				if ($("#jqi").is(":visible")) {
					$.prompt.close();
					return false;
				}

				if ($("#bulkControls").is(":visible")) {
					Thinkery.hideBulk();
					return false;
				}
				if (!autocompleteDiv.is(':hidden')) {
					autocompleteDiv.empty().hide();
				}
				if (searchInput.val() !== "") searchInput.trigger('stop').val("").keyup();

				return false;
		}

		if (autocompleteDiv.size() !== 0 && !autocompleteDiv.is(':hidden') && $("#searchadd").data('tag.added') !== true && autocompleteDiv.find('li').size() !== 0) {
			var t;
			switch (keyCode) {
				case 13: // enter
				case 9: // tab
					// load tag
					t = autocompleteDiv.find("li.active").attr('data-tag');
					if (t && autocompleteDiv.find("li.active").size()) {
						searchInput.trigger('tag-autocomplete', t);
						return false;
					}
					break;
				case 38: // up
					// top
					if (autocompleteDiv.find("li.active").index() === 0) {
						searchInput.focus();
						return true;
					}
					if (autocompleteDiv.find("li").size() == 1) {
						t = autocompleteDiv.find("li").attr('data-tag');
						searchInput.trigger('tag-autocomplete', t);
						return false;
					}
					autocompleteDiv.find('li.active').removeClass('active').prev().addClass('active');
					return false;
				case 40: // down
					if (autocompleteDiv.find("li").size() == 1) {
						t = autocompleteDiv.find("li").attr('data-tag');
						searchInput.trigger('tag-autocomplete', t);
						return false;
					}
					if (autocompleteDiv.find('li.active').next().size()) {
						autocompleteDiv.find('li.active').removeClass('active').next().addClass('active');
					}
					return false;
			}
			// return;
		}

		if (target.is("input,select,textarea,button") && target.is(":visible")) {
			if (!target.is(searchInput) || keyCode > 20) {
				return true;
			}
		}

		switch (keyCode) {
			case 9: // tab
				// searchInput.keyup();
				return false;
			case 13: // enter
				if ($.trim(searchInput.val()).length > 0) {
					searchInput.trigger('stop');
					if (forceAdd) {
						$("button.ir.force-add").removeClass("force-add");
						target.closest("form").submit();
						return true;
					}
					if ($("button.ir.force-add").length) {
						return false;
					}
					return true;
				}

				// for hitting enter on navigation
				var navEL = $("nav#menu li.active");
				if (navEL.index() !== -1) {
					var aNav = navEL.children(":first");
					location.href = aNav.attr('href');
					return false;
				}

				var link = $("div.embed a");
				if (!link.length) {
					link = $("section#flyout section.content.edit input.url").val();
					if (link) {
						location.href = link;
					}
					return false;
				}
				location.href = link.eq(0).attr("href");
				return false;
			case 75: // "k"
			case 38: // up
				if ($("ul.things li.active").index() === 0) {
					searchInput.focus();
					return true;
				}

				var thingsEl = $("ul.things li.active");
				var tagsEl = $("nav#menu li.active");
				if (thingsEl.index() === -1) {
					var tagsPrevEl = tagsEl.prev();
					if (tagsEl.index() === 0) {
						tagsPrevEl = tagsEl.parent().prev();
						while (!tagsPrevEl.is("div")) {
							if (tagsPrevEl.is("ul")) {
								tagsPrevEl = tagsPrevEl.children(":last");
								break;
							}
							tagsPrevEl = tagsPrevEl.prev();
						}
						if (tagsPrevEl.is("div")) {
							return false;
						}
					}
					tagsPrevEl.addClass("active");
					tagsEl.removeClass("active");
					return false;
				}

				scrollIntoView(thingsEl.prev().click());
				return false;

			case 74: // "j" - http://stackoverflow.com/questions/6553758/in-vim-why-is-j-used-for-down-and-k-for-up
			case 40: // down
				var thingsEl = $("ul.things li.active");
				var tagsEl = $("nav#menu li.active");
				if (thingsEl.index() === -1) {
					var tagsNextEl = tagsEl.next();

					if (tagsNextEl.index() === -1) {
						var nextUL = tagsEl.parent().next();
						while (nextUL.prop('tagName') !== 'UL' || nextUL.children().length === 0) {
							nextUL = nextUL.next();
							if (nextUL.prop('tagName') === undefined) {
								return false;
							}
						}

						tagsNextEl = nextUL.children(":first");
					}

					tagsNextEl.addClass("active");
					tagsEl.removeClass("active");
					return false;
				}

				scrollIntoView(thingsEl.next().click());
				return false;
			case 37: // left
				var thingsEl = $("ul.things li.active");
				if (thingsEl.index() !== -1) {
					thingsEl.addClass("prevactive");
					thingsEl.removeClass("active");

					var prevactiveEl = $("nav#menu li.prevactive");
					if (prevactiveEl.index() !== -1){
						prevactiveEl.addClass("active").removeClass("prevactive");
					} else {
						$("nav#menu li").first().addClass('active');
					}
				}
				return false;
			case 39: // right
				var thingsEl = $("nav#menu li.active");
				if (thingsEl.index() !== -1) {
					thingsEl.addClass("prevactive").removeClass("active");

					var prevactiveEl = $("ul.things li.prevactive");
					if (prevactiveEl.index() !== -1) {
						prevactiveEl.removeClass("prevactive").click();
					} else {
						$("section#things li").first().click();
					}
				}
				return false;
		}

		var keyPressed = String.fromCharCode(keyCode);
		keyPressed = e.shiftKey ? keyPressed.toUpperCase() : keyPressed.toLowerCase();
		switch (keyPressed) {
			case "e":
				$("ul.things li.active a.edit").click();
				return false;
			case "h":
				Thinkery.toggleHighlight();
				return false;
			case "c":
				Thinkery.rotateColorHighlight();
				return false;
			case "q":
				Thinkery.toggleQuickEdit();
				return false;
			case "a":
				if ($("#bulkControls").is(":visible")) $("#bulkControls button[name=archive]").click();
				else $("section#things li.active a.archive").click();
				return false;
			case "p":
				if ($("#bulkControls").is(":visible")) $("#bulkControls button[name=pin]").click();
				else $("section#things li.active a.pin").click();
				return false;
			case "t":
				if ($("#bulkControls").is(":hidden")) return true;
				$("#bulkControls button[name=add-tag]").click();
				return false;
			case "r":
				if ($("#bulkControls").is(":hidden")) return true;
				$("#bulkControls button[name=remove-tag]").click();
				return false;
			case "b":
				$('#toggleBulk').click();
				return false;
			case "m":
				$("section#things li.active input.bulk").click();
				return false;
			case "d":
				if ($("#bulkControls").is(":visible")) $("#bulkControls button[name=delete]").click();
				else $("ul.things li.active a.delete").click();
				return false;
			case "s":
				$("#searchadd").focus();
				return false;
		}
		return true;
	});

	var loginPopupHtml = $('#loginPopup').html();
	$('#loginPopup').text("");
	$(document).on("click", "a.login", function() {
		$.prompt.close();
		$.prompt(loginPopupHtml, {
			buttons: {},
			classes: 'login',
			loaded: function(v) {
				$("input[name=username]:visible").focus();
			}
		});
		return false;
	});

	$(document).on("submit", "form.login:hidden", function() {
		return false;
	});

	$(document).on("click", "div#notifications a.clear", function(e) {
		$(e.target).siblings().remove();
		$("a.notifications").closest("li").remove();
		$.post("/notifications.php", { clear: 1 });
		return false;
	});

	$(document).on("click", "div#notifications button.accept, div#notifications button.deny", function(e) {
		var li = $(this).closest("li");
		var data = {from: li.data("from")};
		var tag = li.data("tag");
		if ($(this).hasClass("accept")) {
			if (tag) {
				data.acceptTag = tag;
			} else {
				data.acceptThing = li.data("thing");
			}
		} else {
			if (tag) {
				data.denyTag = tag;
			} else {
				data.denyThing = li.data("thing");
			}
		}
		$.post("/share.php", data, function(r) {
			if (typeof data.acceptTag != "undefined" || typeof data.acceptThing != "undefined") {
				if (r == "ok") {
					location.reload();
				}
				alert(r);
				return;
			}
			li.remove();
		});
		return false;
	});

	if (!isTouchDevice) { // don't use tooltips on mobile device
		$(".tip").tipTip({
			delay: 100,
			edgeOffset: 5}
		);
	}

	$(document).on("click", "ul.dropdown li.dir", function(e) {
		var $this = $(e.target);
		if ($this.closest("li.open").length) return true;
		$this.closest("li").addClass("open");
		return false;
	});

	$("body").on("click touchend", function(e) {
		if ($(e.target).closest("li.open").length > 0) return true; // click within the dropdown: ok
		var openDropdown = $("ul.dropdown li.open");
		if (!openDropdown.length) return true;
		openDropdown.removeClass("open");
		return false;
	});


	$('input.tags,input[name=add-tag],input[name=remove-tag]').thinkeryAutocompleter({tags: Thinkery.tags});

	/* dragAndDrop for Things in Sidebar tags */
	;(function() {
		var menu = $('#menu'),
			els = $("li.thing-list-item.pinned"),
			dragIndex = -1,
			dragOptions = {
				opacity: 0.7,
				zIndex: 600,
				helper: 'clone',
				// scope: 'tags',
				appendTo: 'body',
				handle: '.drag',
				cursorAt: { left: 50 },
				start: function(event, ui) {
					els = $("li.thing-list-item.pinned");

					if (!els.length) els = $("li.thing-list-item:first");
					else els = els.add(els.next());

					if (!$(ui.helper).hasClass("pinned")) {
						dragIndex = -1;
					} else {
						dragIndex = els.index(ui.helper.context);
					}
					menu.addClass('drag-drop-enabled');
					nonFavoriteTags.show();
					favoriteTags.show();
				},
				stop: function() {
					menu.removeClass('drag-drop-enabled');
				},
				drag: function (event, ui) {
					var over = els.filter(".drophover");
					if (!over.length) return;
					var drag = ui.draggable;
					if (dragIndex > -1 && dragIndex < els.index(over[0])) {
						over.removeClass("before").addClass("after");
					} else {
						over.removeClass("after").addClass("before");
					}

				}
			},
			dropOptions = {
				accept: '.thing-list-item',
				activeClass: 'ui-state-highlight',
				hoverClass: 'drophover',
				tolerance: 'pointer',
				// scope: 'tags',
				drop: function (event, ui) {
					var drag = ui.draggable;
					if (!drag.size()) return;
					var id = drag.find('.bulk').val(),
						tag = $(this).attr('data-tag');

					var thing = Thinkery.findThingById(id);
					if (!thing) {
						// error
						return;
					}

					if ($(this).hasClass("thing-list-item")) {
						var from = drag.index();
						var to = $(this).index();

						// var before = ;

						if ($(this).hasClass("before")) {
							$(this).removeClass("before");
							drag.insertBefore(this);
						} else {
							$(this).removeClass("after");
							drag.insertAfter(this);
						}

						var saveAll = false;
						if (!drag.hasClass("pinned")) {
							Thinkery.pin(from, false);
							from = 0;
							saveAll = true;
						}

						Thinkery.movePinned(from, to, saveAll);

						return;
					} else if (!tag) {
						// prompt new tag window
						$.prompt('<h1 class="border">Add which tag?</h1><input type="text" name="add-tag" data-only-one="1" /><div class="status"></div>', {
							buttons: { "Add tag": true, "Cancel": false},
							loaded: function(v) {
								$("input[name=add-tag]:visible").focus().keyup(function(e) {
									if (e.altKey || e.metaKey || e.ctrlKey) return true;
									var keyCode = (e === null) ? event.keyCode : e.which;
									if (keyCode == 13) {
										$(this).closest(".jqi").find("button").eq(0).click();
									}
								});
							},
							submit: function(e, buttonClicked, message, fields) {
								if (!buttonClicked) return;
								var input = $("input[name=add-tag]:visible");
								if ($.trim(input.val()) === "") {
									input.closest(".jqi").find("div.status").addClass("error").html("Please enter a tag.");
									input.focus();
									return false;
								}

								Thinkery.saveThing({
									id: thing._id,
									title: thing.title,
									tags: $.trim(thing.textTags + " " + fields["add-tag"])
								}, function() {
									Thinkery.reloadSidebar();
								});

								return true;
							}
						});
						return;
					}

					Thinkery.saveThing({
						id: thing._id,
						title: thing.title,
						tags: $.trim(thing.textTags + " " + tag)
					});
				}
			};


		$(window).bind('things-list-updated', function(event, data) {
			$('.thing-list-item.canEdit').draggable(dragOptions);
		});
		if ($('.thing-list-item.canEdit').length) {
			$(window).bind('reloadSidebar', function(event, data) {
				$("#nonFavoriteTags li,#favoriteTags li,#menu .create-new-tag").droppable(dropOptions);
			});

			$(window).bind('changePinned', function(event, data) {
				var els = $("#things li.pinned");

				if (!els.length) els = $("#things li.thing-list-item:first");
				else els = els.add(els.next());

				els.droppable(dropOptions);
			});

			$('.thing-list-item.canEdit').draggable(dragOptions);
			$(window).trigger('changePinned');
			$(window).trigger('reloadSidebar');
		}

	})();

});
