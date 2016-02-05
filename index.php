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
		</style>
		<script>
		var mode = 'text/html';
		var keyword = 'div';
		function getRegex() {
			return _regex = new RegExp('<\\/?' + keyword + '[^>]*?>', "i");
		}
		CodeMirror.defineMode("highlightSearch", function (config, parserConfig) {
			var searchOverlay = {
               token: function(stream, state) {
                    if (stream.match(getRegex())) {
                        return "highlightSearch";
                    }
                    while (stream.next() != null && !stream.match(_regex, false)) {}
                    return null;
                }
			};
			return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || mode), searchOverlay);
		});
		var myCodeMirror;
		$(document).ready(function() {
			myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codeArea'), {
				mode: 'highlightSearch',
				readOnly: true,
				lineWrapping: true,
				scrollbarStyle: null,
				lineNumbers: true,
				viewportMargin: Infinity
			});
			if(getQueryVariable('url')) {
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
				myCodeMirror.setValue(data.data);
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
					$log.find("ul").append('<li> ' + i.toLowerCase() + ' : ' + el + ' </li>');
				});
				$('#fetchUrl').remove();
				$('#getUrl').remove();
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
		<input id="getUrl" value="http://google.com">
		<button id="fetchUrl">fetch</button>
		<br>
		<div id="log">
			<ul>
			</ul>
		</div>
		<textarea id="codeArea" style="display:none;"></textarea>
	</body>
</html>
