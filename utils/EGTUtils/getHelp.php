<?
	$z = scandir(__DIR__);
    foreach($z as $l) {
    	if(strpos($l, '.') == false && strlen($l) > 2) {
    		$p = $l.'/games/';
    		$t = scandir($p);
    		$typeSLOT = $t[2];
    		$p .= $t[2].'/';
    		$t = scandir($p);
    		foreach($t as $ti) {
    			if(strpos($ti, '.') == false && strlen($ti) > 2) {
    				$p .= $ti;
    				$nameSLOT = $ti;
    			}
    		}
    		
    		$jp = 'https://free.egtmgs.com/games/'.$typeSLOT.'2/'.$nameSLOT.'/helpJackpot/help_en.html';
    		
    		if($typeSLOT == 'MagSlot') {
    			$jp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		if($typeSLOT == 'ForestBandSlot') {
    			$jp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		if($typeSLOT == 'ImperialWarsSlot') {
    			$jp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		if($typeSLOT == 'KangarooSlot') {
    			$jp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		if($typeSLOT == 'RainbowQueenSlot') {
    			$jp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		
    		
    		if($typeSLOT == 'HoldSlot') {
    			$jp = 'https://free.egtmgs.com/games/ExtraJokerSlot/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		if($typeSLOT == 'HalloweenSlot') {
    			$jp = 'https://free.egtmgs.com/games/DarkQueenSlot/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		if($typeSLOT == 'OceanRushSlot') {
    			$jp = 'https://free.egtmgs.com/games/ActionMoneySlot/'.$nameSLOT.'/helpJackpot/help_en.html';
    		}
    		
    		$content = file_get_contents($jp);
    		$sc = strpos($content, 'Following buttons appear');
    		$ec = strpos($content, 'On the top of the game screen, the player can also see the number of the game and the local time according to his/her PC.');
    		$ec += strlen('On the top of the game screen, the player can also see the number of the game and the local time according to his/her PC.');
    		
    		$tmp = str_replace('_', ' ', $l);
    		$tmp = ucwords($tmp);

    		$m = 'Following buttons appear on the screen of the '.$tmp.' slot:
	<ul>
		<li><span class="game_controls_items">“Paytable”</span> - when activated this button opens/closes the rules of this game. “Paytable” button is inactive when the reels are spinning, when the game is in Autoplay and Free spins mode;</li>
		<li><span class="game_controls_items">“Help”</span> - when activated this button opens up a screen with helpful information - this screen;</li>
		<li>
			<span class="game_controls_items">“Start/Stop All/Collect”</span> - depending on the stage of the game, the button is in one of the three states: “Start”, “Stop All”, “Collect”.<br>
			When activating button “Start”, the reels start turning and this button turns into “Stop All” button. If there was an amount previously won, it is automatically added to the player\'s balance. The Reels can also start rotating by activation of the “Space” button (from the keyboard).<br>
			When the button “Stop All” is activated, the reels stop turning all at one time and the button changes to “Start” (if there is no winning combination), and “Collect” if there is a winning combination. The Reels can also stop rotating by activation of the “Space” button (from the keyboard). In '.$tmp.' reels can be stopped individually. While the reels are rotating, the player can click and stop each one of the reels, and then this particular reel stops simultaneously with the first one.<br>
			When activating “Collect” button, the animation of the increased winning stops and in the field “Win” the entire winning sum is shown, and the button gets status “Start”. The animation of the increased winning can also stop by activation of the “Space” button (from the keyboard);
		</li>
		<li><span class="game_controls_items">“Gamble”</span> when activated it opens up the Gamble screen. Pressing the „⇦“ or „⇨“ buttons from the keyboard can also activate “Gamble”;</li>
		<li><span class="game_controls_items">“Autoplay/Stop Auto”</span> - depending on the stage of the game, this button can have one of the two statuses – “Autoplay”, “Stop Auto”. When “Autoplay” button is activated, the game goes into an Autoplay mode and the button appears as “Stop Auto”. This button is active only if the reels are not turning and the screen Gamble is not open.  When “Stop Auto” button is activated, the “Autoplay” game mode is stopped and the button appears as „Autoplay“. This button is active only when the game is in Autoplay mode. Activation of this button is also possible by pressing „.“ symbol from the keyboard;</li>
		<li><span class="game_controls_items">“Exit”</span> - when activating this button, the player leaves the game. “Exit” button is inactive when the reels are rotating, in Autoplay, Gamble and Free Spins mode. In case a sum is won, during the activation of the button “Exit”, the won amount is added to the player\'s balance and the game is closed. The player cannot gamble this last amount won furthermore. Activation of this button is also possible by pressing „,“ symbol from the keyboard;</li>
		<li><span class="game_controls_items">“Bet”</span> - there are five buttons to select a bet. Activation of those buttons is also possible by pressing „C“, „V“, „B“, „N“ and „M” buttons from the keyboard. Pressing „C“ button places the smallest bet, pressing „V“ button places next bet and so on;</li>
		<li><span class="game_controls_items">Change of Denomination button</span> - displays the value of one credit. Clicking this button is also possible by pressing „X“ button from the keyboard. This button is inactive while the reels are rotating, during Autoplay and Gamble mode. The selection of the specific value is also possible by pressing following buttons „C“, „V“, „B“ and „N“ from the keyboard. Pressing „C“ button selects the smallest denomination, pressing „V“ button selects next one and so on. If there is win amount, when changing the denomination this amount is added to the player’s balance and he/she cannot gamble this amount furthermore.</li>
	</ul>';
    	
    		$jsContent = substr($content, 0, $sc) . $m . substr($content, $ec);
    		
    		$jsContent = str_replace('../../help.css', '../../../../../help.css', $jsContent);
    		$jsContent = str_replace('../../help.js', '../../../../../help.js', $jsContent);
    		
    		$njp = 'https://free.egtmgs.com/games/'.$typeSLOT.'2/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		
    		if($typeSLOT == 'MagSlot') {
    			$njp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    		if($typeSLOT == 'ForestBandSlot') {
    			$njp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    		if($typeSLOT == 'ImperialWarsSlot') {
    			$njp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    		if($typeSLOT == 'KangarooSlot') {
    			$njp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    		if($typeSLOT == 'RainbowQueenSlot') {
    			$njp = 'https://free.egtmgs.com/games/'.$typeSLOT.'/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    	
    		if($typeSLOT == 'HoldSlot') {
    			$njp = 'https://free.egtmgs.com/games/ExtraJokerSlot/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    		if($typeSLOT == 'HalloweenSlot') {
    			$njp = 'https://free.egtmgs.com/games/DarkQueenSlot/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    		if($typeSLOT == 'OceanRushSlot') {
    			$njp = 'https://free.egtmgs.com/games/ActionMoneySlot/'.$nameSLOT.'/helpNoJackpot/help_en.html';
    		}
    		
    		
    		
    		
    		$content = file_get_contents($njp);
    		$sc = strpos($content, 'Following buttons appear');
    		$ec = strpos($content, 'On the top of the game screen, the player can also see the number of the game and the local time according to his/her PC.');
    		$ec += strlen('On the top of the game screen, the player can also see the number of the game and the local time according to his/her PC.');
    		
    		$njsContent = substr($content, 0, $sc) . $m . substr($content, $ec);
    		
    		$njsContent = str_replace('../../help.css', '../../../../../help.css', $njsContent);
    		$njsContent = str_replace('../../help.js', '../../../../../help.js', $njsContent);
    		
    		if(!file_exists($p.'/helpJackpot')) {
    			mkdir($p.'/helpJackpot');
    		}
    		
    		$f = fopen($p.'/helpJackpot/help_en.html', 'w+');
    		fwrite($f, $jsContent);
    		fclose($f);
    		
    		if(!file_exists($p.'/helpNoJackpot')) {
    			mkdir($p.'/helpNoJackpot');
    		}
    		
    		$f = fopen($p.'/helpNoJackpot/help_en.html', 'w+');
    		fwrite($f, $njsContent);
    		fclose($f);
    	}
        
    }
?>
