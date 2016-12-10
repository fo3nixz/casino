<?php

	$api = Yii::app()->gamesApi2;
	
	// название игры
    $game = $api->getGameStringId();
    // протокол
    $protocol = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://';
    // ссылка на статику с папкой игры
    $sh = $protocol.$api->getStaticHost().'/'.$api->getGameSectionStringId().'/'.$game;
    // ссылка на сессию с папкой игры
    $sl = $protocol.$api->getSessionHost().'/'.$api->getGameSectionStringId().'/'.$game;
    // bonus
    $bonus = (empty($_GET['bonus'])) ? '' : '?bonus='.$_GET['bonus'];
    
    $helpType = 1;
	if(isset($gameParams['url_help'])) {
	    $helpType = $gameParams['url_help'];
	}

	$iframe_url = $protocol.$api->getStaticHost().'/'.$api->getGameSectionStringId();
	$iframe_url .= '/index.html?game='.$game.'&session='.$sl.'&static='.$sh.'&sp='.$protocol.$api->getSessionHost().'&bonus='.$bonus.'&helpType='.$helpType;
	include "iframe_html.php";
?>
