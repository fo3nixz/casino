<pre>
<?
    $api = Yii::app()->gamesApi2;
    
    // название игры
    $game = $api->getGameStringId();
    // протокол
    $protocol = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://';
    // ссылка на статику с папкой игры
    $sh = $protocol.$api->getStaticHost().'/'.$api->getGameSectionStringId().'/'.$game.'/';
    // bonus
    $bonus = (empty($_GET['bonus'])) ? '' : '?bonus='.$_GET['bonus'];
    // ссылка на обработчик
    $serverLink = $protocol.$api->getSessionHost().'/skvCore/WebEngine.php'.$bonus;
    
    $gameParams = $api->getLaunchParams();
    $config = $api->getConfigVars();
    
    $flash_fullscreen = true;
    
    if(isset($config['flash_scale_exactfit'])) $flash_fullscreen = (bool) $config['flash_scale_exactfit'];
    
    if(isset($gameParams['flash_scale_exactfit'])) {
        if($gameParams['flash_scale_exactfit'] == '1') {
            $flash_fullscreen = true;
        }
        else {
            $flash_fullscreen = false;
        }
    }
    // ссылка на флешку + GET параметры для флешки
    // язык
    $lang = 'en';
    if(!empty($config['lang'])) $lang = $config['lang'];
    if(!empty($gameParams['lang'])) $lang = $gameParams['lang'];
    
    
	$game_swf = $sh. 'GTSWrapper.swf?brand=vanilla&theGame='.$sh.$game.'.swf&lang='.$sh.$game.'_'.$lang.'.xml&edgeServlet='.$serverLink.'&width=100%25&height=100%25&vssession=&loginScript=loadLobby()&play4=free&domain=harrycasino.com&nativeHeight=700&nativeWidth=1130&gameTitle='.$game;
    
    $timeLink = $protocol.$api->getSessionHost().'/skvCore/time.php';
    $jscript = 'window.addEventListener("load", function() {
        var link = "'.$timeLink.'",
            obj = document.getElementById("flashGameObject"),
            counter = 0,
            interval = setInterval(function() {
                checkTime();
            }, 500);
        function checkTime() {
			
            var xmlhttp = new XMLHttpRequest(),
				currentTime = Math.round(+new Date() / 1000),
                resp, time, r;
            xmlhttp.open("POST", link + "?time=" + currentTime, true);
            xmlhttp.send(null);
            xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4) {
					if(xmlhttp.status == 200) {
						resp = xmlhttp.responseText;
						time = Math.round(+new Date() / 1000);
						r = resp - time;
						console.log(r);
						if(r < 2 && r > -2) {
							clearInterval(interval);
							document.getElementById("flashGameObject").style.width = "100%";
						}
						else {
							if(counter % 2 == 0) {
								document.getElementById("flashGameObject").style.width = "100%";
							}
							else {
								document.getElementById("flashGameObject").style.width = "100.1%";
							}
							counter++;
						}
					}
				}
			}
			
        }
    });';
	$flashvars = 'helpURL: "javascript:$j(&quot;#Avengers&quot;).data(&quot;game&quot;).loadGameRules();"';
	
	include 'flash_html.php';
?>
