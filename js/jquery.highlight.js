/*

highlight v3

Highlights arbitrary terms.

<http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>

MIT license.

Johann Burkard
<http://johannburkard.de>
<mailto:jb@eaio.com>

*/


;(function($) {
	$.fn.highlight = function(pat) {
		if (typeof pat == "string") pat = pat.replace(/"/g, "");
		if (!pat) return jQuery.fn.removeHighlight();
		function innerHighlight(node, pat) {
			var skip = 0;
			if (node.nodeType == 3) {
				var pos = node.data.toUpperCase().indexOf(pat);
				if (pos >= 0) {
					var spannode = document.createElement('span');
					spannode.className = 'highlight';
					var middlebit = node.splitText(pos);
					var endbit = middlebit.splitText(pat.length);
					var middleclone = middlebit.cloneNode(true);
					spannode.appendChild(middleclone);
					middlebit.parentNode.replaceChild(spannode, middlebit);
					skip = 1;
				}
			}
			else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
				for (var i = 0; i < node.childNodes.length; ++i) {
					i += innerHighlight(node.childNodes[i], pat);
				}
			}
			return skip;
		}
		var patterns = [], patternsFull = pat.toUpperCase().split(/\s+/);
		for (var i = 0, l = patternsFull.length; i < l; i++) {
			if ($.trim(patternsFull[i]) != "") patterns.push(patternsFull[i]);
		}
		var patterns_length = patterns.length;
		return this.each(function() {
			for (var i = 0; i < patterns_length; i++) {
				pat = patterns[i];
				innerHighlight(this, pat);
			}
		});
	};

	$.fn.removeHighlight = function() {
		return this.find("span.highlight").each(function() {
			this.parentNode.replaceChild(this.firstChild, this);
			this.parentNode.normalize();
		}).end();
	};
})(jQuery);
