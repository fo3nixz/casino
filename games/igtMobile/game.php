<?
    $api = Yii::app()->gamesApi2;
    
    // название игры
    $game = $api->getGameStringId();
    // протокол
    $protocol = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://';
    // ссылка на статику с папкой игры
    $sh = $protocol.$api->getStaticHost().'/'.$api->getGameSectionStringId().'/'.$game;
    // ссылка на сессию с папкой игры
    $sl = $protocol.$api->getSessionHost().'/'.$api->getGameSectionStringId().'/'.$game;
    // ссылка на статику производителя
    $ss = $protocol.$api->getStaticHost().'/'.$api->getGameSectionStringId();
    // bonus
    $bonus = (empty($_GET['bonus'])) ? '' : '?bonus='.$_GET['bonus'];
    // ссылка на обработчик
    $serverLink = $protocol.$api->getSessionHost().'/skvCore/WebEngine.php'.$bonus;
    
    $flash_fullscreen = true;
    // ссылка на флешку + GET параметры для флешки
    
    $lang = 'en';
    
    $gameParams = $api->getLaunchParams();
    $config = $api->getConfigVars();
    
    if(!empty($config['lang'])) $lang = $config['lang'];
    if(!empty($gameParams['lang'])) $lang = $gameParams['lang'];
    
    
    $bonus = (!empty($_GET['bonus'])) ? $_GET['bonus'] : '';
	
	$helpType = 1;
	if(isset($gameParams['url_help'])) {
	    $helpType = $gameParams['url_help'];
	}
?>


<!DOCTYPE html>
<html>
<head>
    <title><?=$api->getGameStringId()?></title>
    <meta charset="utf-8">
    <style>
        iframe, body, html {
            margin:0!important;
            padding:0!important;
            width:100%!important;
            height:100%!important;
            overflow:hidden;
        }
    </style>
</head>
<body>
    <iframe src="<?=$sh?>/index.html?game=<?=$game?>&session=<?=$sl?>&static=<?=$sh?>&bonus=<?=$bonus?>&helpType=<?=$helpType?>&sessiop=<?=$ss?>" width="100%"></iframe>
</body>
</html>
