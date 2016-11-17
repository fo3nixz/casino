<?
	session_start();

	$c = explode('/', $_SERVER['REQUEST_URI']);
	array_pop($c);
	$game = end($c);
?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <title><?=$game?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="content-language" content="en" />
    <meta http-equiv="cache-control" content="no-store, must-revalidate" />
    <meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT" />
    <style type="text/css">
    html, body { width:100%; height:100%; padding:0px; margin:0px; background:#E9E9E9; overflow:hidden; }
    #ifmGame { width:100%; height:100%; border:0px; }
    </style>
</head>
<body>

	<script>
		Function.prototype.clone = function() {
			var fct = this;
			var clone = function() {
				return fct.apply(this, arguments);
			};
			clone.prototype = fct.prototype;
			for (property in fct) {
				if (fct.hasOwnProperty(property) && property !== 'prototype') {
				    clone[property] = fct[property];
				}
			}
			return clone;
		};
		
		function showHelp(url, type, size) {
			openFallback(url, type, size);
		}
		
		var openFallback = window.open.clone();
		window.open = showHelp;
	</script>

    <object type="application/x-shockwave-flash" id="GamePlatform" data="GamePlatform.swf?ts=1440967567933" width="100%" height="100%"><param name="menu" value="false"><param name="allowFullscreen" value="true"><param name="allowScriptAccess" value="always"><param name="bgcolor" value="#000000"><param name="allowFullScreenInteractive" value="true"><param name="flashvars" value="tcpHost=93.76.48.30&amp;tcpPort=1128&amp;sessionKey=<?=$game?>|||egt|||<?=session_id()?>&amp;lang=en&amp;content=assets/content.xml&amp;gameIdentificationNumber=813"></object>
</body>
</html>
