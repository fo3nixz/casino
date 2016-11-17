<?
	$getURL = 'https://free.egtmgs.com/';
	$basePath = '../NEWEGT/';

	function get($url) {
		sleep(1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);  
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($retcode !== 200) {
			return false;
		}
		else {
			return $result;
		}
	}
	
	function getDirContents($dir, &$results = array()){
		$files = scandir($dir);

		foreach($files as $key => $value){
		    $path = $dir.DIRECTORY_SEPARATOR.$value;
		    if($value == 'index.php' || $value == 'gammatrix.php') {
		    	continue;
		    }
		    if(!is_dir($path)) {
		        $results[] = $path;
		    } else if($value != "." && $value != "..") {
		        getDirContents($path, $results);
		        $results[] = $path;
		    }
		}

		return $results;
	}
	
	function RemoveEmptySubFolders($path) {
		$empty=true;
		foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file) {
			if (is_dir($file)) {
				if (!RemoveEmptySubFolders($file)) $empty=false;
			}
		 	else {
				$empty=false;
			}
		}
		if ($empty) rmdir($path);
		return $empty;
	}
	
	
	$z = scandir(__DIR__);
    foreach($z as $l) {
    	if(strpos($l, '.') == false && strlen($l) > 2) {
    		$content = getDirContents($l);
    		
    		$gamePath = $basePath.$l.'/';
    		
    		$sType = '';
    		$sName = '';
    		
    		$res = array();
    		foreach($content as $c) {
    			$exploded = explode('/', $c);
    			array_shift($exploded);
    			$c = implode('/', $exploded);
    			
				if(strpos($c, 'games/') !== false) {
					$typeSLOT = $exploded[1].'2';
					
					if($exploded[1] == 'MagSlot') {
						$typeSLOT = $exploded[1];
					}
					if($exploded[1] == 'ForestBandSlot') {
						$typeSLOT = $exploded[1];
					}
					if($exploded[1] == 'ImperialWarsSlot') {
						$typeSLOT = $exploded[1];
					}
					if($exploded[1] == 'KangarooSlot') {
						$typeSLOT = $exploded[1];
					}
					if($exploded[1] == 'RainbowQueenSlot') {
						$typeSLOT = $exploded[1];
					}
				
				
					if($exploded[1] == 'HoldSlot') {
						$typeSLOT = 'ExtraJokerSlot';
					}
					if($exploded[1] == 'HalloweenSlot') {
						$typeSLOT = 'DarkQueenSlot';
					}
					if($exploded[1] == 'OceanRushSlot') {
						$typeSLOT = 'ActionMoneySlot';
					}
					$exploded[1] = $typeSLOT;
					$c = implode('/', $exploded);
				}
				
				if(count($exploded) > 3) {
    				$sType = $exploded[1];
    				$sName = $exploded[2];
    			}
    			
    			
    			$res[] = $c;
    		}
    		
    		$res[] = 'games/'.$sType.'/'.$sType.'Engine.swf';
    		
    		$res[] = 'games/'.$sType.'/'.$sName.'/freespinAnimation';
    		$res[] = 'games/'.$sType.'/'.$sName.'/freespinAnimation/freespinAnimation_en.swf';
    		$res[] = 'games/'.$sType.'/'.$sName.'/freespinAnimation/freespinAnimation_ru.swf';
    		
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_en_1.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_en_2.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_en_3.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_en_4.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_en_5.png';
    		
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_ru_1.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_ru_2.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_ru_3.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_ru_4.png';
    		$res[] = 'games/'.$sType.'/'.$sName.'/paytable/paytable_ru_5.png';
    		
    		$res[] = 'games/'.$sType.'/'.$sName.'/videos';
    		
    		$videoJSON = json_decode(get($getURL . 'games/'.$sType.'/'.$sName.'/visualSettings.json'));
    		foreach($videoJSON->reelVideosConfig as $o) {
    			$res[] = 'games/'.$o->url;
    		}   		
    		
    		foreach($res as $c) {
    			if(strpos($c, '.') == false) {
    				if(!file_exists($gamePath . $c)) {
    					mkdir($gamePath . $c, 0777, true);
    				}
    				
    			}
    		}
    		
    		foreach($res as $c) {
    			if(strpos($c, '.') !== false) {
    				$filePath = $gamePath . $c;
    				$url = $getURL . $c;
    				$content = get($url);
    				if($content !== false) {
    					if(strpos($c, 'help_en.html') !== false) {
    						$content = str_replace('../../', '../../../../../', $content);
    					}
    					if(strpos($c, 'content.xml') !== false) {
    						$content = str_replace("timeToLive='15'", "timeToLive='9999'", $content);
    					}
    					$f = fopen($filePath, 'w+');
    					fwrite($f, $content);
    					fclose($f);
    				}
    				
    			}
    		}
    		
    		$f = fopen($gamePath . 'index.php', 'w+');
    		fwrite($f, '<?
	require_once(\'../index.php\');
?>');
			fclose($f);
    		
    		RemoveEmptySubFolders($gamePath);

		}
    	
    	
    }
?>
