<?
	$z = scandir(__DIR__);
    foreach($z as $l) {
    	if(strpos($l, '.') == false && strlen($l) > 2) {
    		echo '<a href="http://localhost/core/games/egt/'.$l.'">'.$l.'</a><br>';
    	}
    }	
?>
