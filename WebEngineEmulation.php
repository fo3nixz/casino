<?

set_time_limit(0);

require_once 'slot_funcs.php';

require_once 'slotTraits/SymbolsWorker.php';
require_once 'slotTraits/BonusWorker.php';

require_once 'Slot.php';
require_once 'Reel.php';
require_once 'Ctrl.php';
require_once 'Params.php';
require_once 'Ways.php';
$_SESSION['lastRequestTime'] = time();


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

            $api->setEmulation(true);

            $requests = json_decode(file_get_contents('gameEmulation/'.$api->getGameSectionStringId().'.json'), true);

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

            if(isset($_GET['spin'])) {
                $spins = $_GET['spin'];
            }
            else {
                die('No spins count');
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


            $currentPick = 0;
            for($i = 0; $i < $spins; $i++) {
                if(isset($_SESSION['state'])) {
                    if($_SESSION['state'] == 'PICK') {
                        $_POST['xml'] = str_replace('{{currentPick}}', $currentPick, $requests['pick']['data']);
                        $currentPick++;
                    }
                    elseif($_SESSION['state'] == 'GAMBLE') {
                        $_POST['xml'] = $requests['gamble']['data'];
                    }
                    else {
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
                    $_POST['xml'] = $requests['spin']['data'];
                }

                $ctrl = new $ctrlName($params);
            }
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

$WE = new WebEngine(Yii::app()->gamesApi2);

?>