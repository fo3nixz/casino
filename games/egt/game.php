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

	$game_swf = $sh.'/GamePlatform.swf?ts=1440967567933';
	
	
	$url_casino = empty($gameParams["url_casino"]) ? '' : $gameParams["url_casino"];
	
    
    $timeLink = $protocol.$api->getSessionHost().'/skvCore/time.php';
    
    $flash_wmode = 'opaque';
    
    $jscript = 'window.addEventListener("load", function() {
        setInterval(function(){
            var flash = document.getElementById("flashGameObject");
            flash.tabIndex = 1234;  // This was needed on Chrome 23
            flash.focus();
        }, 500);
        
        var d = document.createElement("div");
        d.setAttribute("id", "exitLabel");
        d.setAttribute("style", "position:absolute; z-index:9999;");
        
        document.body.appendChild(d);
        
        var elem = document.getElementById(\'exitLabel\');
        function onResize() {      
            
            var width = window.innerWidth;
            var height = window.innerHeight;
            var baseRatio = 0.625;
            var currentRatio = height / width;
            
            if(width * baseRatio > height) {
                var currentWidth = height / baseRatio;
                elem.style.left = width/2 + currentWidth/5.33 + \'px\';
                elem.style.bottom = height / 60 + \'px\';
                elem.style.width = currentWidth/15.7 + \'px\';
                elem.style.height = currentWidth/25 + \'px\';
            }
            else {
                var currentHeight = width * baseRatio;
                elem.style.left = width/2 + width/5.33 + \'px\';
                elem.style.bottom = (height-currentHeight)/2 + currentHeight/60 + \'px\';
                elem.style.width = width/15.7 + \'px\';
                elem.style.height = currentHeight/16 + \'px\';
            }
            console.log(currentRatio);
            
            
        }
        
        window.addEventListener(\'resize\', onResize); 
        onResize();
        elem.addEventListener(\'click\', function() {
            var href = "'.$url_casino.'";
            if(href.indexOf(\'history.back\') >= 0) {
                history.back();
            }
            else if(href.indexOf(\'window.close\') >= 0) {
                window.close();
            }
            else {
                location.href = href;
            }
            
        });
        
        Function.prototype.clone = function() {
			var fct = this;
			var clone = function() {
				return fct.apply(this, arguments);
			};
			clone.prototype = fct.prototype;
			for (property in fct) {
				if (fct.hasOwnProperty(property) && property !== \'prototype\') {
				    clone[property] = fct[property];
				}
			}
			return clone;
		};
		
		function showHelp(url, type, size) {
			openFallback("'.$sl.'/" + url, type, size);
		}
		
		var openFallback = window.open.clone();
		window.open = showHelp;
    });';
    
	$flashvars = 'tcpHost: "'.$api->getSessionHost().'",
	tcpPort: "1128",
	sessionKey:"'.$api->getSessionHost().'",
	lang:"'.$lang.'",
	content:"assets/content.xml",
	gameIdentificationNumber:"813"';

	include 'flash_html.php';
?>
