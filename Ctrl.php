<?php
/**
 * Casino logic
 *
 * Основные файлы логики
 *
 * @category Casino Slots
 * @author Kirill Speransky
 */


/**
 * Class Ctrl
 *
 * Контроллер игры
 */
class Ctrl {
    /**
     * @var array $request XML объект, который содержит в себе запрос от флешки
     */
    protected $request;
    /**
     * @var object $api Глобальный объект для работы с балансом и т.д
     */
    protected $api;
    /**
     * @var string $game Строковое название игры.
     */
    protected $game;
    /**
     * @var object $slot Основной объект, логика
     */
    protected $slot;
    /**
     * @var object $gameParams Параметры игры
     */
    protected $gameParams;
    /**
     * @var array $bonus Содержит информацию по текущим бонусам
     */
    public $bonus = array();
    /**
     * @var array $fsBonus Содержит информацию по текущим FSам
     */
    public $fsBonus = array();

    /**
     * @var bool Использование сохраненной ставки в сессии
     */
    public $useSessionBet = false;

    /**
     * @var array Массив выплат по спину
     */
    public $spinPays = array();
    /**
     * @var array Массив выплат по ФСам
     */
    public $fsPays = array();
    /**
     * @var array Массив выплат по бонусам
     */
    public $bonusPays = array();

    /**
     * @var bool Текущий статус для эмуляции спинов + бонусов + ФСов
     */
    public $emulation = false;

    /**
     *
     * Максимальное количество респинов, в которое слот пытается уложится в баланс банка игры.
     * Если количество > указанного, то слот выплачивает любую сумму.
     *
     * @var int
     */
    public $respinMaxCount = 100;

    /**
     *
     * Текущее количество респинов в слоте(респины проверки возможности выплаты банком игры)
     *
     * @var int
     */
    protected $respinCount = 0;

    /**
     * Обработка запроса и запуск нужного метода
     *
     * @param object $params Параметры игры
     */
    public function __construct($params) {
        $this->api = WebEngine::api();
        $this->game = $this->api->getGameStringId();
        $this->gameID = crc32($this->api->sessionStringId);

        $this->updateSessionParamsType();

        $this->request = $this->getRequest();
        $this->gameParams = $params;

        //$this->respinMaxCount = $this->gameParams->respinMaxCount;

        $this->processRequest($this->request);
    }

    /**
     * Устанавливаем некоторым переменным сессии типы
     */
    private function updateSessionParamsType() {
        $reg = '/^-?(?:\d+|\d*\.\d+)$/';
        foreach($_SESSION as $k=>$v) {
            if(!is_array($v)) {
                if(preg_match($reg, $v)) {
                    $_SESSION[$k] = floatval($v);
                }
            }

        }
    }

    /**
     * Проверка возможности выплата + установка максимального количества респинов для слота
     * Если максимальное количество респинов превышено - выплачивается последний выигрыш
     * Даже если в банке игры не хватает для этого денег
     *
     * @param int $bet
     * @param int $win
     * @param int $bonus_win
     * @param string $type
     * @param int $jackpot
     * @return bool
     */
    public function checkBankPayments($bet,$win,$bonus_win=0,$type='',$jackpot=0) {
        $this->respinCount++;
        if($this->respinCount < $this->respinMaxCount) {
            return !game_ctrl($bet, $win, $bonus_win, $type, $jackpot);
        }
        else {
            return false;
        }
    }

    /**
     * Запуск выплат по спинам, ФСам и бонусам
     */
    public function startPay() {
        foreach($this->spinPays as $s) {

            $reels = $s['report']['reels'];
            $reelsStr = '';
            foreach($reels as $r) {
                $reelsStr .= implode(' ', $r)."\n";
            }

            $this->api->setResponse($reelsStr);

            $type = (!empty($s['report']['type'])) ? strtolower($s['report']['type']) : '';

            $this->api->setRequest('bet: '.$s['report']['bet']."
betOnLine: ".$s['report']['betOnLine']."
linesCount: ".$s['report']['linesCount']."
action: ".$type);


            game_ctrl(round($this->slot->bet * 100), round($s['win'] * 100), 0, 'standart');
        }

        foreach($this->fsPays as $f) {

            $reels = $f['report']['reels'];
            $reelsStr = '';
            foreach($reels as $r) {
                $reelsStr .= implode(' ', $r)."\n";
            }

            $this->api->setResponse($reelsStr);

            $type = (!empty($f['report']['type'])) ? strtolower($f['report']['type']) : '';

            $this->api->setRequest('bet: '.$f['report']['bet']."
betOnLine: ".$f['report']['betOnLine']."
linesCount: ".$f['report']['linesCount']."
action: ".$type);


            game_ctrl(0, round($f['win'] * 100), 0, 'free');
        }

        foreach($this->bonusPays as $b) {

            $this->api->setResponse('');
            $this->api->setRequest('');

            if(!empty($b['report'])) {
                $reels = $b['report']['reels'];
                $reelsStr = '';
                foreach($reels as $r) {
                    $reelsStr .= implode(' ', $r)."\n";
                }

                $this->api->setResponse($reelsStr);

                $type = (!empty($b['report']['type'])) ? strtolower($b['report']['type']) : '';

                $this->api->setRequest('bet: '.$b['report']['bet']."
betOnLine: ".$b['report']['betOnLine']."
linesCount: ".$b['report']['linesCount']."
action: ".$type);
            }



            game_ctrl(0, 0, round($b['win'] * 100), 'bonus');
        }
    }

    /**
     * Старт выплаты денег по спину
     *
     * @param array $data
     * @param $win
     */
    protected function startPaySpin($data, $win) {
		$reels = $data['report']['reels'];
		$reelsStr = '';
		foreach($reels as $r) {
			$reelsStr .= implode(' ', $r)."\n";
		}
		$reelsStr .= 'Collect Win:'.$win*100;
		$this->api->setResponse($reelsStr);

		$type = (!empty($f['report']['type'])) ? strtolower($f['report']['type']) : '';

		$this->api->setRequest('bet: '.$data['report']['bet']."
betOnLine: ".$data['report']['betOnLine']."
linesCount: ".$data['report']['linesCount']."
action: ".$type);

		$payType = 'standart';
		if($data['report']['type'] == 'FREE') {
			$payType = 'free';
		}

		game_ctrl(0, $win * 100, 0, $payType);
	}

    /**
     * Старт выплаты денег по удвоению
     *
     * @param int $gambleWin
     */
	protected function startGamblePay($gambleWin) {
		game_ctrl(0, $gambleWin * 100, 0, 'double');
	}

    /**
     * Обработка запроса
     *
     * @param array $request
     */
    protected function processRequest($request) {
        // Тип девайса... одинаковый ответ
        if(!empty($request['DeviceTypeRequest'])) {
            $this->startDevice($request);
        }
        // баланс + выплаты + раскладки + восстановление игры
        else if(!empty($request['EEGLoadOddsRequest']) || !empty($request['FOConfigRequest'])) {
            $this->startInit($request);
        }
        // спин
        else if(!empty($request['EEGPlaceBetsRequest'])) {
            $this->startSpin($request['EEGPlaceBetsRequest']);
        }
        // получение баланса после спина
        else if(!empty($request['EEGSettleBetsRequest'])) {
            $this->startBalance($request['EEGSettleBetsRequest']);
        }
        // получение баланса + параметры ставок
        else if(!empty($request['EEGConfigRequest'])) {
            $this->startStakeConfig($request['EEGConfigRequest']);
        }
        else if(!empty($request['EEGLoadResultsRequest'])) {
            $this->startBonusResult();
        }


        else if(!empty($request['ClientDetailsRequest'])) {
            $this->startClientDetails($request['ClientDetailsRequest']);
        }
        // Сохранение состояния бонуса
        else if(!empty($request['EEGSaveStateRequest']) || !empty($request['FOSaveStateRequest'])) {
            $this->startSaveState($request);
        }
        // Запросы для бонусов и т.д
        else if(!empty($request['EEGActionRequest'])) {
            $this->startAction($request['EEGActionRequest']);
        }
        else if(isset($request['CustomerDetailsRequest'])) {
            $this->startCustomer($request['CustomerDetailsRequest']);
        }
        else if(isset($request['CustomerFunBalanceRequest'])) {
            $this->startFunBalance($request['CustomerFunBalanceRequest']);
        }
        else if(!empty($request['FOOpenGameRequest'])) {
            $this->startGameId($request['FOOpenGameRequest']);
        }
        else if(!empty($request['FOPlaceBetsRequest'])) {
            $this->startSpin($request['FOPlaceBetsRequest']);
        }
        else if(!empty($request['FOSettleBetsRequest'])) {
            $this->startBalance($request['FOSettleBetsRequest']);
        }
        else if(isset($request['FOLoadResultsRequest'])) {
            $this->startResult();
        }
    }

    /**
     * Ответ за запрос информации и клиенте
     *
     *
     */
    protected function startClientDetails() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<CompositeResponse elapsed="0" date="2015-02-08T20:05:04+0100"><ClientDetailsResponse/></CompositeResponse>';

        $this->outXML($xml);
    }

    /**
     * Получение запроса из POST
     *
     * @return array
     */
    protected  function getRequest() {
        if(isset($_POST['emulation'])) {
            $this->emulation = true;
        }

        if(isset($_POST['xml'])) return (array) simplexml_load_string($_POST['xml']);
        else return (array) simplexml_load_string($this->api->getRequestBody());

    }

    /**
     * Получение отформатированной даты
     *
     * @return string
     */
    protected function getFormatedDate() {
        return date('Y-m:d').'T'.date('H:m:s').'+0200';
    }

    /**
     * Отдаем отформатированный XML.
     *
     * @param string $xml
     */
    protected function outXML($xml) {
        $xml = str_replace(PHP_EOL, '', $xml);
        $xml = str_replace("\n", '', $xml);
        $xml = preg_replace('/> +</', '><', $xml);

        $xml = $this->roundXmlParamValue($xml, 'payout');
        $xml = $this->roundXmlParamValue($xml, 'drawWin');
        $xml = $this->roundXmlParamValue($xml, 'runningTotal');
        $xml = $this->roundXmlParamValue($xml, 'newBalance');

        echo $xml;
    }

    /**
     * Отдаем отформатированный JSON.
     *
     * @param string $json
     */
    protected function outJSON($json) {
        echo $json;
    }

    /**
     * Получение баланса игрока
     *
     * @return float
     */
    protected function getBalance() {
        $balance = $this->api->getPlayerBalance() / 100;
        if(!empty($_SESSION['bonusWIN'])) $balance -= $_SESSION['bonusWIN'];
        return round($balance, 2);
    }

    /**
     * Получение конфига ставок
     *
     * @return string
     */
    protected function getStakeParams() {
        $stake = $this->gameParams->betConfig;

        $defaultBet = $stake['defaultBet'];
        if($this->useSessionBet) {
            if(!empty($_SESSION['lastBet'])) {
                $defaultBet = $_SESSION['lastBet'] / $_SESSION['lastPick'];
            }
        }

        $xml = '<EEGConfigResponse minStake="'.$stake['minBet'].'" maxStake="'.$stake['maxBet'].'" maxWin="'.($stake['maxBet']*1000).'" defaultStake="'.$defaultBet.'" roundLimit="'.$stake['maxBet'].'">
        '.$this->gameParams->getIncreaseData().'</EEGConfigResponse>';

        return $xml;
    }

    /**
     * Ответ на запрос на девайсы
     *
     * @param array $request
     */
    protected function startDevice($request) {
        $stake = $this->gameParams->betConfig;
        $xml = '<CompositeResponse duine="1401764" elapsed="0" date="'.$this->getFormatedDate().'"><CustomerAuthenticateResponse/><CustomerDetailsResponse domain="harrycasino.com" anonymous="true" currency="'.$stake['currency'].'" currencyPrefix="'.$stake['currencyPrefix'].'"/><DeviceTypeResponse deviceID="23097"/></CompositeResponse>';
        $this->outXML($xml);
    }

    /**
     * Отправка баланса пользователя
     *
     * @param $request
     */
    protected function startFunBalance($request) {
        $xml = '<CompositeResponse elapsed="0" date="'.$this->getFormatedDate().'"><CustomerFunBalanceResponse balance="'.$this->getBalance().'"/></CompositeResponse>';

        $this->outXML($xml);
    }

    /**
     * Баланс после СПИНА и БОНУСОВ
     *
     * Когда флешка вызывает этот метод, то это значит, что спин или любой бонус закончился
     * и нужно подчистить все переменные в сессии, которые использовались бонусами.
     * Это нужно, в основном, для безопасной перезагрузки флешки с дальнейшим восстановлением
     *
     * @param array $request
     */
    protected function startBalance($request) {
        $this->clearSession();

        $xml = '<CompositeResponse elapsed="0" date="'.$this->getFormatedDate().'"><EEGSettleBetsResponse newBalance="'.$this->getBalance().'" gameId="'.$this->gameID.'"/><EEGCloseGameResponse/></CompositeResponse>';
        $this->outXML($xml);
    }

    /**
     *
     * Параметры сессии, которые должны подчиститься на startBalance
     *
     */
    protected function clearSession() {
        unset($_SESSION['drawStates']);
        unset($_SESSION['savedState']);
        unset($_SESSION['bonusWIN']);
        unset($_SESSION['bonus']);
    }

    /**
     * Баланс + данные о ставках после СПИНА
     *
     * @param array $request
     */
    protected function startStakeConfig($request) {
        $xml = '<CompositeResponse elapsed="0" date="'.$this->getFormatedDate().'"><CustomerFunBalanceResponse balance="'.$this->getBalance().'"/><EEGOpenGameResponse gameId="'.$this->gameID.'"><DrawState drawId="0"/></EEGOpenGameResponse>'.$this->getStakeParams().'</CompositeResponse>';
        $this->outXML($xml);
    }

    /**
     * Сохранение состояния флешки в бонусах
     *
     * @param array $request
     */
    protected function startSaveState($request) {
        $xml = '<CompositeResponse elapsed="0" date="'.$this->getFormatedDate().'"><EEGSaveStateResponse/></CompositeResponse>';

        $this->outXML($xml);

        $tmp = (array) $request['EEGSaveStateRequest']->SavedState;
        $attrs = $tmp['@attributes'];
        $st = '<SavedState';
        foreach($attrs as $key=>$value) {
            $st.= ' '.$key.'="'.$value.'"';
        }
        $st .= '/>';

        if(empty($_SESSION['savedState'])) $_SESSION['savedState'] = array();
        $seq = $attrs['seq'];
        $_SESSION['savedState'][$seq] = $st;
        $this->afterSaveState($attrs);

    }

    /**
     * Коллбэк, после сохранения состояния.
     *
     * @param array $attrs
     */
    protected function afterSaveState($attrs) {

    }

    /**
     * Формирование XML для выигрышных линий по переданному репорту и доп.конфигу
     *
     * @param array $report
     * @param array $override
     * @return string
     */
    protected function getWinLinesData($report, $override = array()) {
        $params = array(
            'spins' => 0,
            'currentSpins' => 0,
            'lastSpins' => 0,
            'reelset' => 0,
            'runningTotal' => 0,
            'addString' => '',
            'bonus' => '',
            'trigger' => '',
            'drawWin' => $report['spinWin'],
            'display' => 'rows',
            'winLineMultiple' => 1,
            'collecting' => false,
            'collectingSymbol' => '',
        );
        $params = $this->extendArray($params, $override);

        $runningTotal = $params['runningTotal'];

        $display = $this->gameParams->getDisplay($report[$params['display']]);

        if(empty($report['winLines']) && empty($params['bonus']) && empty($params['trigger'])) {
            $xml = '<WinLines spins="'.$params['spins'].'" currentSpins="'.$params['currentSpins'].'" lastSpins="'.$params['lastSpins'].'" runningTotal="'.$runningTotal.'" reelset="'.$params['reelset'].'" stops="'.$report['stops'].'" display="'.$display.'" drawWin="'.$params['drawWin'].'" '.$params['addString'].' />';
        }
        else {
            $xml = '<WinLines spins="'.$params['spins'].'" currentSpins="'.$params['currentSpins'].'" lastSpins="'.$params['lastSpins'].'" runningTotal="'.$runningTotal.'" reelset="'.$params['reelset'].'" stops="'.$report['stops'].'" display="'.$display.'" drawWin="'.$params['drawWin'].'" '.$params['addString'].'>';
            foreach($report['winLines'] as $winLine) {
                $star = '';
                if($winLine['withWild']) $star = '*';
                $offset = $this->slot->getOffsetsByLine($winLine['line'], $winLine['count']);
                $payout = $report['betOnLine']*$winLine['multiple']*$params['winLineMultiple'];
                $alias = $winLine['alias'];
                if($params['collecting'] && !empty($winLine['collecting'])) {
                    $alias = $params['collectingSymbol'];
                }
                $xml .= '<WinLine line="'.$winLine['id'].'" offsets="'.implode(',', $offset).'" prize="'.$winLine['count'].$alias.$star.'" length="'.$winLine['count'].'" payout="'.$payout.'" />';
            }
            $xml .= $params['bonus'];
            $xml .= '</WinLines>';
            $xml .= $params['trigger'];
        }
        return $xml;
    }

    /**
     * Удаляет указанное значение из строки выигрышных комбинаций
     *
     * @param string $str Полная строка
     * @param string $option Параметр, который нужно удалить
     * @return string
     */
    protected function deleteWinLinesOption($str, $option) {
        $p = strpos($str, $option);

        if($p) {
            $quoteOffset = strpos($str, '"', ($p + strlen($option) + 2)) + 1;

            $result = substr($str, 0, $p) . substr($str, $quoteOffset);
            return $result;
        }
        else {
            return $str;
        }
    }

    /**
     * Устанавливает новое значение параметру в XML строке
     *
     * @param string $str
     * @param string $option
     * @param mixed $newValue
     * @return string
     */

    protected function updateStringOption($str, $option, $newValue) {
        $p = strpos($str, $option);

        if($p) {
            $quoteOffset = strpos($str, '"', $p);
            $quoteOffset2 = strpos($str, '"', ($p + strlen($option) + 2)) + 1;

            $result = substr($str, 0, $quoteOffset + 1) . $newValue . substr($str, $quoteOffset2 - 1);
            return $result;
        }
        else {
            return $str;
        }
    }


    /**
     * Округляет заданные параметры в готовом XML
     *
     * @param $xml
     * @param $name
     * @return string
     *
     */
    protected function roundXmlParamValue($xml, $name) {
        $item = ' '.$name.'="';
        $inc = 0;
        while(strpos($xml, $item, $inc)) {
            $start = strpos($xml, $item, $inc) + strlen($item);
            $end = strpos($xml, '"', $start);
            $value = round(substr($xml, $start, $end-$start), 2);
            $xml = substr($xml, 0, $start) . $value . substr($xml, $end);
            $inc = $end;
        }
        return $xml;
    }

    /**
     * Округляет значения заданных параметров XMLa
     *
     * @param string $xml
     * @param string $name
     * @return string
     */
    protected function roundXmlParam($xml, $name) {
        $item = '<'.$name.'>';
        $itemClose = '</'.$name.'>';
        $inc = 0;
        while(strpos($xml, $item, $inc)) {
            $start = strpos($xml, $item, $inc) + strlen($item);
            $end = strpos($xml, $itemClose, $start);
            $value = round(substr($xml, $start, $end-$start), 2);
            $xml = substr($xml, 0, $start) . $value . substr($xml, $end);
            $inc = $end;
        }
        return $xml;
    }

    /**
     *
     * Умножает выбранные параметры на $multiple
     *
     * @param $xml
     * @param $name
     * @param $multiple
     * @return string
     *
     */
    protected function multipleXmlParamValue($xml, $name, $multiple) {
        $item = ' '.$name.'="';
        $inc = 0;
        while(strpos($xml, $item, $inc)) {
            $start = strpos($xml, $item, $inc) + strlen($item);
            $end = strpos($xml, '"', $start);
            $value = substr($xml, $start, $end-$start) * $multiple;
            $xml = substr($xml, 0, $start) . $value . substr($xml, $end);
            $inc = $end;
        }
        return $xml;
    }

    /**
     *
     * Умножает параметр на $multiple
     *
     * @param $xml
     * @param $name
     * @param $multiple
     * @return string
     */
    protected function multipleXmlParam($xml, $name, $multiple) {
        $item = '<'.$name.'>';
        $itemClose = '</'.$name.'>';
        $inc = 0;
        while(strpos($xml, $item, $inc)) {
            $start = strpos($xml, $item, $inc) + strlen($item);
            $end = strpos($xml, $itemClose, $start);
            $value = substr($xml, $start, $end-$start) * $multiple;
            $xml = substr($xml, 0, $start) . $value . substr($xml, $end);
            $inc = $end;
        }
        return $xml;
    }

    /**
     * Дополняет\перезаписывает массив $c всем из массива $p
     *
     * @param array $c
     * @param array $p
     * @return array
     */
    protected function extendArray($c, $p) {
        foreach($p as $k=>$v) {
            $c[$k] = $v;
        }
        return $c;
    }

    /**
     * Получение рандомного элемента массива
     *
     * @param array $param
     * @return mixed
     */
    protected function getRandParam($param) {
        return $param[rnd(0, count($param)-1)];
    }

    /**
     * Устанавливает дефолтное значение переменной, если она пуста
     *
     * @param string $param
     * @param mixed $value
     */
    protected function setSessionIfEmpty($param, $value) {
        if(empty($_SESSION[$param])) {
            $_SESSION[$param] = $value;
        }
    }

    /**
     * Меняет местами строки(переворачивает) в переданном оффсете.
     *
     * @param array|int $offsets
     * @return array|int
     */
    public function invertOffsets($offsets) {
        if(is_array($offsets)) {
            $newOffsets = array();
            foreach($offsets as $offset) {
                if($offset < 5) $newOffsets[] = $offset + 10;
                if($offset >= 5 && $offset <= 9) $newOffsets[] = $offset;
                if($offset > 9) $newOffsets[] = $offset - 10;
            }
        }
        else {
            $newOffsets = '';
            if($offsets < 5) $newOffsets = $offsets + 10;
            if($offsets >= 5 && $offsets <= 9) $newOffsets = $offsets;
            if($offsets > 9) $newOffsets = $offsets - 10;
        }

        return $newOffsets;
    }

    /**
     * Снижение каждого элемента массива на единицу
     *
     * @param array $array
     * @return array
     */
    public function arrayDecrease($array) {
        $c = array();
        foreach($array as $i) {
            $c[] = $i - 1;
        }
        return $c;
    }

    /**
     * Сжатие массива данных для уменьшения веса сессии
     *
     * @param $array
     * @return string
     */
    public function compressArray($array) {
        return base64_encode(gzcompress(json_encode($array), 9));
    }

    /**
     *
     * Распакова массива из строки.
     *
     * @param $string
     * @return mixed
     */
    public function decompressArray($string) {
        if(!empty($string)) {
            return json_decode(gzuncompress(base64_decode($string)), true);
        }
        else {
            return array();
        }

    }

    /**
     * Начинает логировать переданных объект в файл
     *
     * @param mixed $target То, что будет логировано
     * @param string $filename Название файла
     */
    public function startLog($target, $filename = 'log') {
        ob_start();
        print_r($target);
        $c = ob_get_contents();
        ob_end_clean();
        $f = fopen($filename, 'w+');
        fwrite($f, $c);
        fclose($f);
    }
}
