<!DOCTYPE html>
<html lang="en">
	<head>
		<title>HTML Viewer</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0">
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/codemirror.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/addon/mode/overlay.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/mode/htmlmixed/htmlmixed.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/mode/xml/xml.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/mode/javascript/javascript.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/codemirror.min.css">
		<style>
			.CodeMirror {
				height: auto;
				margin-left: 50px;
				margin-right: 50px;
			}
			.cm-highlightSearch {
				color: #0ca;
				background-color: #FFF147;
			}
			#error {
				color: red;
				font-size: 16px;
			}
			#log {
				width: 200px;
				margin-left: 50px;
			}
			h1 {
				font: 200 20px/1.5 Helvetica, Verdana, sans-serif;
				margin-left: 50px;
			}
			ul {
				list-style-type: none;
				margin: 0 0 50px 0;
				padding: 0;
			}
			li {
				font: 200 20px/1.5 Helvetica, Verdana, sans-serif;
				border-bottom: 1px solid #ccc;
			}
			li:last-child {
				border: none;
			}
			.tagSelect {
				cursor: pointer;
			}
			#getUrl {
				font: 200 14px/1.5 Helvetica, Verdana, sans-serif;
				margin-left: 50px;
				height: 30px;
				width: 200px;
			}
			#fetchUrl {
				font: 200 14px/1.5 Helvetica, Verdana, sans-serif;
				margin-left: 5px;
				height: 30px;
				width: 50px;
			}
		</style>
		<script>
			var cm = (function() {
				var editor;
				var mode = 'text/html';
				var overlay = null;

				function init(element) {
					editor = CodeMirror.fromTextArea(element, {
						mode: mode,
						readOnly: true,
						lineWrapping: true,
						scrollbarStyle: null,
						lineNumbers: true,
						viewportMargin: Infinity
					});
				}
				function setHighlight(tagName) {
					editor.operation(function() {
						if (overlay !== null) {
							editor.removeOverlay(overlay);
						}
						overlay = _makeOverlay(tagName);
						editor.addOverlay(overlay);
					});
				}
				function _makeOverlay(query) {
					var _regex = new RegExp('<\\/?' + query + '\\b[^>]*?>', "i");
					return {
					   token: function(stream, state) {
							if (stream.match(_regex)) {
								return "highlightSearch";
							}
							while (stream.next() != null && !stream.match(_regex, false)) {}
							return null;
						}
					};
				}
				function loadData(value) {
					editor.setValue(value);
				}
				return {
					init: init,
					loadData: loadData,
					setHighlight: setHighlight
				};
			})();
			$(document).ready(function() {
				if(getQueryVariable('url')) {
					var _url = getQueryVariable('url');
					$('#getUrl').val(_url);
					fetch(_url);
				}
				$('#fetchForm').submit(function(e) {
					e.preventDefault();
					var _url = $('#getUrl').val();
					window.location.href = window.location.pathname + '?url=' + _url;
				});
			});
			function fetch(_url) {
				$.ajax({
				  url: 'ajax/getUrl.php',
				  data: {url: _url},
				  success: success
				});
				function success(data) {
					if(data.status == "1") {
						cm.init(document.getElementById('codeArea'));
						cm.loadData(data.data);
						var tagSummary = {};
						var metaTags = ['title', 'style', 'meta', 'link', 'script', 'base'];
						var $log = $("#log");
						function countTag(_tagName) {
							if (_tagName in tagSummary) {
								tagSummary[_tagName] = tagSummary[_tagName] + 1;
							}
							else {
								tagSummary[_tagName] = 1;
							}
						}
						var fakeDom = data.data;
						// pull top level tags as strings
						var _result = fakeDom.match(/<(head|html|body|\!doctype)[^>]*?>/ig);
						$.each(_result, function(i, el) {
							match = el.match(/<(\!?[\w]*).*?/i);
							if (match != null)
								countTag(match[1].toLowerCase());
						});
						// count meta tags before converting to jQuery collection
						fakeDom = $.parseHTML(fakeDom, document, true);
						$.each(fakeDom, function(i, el) {
							var _name = el.tagName;
							if (_name !== undefined) {
								_name = _name.toLowerCase();
								if (metaTags.indexOf(_name) !== -1){
									countTag(_name);
								}
							}
						});
						// convert to jQuery so we can use find on the body
						var $allDom = $(fakeDom).find('*');
						$.each($allDom, function(i, el) {
							countTag($(el).prop("tagName"));
						});
						$.each(tagSummary, function(i, el) {
							$log.find("ul").append('<li class="tagSelect" data-tag-name="' + i.toLowerCase() + '"> ' + i.toLowerCase() + ' : ' + el + ' </li>');
						});
						$('li.tagSelect').click(function() {
							var $this = $(this);
							$this.parent().find("li").removeClass('cm-highlightSearch');
							$this.addClass('cm-highlightSearch');
							cm.setHighlight($(this).data("tagName"));
						});
						$("#urlTitle").html("tag summary for " + _url);
					}
					else {
						$('#error').html('Bad url: ' + _url);
					}
				}
			}
			function getQueryVariable(variable)	{
				var query = window.location.search.substring(1);
				var vars = query.split("&");
				for (var i=0;i<vars.length;i++) {
					var pair = vars[i].split("=");
					if(pair[0] == variable){return pair[1];}
				}
				return(false);
			}
		</script>
	</head>
	<body>
		<div id="error"></div>
		<form id="fetchForm">
			<input id="getUrl" value="http://google.com">
			<button type="submit" id="fetchUrl">fetch</button>
		</form>
		<br>
		<h1 id="urlTitle"></h1>
		<div id="log">
			<ul>
			</ul>
		</div>
		<textarea id="codeArea" style="display:none;"></textarea>
	</body>
</html>
