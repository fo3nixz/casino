<?

set_time_limit(0);

/* DELETE */
if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '93.76.48.30') {
    $_GET['game'] = implode('|||', array_reverse(explode('/', $_GET['game'])));

    if(strpos($_GET['game'], '|||') === false) {
        $_GET['game'] .= '|||igt';
    }

    if(isset($_GET['sessionID'])) {
        session_id($_GET['sessionID']);
        session_start();
    }
    else {
        session_start();
    }
    require_once 'api.php';

    $apiRes = $api;
}
else {
    $apiRes = Yii::app()->gamesApi2;
}

/* DELETE */

require_once 'slot_funcs.php';

require_once 'slotTraits/SymbolsWorker.php';
require_once 'slotTraits/BonusWorker.php';

require_once 'Slot.php';
require_once 'Reel.php';
require_once 'Ctrl.php';
require_once 'Params.php';
require_once 'Ways.php';
$_SESSION['lastRequestTime'] = time();

ob_end_flush();


class WebEngine {

    public static $api;

    public static function api() {
        return self::$api;
    }

    public function __construct($api) {
        self::$api = &$api;
        $game = $api->getGameStringId();

        $ctrlName = $game.'Ctrl';
        $paramsName = $game.'Params';
        if(file_exists(__DIR__.'/gameParams/'.$api->getGameSectionStringId().'/'.$paramsName.'.php') && file_exists(__DIR__.'/gameCtrl/'.$api->getGameSectionStringId().'/'.$game.'Ctrl.php')) {
            include_once 'gameParams/'.$api->getGameSectionStringId().'/'.$paramsName.'.php';
            include_once 'gameCtrl/'.$api->getGameSectionStringId().'/'.$ctrlName.'.php';

            $nobd = false;
            if(isset($_GET['nobd'])) {
                $nobd = true;
            }

            $api->setEmulation(true);
            if($nobd) {
                $api->setWrite(false);
                $api->setInMemoryStats(true);
            }


            $requests = json_decode(file_get_contents(__DIR__.'/gameEmulation/'.$api->getGameSectionStringId().'.json'), true);

            $params = new $paramsName(crc32($api->sessionStringId));

            $gameParams = $api->getParams();
            if(!empty($gameParams)) {
                foreach($gameParams as $key=>$value) {
                    $params->$key = $value;
                }
            }

            $params->createBetConfig();


            $betOnLine = 1;
            if(isset($_GET['betOnLine'])) {
                $betOnLine = $_GET['betOnLine'];
            }

            if(empty($params->defaultCoinsCount)) {
                $maxLines = count($params->winLines);
            }
            else {
                $maxLines = $params->defaultCoinsCount;
            }
            if(isset($_GET['maxLines'])) {
                $maxLines = $_GET['maxLines'];
            }

            $totalBet = $maxLines * $betOnLine;

            if(isset($_GET['spins'])) {
                $spins = $_GET['spins'];
            }
            else {
                $spins = 4500;
            }

            $_POST['emulation'] = true;
            $_POST['xml'] = '';

            // start init
            if(isset($requests['init']['data'])) {
                $_POST['xml'] = $requests['init']['data'];
            }
            if(isset($requests['init']['url'])) {
                $_SESSION['REQUEST_URI'] = $requests['init']['url'];
            }

            $ctrl = new $ctrlName($params);

            // Update params
            if(isset($requests['spin']['data'])) {
                $requests['spin']['data'] = str_replace('{{maxLines}}', $maxLines, $requests['spin']['data']);
                $requests['spin']['data'] = str_replace('{{totalBet}}', $totalBet, $requests['spin']['data']);
                $requests['spin']['data'] = str_replace('{{betOnLine}}', $betOnLine, $requests['spin']['data']);
                $requests['spin']['data'] = str_replace('{{betOnLine100}}', $betOnLine*100, $requests['spin']['data']);
            }
            $status = 'init';


            $currentPick = 0;

            echo '<script>function rf(){document.body.innerHTML = "";}</script>';
            $time_start = microtime(true);

            $statusArray = array();
            for($i = 0; $i < $spins; $i++) {
                if($i % 50 == 0) {
                    flush();
                    echo '<script>rf();</script>';
                }
                if(isset($_SESSION['state'])) {
                    if($_SESSION['state'] == 'PICK') {
                        $status = 'pick';
                        $_POST['xml'] = str_replace('{{currentPick}}', $currentPick, $requests['pick']['data']);
                        $currentPick++;
                        $i--;
                    }
                    elseif($_SESSION['state'] == 'GAMBLE') {
                        $currentPick = 0;
                        $status = 'collect';
                        $_POST['xml'] = $requests['gamble']['data'];
                        $i--;
                    }
                    else {
                        if($_SESSION['state'] == 'FREE') {
                            $status = 'freespin';
                            $i--;
                        }
                        else {
                            $status = 'spin';
                        }

                        $currentPick = 0;
                        if(isset($requests['spin']['data'])) {
                            $_POST['xml'] = $requests['spin']['data'];
                        }
                        if(isset($requests['spin']['url'])) {
                            $_SESSION['REQUEST_URI'] = $requests['spin']['url'];
                        }
                    }
                }
                else {
                    $status = 'spin';
                    $_POST['xml'] = $requests['spin']['data'];
                }
                $elem = ($i + 1) . ' ' . $status . '<br>';

                echo $elem;

                $ctrl = new $ctrlName($params);
            }

            $time_end = microtime(true);
            $time = $time_end - $time_start;

            flush();
            echo '<script>rf();</script>';

            if($nobd) {
                $stat = $api->getStats();
                echo 'RTP - '.$stat['rtp'].'<br>';
                echo 'Bet Sum - '.$stat['bet_sum'].'<br>';
                echo 'Win Sum - '.$stat['win_sum'].'<br>';
                echo 'Spin Count - '.$stat['spin_count'].'<br>';
            }
            echo 'Total execution time in seconds: ' . number_format($time, 2, '.', '');

            unset($_SESSION['REQUEST_URI']);
        }
        else {
            $this->error();
        }
    }

    /**
     * Выводим ошибку, если игра не была найдена
     */
    protected function error() {
        echo 'Game not found';
    }
}

$WE = new WebEngine($apiRes);

?>