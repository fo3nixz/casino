<?
require_once('egtCtrl.php');

class dragon_reelsCtrl extends egtCtrl {

    public function startLogin($request) {
        $this->setSessionIfEmpty('gambles', 0);
        $this->setSessionIfEmpty('state', 'SPIN');
        $this->setSessionIfEmpty('gambleCards', array());

        $balance = $this->getBalance() * 100;

        $json = '{
    "playerName": "igambler1515",
    "balance": '.$balance.',
    "currency": "'.$this->gameParams->curiso.'",
    "languages": ["en","ru"],
    "groups": ["all"],
    "showRtp": false,
    "multigame": true,
    "sendTotalsInfo": false,
    "complex": {
        "DRJSlot": [{
            "gameIdentificationNumber": 813,
            "recovery": "norecovery",
            "gameName": "Dragon Reels",
            "featured": false,
            "mlmJackpot": '.(($this->gameParams->jackpotEnable)?'true':'false').',
            "totalBet": 0,
            "groups": [{
                "name": "all",
                "order": 39
            }]
        }]
    },
    "sessionKey": "'.$this->sessionKey.'",
    "msg": "success",
    "messageId": "'.$this->messageId.'",
    "qName": "app.services.messages.response.LoginResponse",
    "command": "login",
    "eventTimestamp": '.$this->getTimeStamp().'
}';

        $this->out($json);

    }

    public function startSubscribe($request) {
        $state = '';

        $this->slot = new Slot($this->gameParams, 1, 1);

        $gamblesUsed = 0;
        $winAmount = 0;
        $fsUsed = 0;
        $totalFs = 0;
        $reelsLinesScatters = $this->getRandomDisplay().'
            "lines": [],
            "scatters": [],';
        if($_SESSION['state'] == 'SPIN') {
            $state = 'idle';

        }
        elseif($_SESSION['state'] == 'FREE') {
            $state = 'freespin';
            $winAmount = $_SESSION['fsTotalWin'] * 100;
            $fsUsed = $_SESSION['fsPlayed'];
            $gamblesUsed = 0;
            $totalFs = $_SESSION['fsLeft'];

            $report = unserialize(gzuncompress(base64_decode($_SESSION['report'])));
            $reelsLinesScatters = $_SESSION['reels'].$this->getWinLinesData($report).$this->getScatters($report, $this->gameParams->scatter[0]);
        }
        elseif($_SESSION['state'] == 'GAMBLE') {
            $state = 'gamble';
            $winAmount = $_SESSION['lastWin'] * 100;
            $fsUsed = 0;
            $gamblesUsed = 5 - $_SESSION['gambles'] + 1;
            $totalFs = 0;

            $report = unserialize(gzuncompress(base64_decode($_SESSION['report'])));
            $reelsLinesScatters = $_SESSION['reels'].$this->getWinLinesData($report).$this->getScatters($report, $this->gameParams->scatter[0]);
        }

        $jp = '';
        $jps = '';
        if($this->gameParams->jackpotEnable) {
            $jp = ',
            "jackpotState": {'.$this->getJackpotState().'}';

            if($_SESSION['state'] == 'JACKPOT') {
                $jps = ',
            "jackpotGameState": ['.implode(',', $_SESSION['jackpotGameState']).']';
                $state = 'jackpot';
            }


        }

        $json = '{
    "complex": {
        "currentState": {
            "gamblesUsed": '.$gamblesUsed.',
            "freespinsUsed": '.$fsUsed.',
            "previousGambles": ['.implode(',', $_SESSION['gambleCards']).'],
            "bet": 100,
            "numberOfLines": '.count($this->gameParams->winLines).',
            "denomination": 100,
            "state": "'.$state.'",
            "winAmount": '.$winAmount.',
            "firstSpin": {
                '.$reelsLinesScatters.'
                "expand": []
            },
            '.$reelsLinesScatters.'
            "expand": [],
            "gambles": '.$_SESSION['gambles'].',
            "freespins": '.$totalFs.',
            "jackpot": false'.$jps.'
        }'.$jp.'
    },
    "gameIdentificationNumber": '.$this->gameIdentificationNumber.',
    "gameNumber": 1272704999439,
    "sessionKey": "'.$this->sessionKey.'",
    "msg": "success",
    "messageId": "'.$this->messageId.'",
    "qName": "app.services.messages.response.GameEventResponse",
    "command": "subscribe",
    "eventTimestamp": '.$this->getTimeStamp().'
}';


        $this->out($json);

        if($this->gameParams->jackpotEnable) {
            $this->startJackpotAmount();
        }
    }

    protected function startSpin($request) {
        $_SESSION['state'] = 'SPIN';
        $pick = $request->bet->lines;
        $betPerLine = $request->bet->bet / 100;
        $stake = $pick * $betPerLine;

		$this->checkLastWin();

        $balance = $this->getBalance();
        if($stake > $balance) {
            die();
        }

        $this->slot = new Slot($this->gameParams, $pick, $stake);
        if($this->gameParams->jackpotEnable) {
            $this->slot->createCustomReels($this->gameParams->reels[2], $this->gameParams->reelConfig);
        }
        else {
            $this->slot->createCustomReels($this->gameParams->reels[0], $this->gameParams->reelConfig);
        }

        $spinData = $this->getSpinData();
        $totalWin = $spinData['totalWin'];
        $respin = $spinData['respin'];

        while($this->checkBankPayments($stake * 100, $totalWin * 100) || $respin) {
            $spinData = $this->getSpinData();
            $totalWin = $spinData['totalWin'];
            $respin = $spinData['respin'];
        }

        switch($spinData['report']['type']) {
            case 'SPIN':
                $this->showSpinReport($spinData['report'], $spinData['totalWin']);
                break;
            case 'FREE':
                $this->showStartFreeSpinReport($spinData['report'], $spinData['totalWin']);
                break;
        }

        $_SESSION['lastBet'] = $stake;
        $_SESSION['lastPick'] = $pick;
        $_SESSION['lastStops'] = $spinData['report']['stops'];
    }

    protected function startFreeSpin($request) {
        $pick = $_SESSION['lastPick'];
        $stake = $_SESSION['lastBet'];

        $balance = $this->getBalance();
        if($balance < 0) {
            die();
        }

        $this->slot = new Slot($this->gameParams, $pick, $stake);
        if($this->gameParams->jackpotEnable) {
            $this->slot->createCustomReels($this->gameParams->reels[3], $this->gameParams->reelConfig);
        }
        else {
            $this->slot->createCustomReels($this->gameParams->reels[1], $this->gameParams->reelConfig);
        }

        $spinData = $this->getSpinData();
        $totalWin = $spinData['totalWin'];
        $respin = $spinData['respin'];

        while($this->checkBankPayments($stake * 100, $totalWin * 100) || $respin) {
            $spinData = $this->getSpinData();
            $totalWin = $spinData['totalWin'];
            $respin = $spinData['respin'];
        }

        $this->showPlayFreeSpinReport($spinData['report'], $spinData['totalWin']);

        $_SESSION['lastBet'] = $stake;
        $_SESSION['lastPick'] = $pick;
        $_SESSION['lastStops'] = $spinData['report']['stops'];
    }

    protected function getSpinData() {
        $this->spinPays = array();
        $this->fsPays = array();
        $this->bonusPays = array();

        $respin = false;

        $bonus = array();

        if($_SESSION['state'] == 'FREE') {
            $bonus = array(
                'type' => 'multiple',
                'range' => array(2,2),
            );
        }

        $report = $this->slot->spin($bonus);

        $report['type'] = 'SPIN';

        if($_SESSION['state'] == 'SPIN') {
            $report['scattersReport'] = $this->slot->getScattersCount();
            if(!empty($this->gameParams->scatterMultiple[$report['scattersReport']['count']])) {
                $report['scattersReport']['totalWin'] = $report['bet'] * $this->gameParams->scatterMultiple[$report['scattersReport']['count']];
                $report['totalWin'] += $report['scattersReport']['totalWin'];
                $report['spinWin'] += $report['scattersReport']['totalWin'];
                if($report['scattersReport']['count'] > 2) {
                    $report['type'] = 'FREE';
                    $_SESSION['state'] = 'FREE';
                }
            }
            else {
                $report['scattersReport']['totalWin'] = 0;
            }
        }
        else {
            $a = $this->slot->getSymbolAnyCount($this->gameParams->wild[0]);
            $b = $this->slot->getSymbolAnyCount($this->gameParams->scatter[0]);
            $report['scattersReport'] = array();
            $report['scattersReport']['count'] = $a['count'] + $b['count'];
            $report['scattersReport']['offsets'] = array_merge($a['offsets'], $b['offsets']);

            if($report['scattersReport']['count'] > 1) {
                $report['scattersReport']['totalWin'] = $report['bet'] * $this->gameParams->scatterMultiple[$report['scattersReport']['count']] * 2;
                $report['totalWin'] += $report['scattersReport']['totalWin'];
                $report['spinWin'] += $report['scattersReport']['totalWin'];
                if($report['scattersReport']['count'] > 2) {
                    $_SESSION['fsLeft'] += 5;
                }

            }
            else {
                $report['scattersReport']['totalWin'] = 0;
            }
        }

        $totalWin = $report['totalWin'];

        return array(
            'totalWin' => $totalWin,
            'report' => $report,
            'respin' => $respin,
        );
    }

    public function showSpinReport($report, $totalWin) {
        if($report['totalWin'] >= $report['bet'] * 35) {
            $this->spinPays[] = array(
                'win' => $report['totalWin'],
                'report' => $report,
            );
        }
        else {
            $this->spinPays[] = array(
                'win' => 0,
                'report' => $report,
            );

        }
        $this->startPay();

        $display = $this->getDisplay();
        $winLines = $this->getWinLinesData($report);
        $balance = $this->getBalance() * 100;
        $scatters = $this->getScatters($report, $this->gameParams->scatter[0]);

        $state = 'idle';
        $_SESSION['gambles'] = $this->getGambleStepsCount($report['totalWin'], $report['bet']);
        if($report['totalWin'] > 0 && $report['totalWin'] < $report['bet'] * 35) {
            $state = 'gamble';
            $_SESSION['report'] = base64_encode(gzcompress(serialize(array(
                'winLines' => $report['winLines'],
                'reels' => $report['reels'],
                'type' => $report['type'],
                'bet' => $report['bet'],
                'betOnLine' => $report['betOnLine'],
                'linesCount' => $report['linesCount'],
                'scattersReport' => $report['scattersReport'],
            )), 9));
            $_SESSION['reels'] = $display;
            $_SESSION['state'] = 'GAMBLE';
        }

        $json = '{
    "complex": {
        '.$display.$winLines.$scatters.'
        "expand": [],
        "gambles": '.$_SESSION['gambles'].',
        "freespins": 0,
        "jackpot": false,
        "gameCommand": "bet"
    },
    "state": "'.$state.'",
    "winAmount": '.($report['totalWin']*100).',
    "gameIdentificationNumber": '.$this->gameIdentificationNumber.',
    "gameNumber": 1303752974352,
    "balance": '.$balance.',
    "sessionKey": "'.$this->sessionKey.'",
    "msg": "success",
    "messageId": "'.$this->messageId.'",
    "qName": "app.services.messages.response.GameEventResponse",
    "command": "bet",
    "eventTimestamp": '.$this->getTimeStamp().'
}';

        $_SESSION['lastWin'] = $report['totalWin'];

        $this->out($json);
    }

    public function showStartFreeSpinReport($report, $totalWin) {
        $display = $this->getDisplay();
        $winLines = $this->getWinLinesData($report);
        $balance = $this->getBalance() * 100 - $report['bet'] * 100;
        $scatters = $this->getScatters($report, $this->gameParams->scatter[0]);

        $json = '{
    "complex": {
        '.$display.$winLines.$scatters.'
        "expand": [],
        "gambles": 0,
        "freespins": 10,
        "freespinScatters": ['.$this->gameParams->scatter[0].'],
        "jackpot": false,
        "gameCommand": "bet"
    },
    "state": "freespin",
    "winAmount": '.($report['totalWin']*100).',
    "gameIdentificationNumber": '.$this->gameIdentificationNumber.',
    "gameNumber": 1304913358885,
    "balance": '.$balance.',
    "sessionKey": "'.$this->sessionKey.'",
    "msg": "success",
    "messageId": "'.$this->messageId.'",
    "qName": "app.services.messages.response.GameEventResponse",
    "command": "bet",
    "eventTimestamp": '.$this->getTimeStamp().'
}';

        $_SESSION['fsLeft'] = 10;
        $_SESSION['fsPlayed'] = 0;
        $_SESSION['fsTotalWin'] = $report['totalWin'];
        $_SESSION['report'] = base64_encode(gzcompress(serialize(array(
            'winLines' => $report['winLines'],
            'reels' => $report['reels'],
            'type' => $report['type'],
            'bet' => $report['bet'],
            'betOnLine' => $report['betOnLine'],
            'linesCount' => $report['linesCount'],
            'scattersReport' => $report['scattersReport'],
        )), 9));
        $_SESSION['reels'] = $display;

        $this->spinPays[] = array(
			'win' => 0,
            'report' => $report,
        );
        $this->startPay();

        $this->out($json);
    }

    public function showPlayFreeSpinReport($report, $totalWin) {
        $display = $this->getDisplay();
        $winLines = $this->getWinLinesData($report);
        $scatters = $this->getScatters($report, $this->gameParams->scatter[0]);

        $_SESSION['fsTotalWin'] += $report['totalWin'];
        $_SESSION['fsLeft']--;
        $_SESSION['fsPlayed']++;

        $state = 'freespin';
        $balance = '';
        $getBalance = '"balance": '.($this->getBalance() * 100 + $_SESSION['fsTotalWin']*100).',';

        if($_SESSION['fsLeft'] <= 0) {
            $state = 'idle';
            $balance = '"balance": '.($this->getBalance() * 100).',';
        }
		else {
			$this->fsPays[] = array(
				'win' => 0,
				'report' => $report,
			);
			$this->startPay();
		}

        $bonusSpins = 0;
        if($report['scattersReport']['count'] > 2) {
            $bonusSpins = 5;
        }

        $_SESSION['gambles'] = 0;
		if($_SESSION['fsTotalWin'] > 0 && $_SESSION['fsTotalWin'] < $report['bet'] * 35 && $_SESSION['fsLeft'] <= 0) {
			$state = 'gamble';
			$_SESSION['gambles'] = $this->getGambleStepsCount($_SESSION['fsTotalWin'], $report['bet']);
			$_SESSION['state'] = 'GAMBLE';
			$_SESSION['lastWin'] = $_SESSION['fsTotalWin'];
		}
		if($_SESSION['fsTotalWin'] >= $report['bet'] * 35 && $_SESSION['fsLeft'] <= 0) {
			$this->fsPays[] = array(
				'win' => $_SESSION['fsTotalWin'],
				'report' => $report,
			);
			$this->startPay();
			$balance = $getBalance;
		}

        $json = '{
    "complex": {
        '.$display.$winLines.$scatters.'
        "expand": [],
        "gambles": '.$_SESSION['gambles'].',
        "freespins": '.$bonusSpins.',
        "freespinScatters":['.$this->gameParams->scatter[0].'],
        "jackpot": false,
        "gameCommand": "bet"
    },
    "state": "'.$state.'",
    "winAmount": '.($_SESSION['fsTotalWin']*100).',
    "gameIdentificationNumber": '.$this->gameIdentificationNumber.',
    "gameNumber": 1304913358885,
    '.$balance.'
    "sessionKey": "'.$this->sessionKey.'",
    "msg": "success",
    "messageId": "'.$this->messageId.'",
    "qName": "app.services.messages.response.GameEventResponse",
    "command": "bet",
    "eventTimestamp": '.$this->getTimeStamp().'
}';

		if($_SESSION['fsLeft'] <= 0) {
			unset($_SESSION['fsLeft']);
			unset($_SESSION['fsTotalWin']);
			unset($_SESSION['fsPlayed']);
			if($state == 'idle') {
				$_SESSION['state'] = 'SPIN';
				unset($_SESSION['report']);
				unset($_SESSION['reels']);
			}
		}

        $this->out($json);
    }

}