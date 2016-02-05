<!DOCTYPE html>
<html lang="en">
	<head>
		<title>HTML Viewer</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0">

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/codemirror.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/addon/search/search.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/addon/search/searchcursor.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/addon/mode/overlay.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/addon/search/match-highlighter.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/mode/htmlmixed/htmlmixed.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/mode/xml/xml.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/mode/javascript/javascript.js"></script> 
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.11.0/codemirror.css">
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
					var _regex = new RegExp('<\\/?' + query + '[^>]*?>', "i");
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
					$('#fetchUrl').remove();
					$('#getUrl').remove();
					fetch(getQueryVariable('url'));
				}
				else {
					$('#fetchUrl').click(function() {
						var _url = $('#getUrl').val();
						fetch(_url);
					});
				}
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
						var fakeDom = $.parseHTML(data.data, document, true);
						var $log = $("#log");
						var tagSummary = {};
						$.each(fakeDom, function(i, el) {
							var _tagName = $(el).prop("tagName");
							if(_tagName !== undefined) {
								if (_tagName in tagSummary) {
									tagSummary[_tagName] = tagSummary[_tagName] + 1;
								}
								else {
									tagSummary[_tagName] = 1;
								}
							}
						});
						$.each(tagSummary, function(i, el) {
							$log.find("ul").append('<li class="tagSelect" data-tag-name="' + i.toLowerCase() + '"> ' + i.toLowerCase() + ' : ' + el + ' </li>');
						});
						$('#fetchUrl').remove();
						$('#getUrl').remove();
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
		<input id="getUrl" value="http://google.com">
		<button id="fetchUrl">fetch</button>
		<br>
		<h1 id="urlTitle"></h1>
		<div id="log">
			<ul>
			</ul>
		</div>
		<textarea id="codeArea" style="display:none;"></textarea>
	</body>
</html>
