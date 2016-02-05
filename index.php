<!DOCTYPE html>
<html lang="en">
<head>
	<title>HTML Viewer</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0">
    <style></style>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="codemirror/lib/codemirror.js"></script>
    <script src="codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="codemirror/mode/xml/xml.js"></script>
    <script src="codemirror/mode/javascript/javascript.js"></script> 
    <link rel="stylesheet" href="codemirror/lib/codemirror.css">
	<script>
	$(document).ready(function() {
		$('#fetchUrl').click(function() {
			var _url = $('#getUrl').val();
			$('#codeArea').val(_url);
			$.ajax({
			  url: 'ajax/getUrl.php',
			  data: {url: _url},
			  success: success
			});
			function success(data) {
				$('#codeArea').val(data.data);
				var myCodeMirror = CodeMirror.fromTextArea(document.getElementById('codeArea'), {
				  mode: 'text/html'
				});
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
			}
		});
	});
	</script>
</head>
<body>

<input id="getUrl" value="http://slack.com">
<button id="fetchUrl">fetch</button>
<br>
<div id="log">
<ul>
</ul>
</div>
<br>
<textarea id="codeArea" readonly>text</textarea>
<br>
</body>


</html>
