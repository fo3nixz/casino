<?
	if(!isset($game)) {
		$phpfiles = scandir(__DIR__);
		foreach($phpfiles as $phpfile) {
			if(strpos($phpfile, '.') === false) {
				echo "<a href=$phpfile>".basename($phpfile)."</a><br>";
			}	
			
		}
		die();
	}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=$game?></title>
    <style>
        * {
            margin:0;
            padding:0;
        }
        html, body {
            height:100%;
            overflow:hidden;
        }   
        object {
            width:100%!important;
            height:100%!important;
        }
    </style>
    
    <script type="text/javascript" src="js/swfobject.js"></script>
    <script type="text/javascript" src="js/game.js"></script>    
    <script type="text/javascript" src="js/externalAPI.js"></script>
    <?php
        if(!empty($_GET['bonus'])) {
            $b = '?bonus='.$_GET['bonus'];
        }
        else {
            $b = '';
        }
    ?>
</head>
<body>
    <object type="application/x-shockwave-flash" data="gpe/GPE_Flash/framework/GPELauncher/bin/GPELauncher.swf<?=$b?>" width="100%" height="100%" id="gameSwf" style="visibility: visible;">
        <param name="no_flash" value="Sorry, you need to install flash to see this content.">
        <param name="allowFullScreen" value="true">
        <param name="base" value="gpe/casino/skins/MRGR/bin/">
        <param name="allowScriptAccess" value="always">
        <param name="bgcolor" value="#000000">
        <param name="wmode" value="opaque">
        <param name="menu" value="false">
        <param name="flashvars" value="countrycode=&amp;currencycode=FPY&amp;language=en&amp;minbet=1.0&amp;denomamount=1.0&amp;nscode=MRGR&amp;securetoken=&amp;skincode=MRGR&amp;softwareid=200-1227-001&amp;uniqueid=Guest&amp;channel=INT&amp;presenttype=FLSH&amp;isLocal=false&amp;assetPath=..%2F..%2F..%2F..%2F..&amp;server=..%2F..%2F..%2F..%2F..%2F&amp;audio=on"></object>
</body>
</html>
