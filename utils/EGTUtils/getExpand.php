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
				
				if(count($exploded) > 3) {
    				$sType = $exploded[1];
    				$sName = $exploded[2];
    			}
    			
    			
    			//$res[] = $c;
    		}
    		
    		$res[] = 'games/'.$sType.'/'.$sName.'/videos';
    		$res[] = 'games/'.$sType.'/'.$sName.'/videos/expand.flv';	
    		
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
    		
    		RemoveEmptySubFolders($gamePath);

		}
    	
    	
    }
?>
