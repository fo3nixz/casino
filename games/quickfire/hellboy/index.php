<?
    session_start();
    $_SESSION['server_link'] = 'http://localhost/core/WebEngine.php?game=hellboy|||quickfire';
    $_SESSION['lang'] = 'en';
    $_SESSION['static_link'] = 'http://localhost/core/games/hellboy/';
    $_SESSION['server_game_link'] = $_SESSION['static_link'];
?>
<html>
<head>
    <title>HellBoy</title>
    <style>
        #system {
            width:100%!important;
            height:100%!important;
            margin:0!important;
        }
    </style>
</head>
<body>
    <script src="HttpCombiner.php"></script>
    <script src="s1.php"></script>
<div id="pagePost" action=""></div>
<object type="application/x-shockwave-flash" id="system" name="system" data="Loader.swf" width="100%" height="100%">
        <param name="bgcolor" value="#000000">
        <param name="wmode" value="opaque">
        <param name="align" value="middle">
        <param name="menu" value="false">
        <param name="allowFullScreen" value="false">
        <param name="allowScriptAccess" value="sameDomain">
        <param name="swliveconnect" value="true">
        <param name="flashvars" value="widescreen=True&amp;allowResolutionLocking=true&amp;t3game=false&amp;defaultFrame=false">
    </object>    
</body>
</html>
