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
 * Class Api
 *
 * Сохранение\получение данных игрока, сессии
 */
class Api {
    /**
     * @var stdClass @gameSession объект сессии игры
     */
    public $gameSession;

    /**
     * Api constructor.
     * Создание сессии, установка параметров по умолчанию и переданных
     */
    public function __construct() {
        $this->gameSession = new stdClass();
        $this->gameSession->game = new stdClass();
        if(isset($_GET['game'])) {
            $tmp = $_GET['game'];
            $p = explode('|||', $_GET['game']);
            $this->gameSession->game->string_id = $p[0];
            $this->sectionId = $p[1];

            if(!empty($p[2])) {
                $_GET['bonus'] = $p[2];
            }
        }
        else {
            if(isset($_SESSION['start_game'])) {
                $this->gameSession->game->string_id = $_SESSION['start_game'];
                $this->sectionId = $_SESSION['start_publisher'];
            }
        }

        
        $this->sessionStringId = 1411559061;
        
        if(empty($_SESSION['balance'])) $_SESSION['balance'] = 1000000000;
        
        $this->playerBalance = $_SESSION['balance'];
    }

    /**
     * Установка нового значения баланса
     *
     * @param int $value
     */
    public function setBalance($value) {
        $this->playerBalance = $value;
        $_SESSION['balance'] = $value;
    }

    /**
     * Получает REQUEST BODY запроса
     *
     * @return string
     */
    public function getRequestBody() {
        return file_get_contents('php://input');
    }

    /**
     * Получение конфигов игры
     *
     * @return array
     */
    public function getConfigVars() {
        return array();
    }

    /**
     * Получение параметров запуска игры
     *
     * @return array
     */
    public function getLaunchParams() {
        return array();
    }

    /**
     * Получение ID игры
     *
     * @return mixed
     */
    public function getGameStringId() {
        return $this->gameSession->game->string_id;
    }

    /**
     * Получение секции игры
     *
     * @return mixed
     */
    public function getGameSectionStringId() {
        return $this->sectionId;
    }

    /**
     * Устанавливает кастомный ответ в лог спина
     *
     * @param $string
     */
    public function setResponse($string) {

    }

    /**
     * Устанавливает кастомный запрос в лог спина
     *
     * @param $string
     */
    public function setRequest($string) {

    }

    /**
     * Получение баланса игрока
     *
     * @return int
     */
    public function getPlayerBalance() {
        return $this->playerBalance;
    }

    public function getJackpots() {
        return array(
            "1" => "10000",
            "2" => "20000",
            "3" => "30000",
            "4" => "40000",
        );
    }

    public function setEmulation($flag) {
        return true;
    }

    public function getParams() {
        return array();
    }

}

$api = new Api;
