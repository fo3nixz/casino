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
 * Class Reel
 *
 * Барабаны
 */
class Reel {
    /**
     * @var array $symbols Символы барабана. Берутся из переданной раскладки
     */
    private $symbols = array();
    /**
     * @var array $newSymbols Новый массив символов после спина
     */
    private $newSymbols = array();
    /**
     * @var array $visibleSymbols Видимые символы барабана
     */
    private $visibleSymbols = array();
    /**
     * @var array $fullVisibleSymbols Видимые символы барабана + предыдущий + следующий
     */
    private $fullVisibleSymbols = array();
    /**
     * @var int $visibleCount Количество видимых символов на барабане
     */
    private $visibleCount = 3;
    /**
     * @var int $offset Смещение барабана
     */
    private $offset = 0;

    /**
     * Устанавливаем символы барабана согласно переданной раскладке
     *
     * @param array $symbols раскладка символов
     * @param int $visibleCount Количество видимых символов
     */
    public function __construct($symbols, $visibleCount = 3) {
        $this->symbols = $symbols;
        $this->newSymbols = $symbols;
        $this->visibleCount = $visibleCount;
        $this->updateVisibleSymbols();
    }

    /**
     * Спиним барабан.
     *
     * Смещаем символы на рандомное значение.
     * Устанавливаем offset барабана
     * Обновляем видимые символы
     *
     * @param $bonus Некоторые бонусы спина барабана
     *
     * @return $this
     */
    public function spin($bonus = array()) {
        $blockEnd = false;
        if(!empty($bonus['type'])) {
            if($bonus['type'] == 'blockEndOffset') {
                $blockEnd = true;
            }
        }
        else {
            foreach($bonus as $b) {
                if(!empty($b['type'])) {
                    if($b['type'] == 'blockEndOffset') {
                        $blockEnd = true;
                    }
                }
            }
        }
        $tmp = array_merge($this->symbols, $this->symbols);
        $cnt = count($this->symbols);
        $this->offset = rnd(0, $cnt);
        if($blockEnd) {
            while($cnt - $this->offset <= $this->visibleCount) {
                $this->offset = rnd(0, $cnt);
            }
        }

        $this->newSymbols = array_slice($tmp, $this->offset, $cnt);
        $this->updateVisibleSymbols();

        return $this;
    }

    /**
     * Заменяет символ на другой
     *
     * @param int $old
     * @param int $new
     */
    public function replaceSymbols($old, $new) {
        foreach($this->newSymbols as &$s) {
            if($s == $old) {
                $s = $new;
            }
        }
        $this->updateVisibleSymbols();
    }

    /**
     * Проверка на заполнения видимых символов барабана ОДНИМ символом
     *
     * @param int $symbol Числовой идентификатор символа
     * @return bool
     */
    public function checkFullReelSymbol($symbol) {
        $unique = array_unique($this->getVisibleSymbols());

        if(count($unique) == 1 && $unique[0] == $symbol) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Функция подобна spin(), но смещение барабана мы задаем вручную
     *
     * @param int $offset На сколько сдвинуть баранан
     * @return $this
     */
    public function setOffset($offset) {
        $tmp = array_merge($this->symbols, $this->symbols);
        $cnt = count($this->symbols);
        $this->offset = $offset;
        $this->newSymbols = array_slice($tmp, $this->offset, $cnt);
        $this->updateVisibleSymbols();

        return $this;
    }

    /**
     * Получение видимых символов барабана
     *
     * @return array Видимые символы барабана
     */
    public function getVisibleSymbols() {
        return $this->visibleSymbols;
    }

    /**
     * Устанавливает видимые символы барабана
     *
     * @param array $symbols
     */
    public function setVisibleSymbols($symbols) {
        $this->visibleSymbols = $symbols;
    }

    /**
     * Получение полных видимых символов барабана
     *
     * @return array
     */
    public function getFullVisibleSymbols() {
        return $this->fullVisibleSymbols;
    }

    /**
     * Обновление видимых символов барабана. Устанавливаются как первые символы барабана(после смещения)
     */
    public function updateVisibleSymbols() {
        $this->fullVisibleSymbols = array();
        array_push($this->fullVisibleSymbols, end($this->newSymbols));
        for($i = 0; $i < $this->visibleCount; $i++) {
            $this->visibleSymbols[$i] = $this->newSymbols[$i];
            array_push($this->fullVisibleSymbols,$this->newSymbols[$i]);
        }
        if(isset($this->newSymbols[$this->visibleCount])) {
            array_push($this->fullVisibleSymbols, $this->newSymbols[$this->visibleCount]);
        }
    }

    /**
     * Получение видимого символа барабана по позиции (начинается с 0)
     *
     * @param int $pos Позиция символа
     * @return int Числовой идентификатор символа
     */
    public function getVisibleSymbol($pos) {
        return $this->visibleSymbols[$pos];
    }

    /**
     * Получение видимых символов барабана + предыдущий + последующий символ
     *
     * @param $pos
     * @return mixed
     */
    public function getFullVisibleSymbol($pos) {
        return $this->fullVisibleSymbols[$pos];
    }

    /**
     * Устанавливает барабан как wild-барабан
     *
     * @param int $wildSymbol Числовой идентификатор вайлда
     */
    public function setAsWild($wildSymbol) {
        for($i = 0; $i < $this->visibleCount; $i++) {
            $this->setSymbolOnPosition($i, $wildSymbol);
        }
    }

    /**
     * Устанавливает барабан(все его символы) как wild-барабан
     *
     * @param int $wildSymbol Числовой идентификатор вайлда
     */
    public function setAsFullWild($wildSymbol) {
        for($i = 0; $i < count($this->newSymbols); $i++) {
            $this->newSymbols[$i] = $wildSymbol;
        }
        $this->updateVisibleSymbols();
    }

    /**
     * Установка символа на определенную позицию
     *
     * @param int $pos Позиция на барабане (начинается с 0)
     * @param int $symbol Числовой идентификатор символа
     */
    public function setSymbolOnPosition($pos, $symbol) {
        $this->newSymbols[$pos] = $symbol;
        $this->updateVisibleSymbols();
    }

    /**
     * Получение позиций скаттеров на барабане
     *
     * Формирует и повзращает массив оффсетов скаттеров
     *
     * @param array $scatter Массив числовых идентификаторов скаттеров
     * @param int $iterate Номер барабана. Нужен для формирования оффсета
     * @return array
     */
    public function checkScatters($scatter, $iterate) {
        $offsets = array();
        for($i = 0; $i < $this->visibleCount; $i++) {
            if(in_array($this->visibleSymbols[$i], $scatter)) {
                $offsets[] = $i * 5 + $iterate;
            }
        }
        return $offsets;
    }

    /**
     * Получение позиций символа\символов на барабане
     *
     * Формирует и повзращает массив оффсетов символа\символов
     *
     * @param array $symbol Массив числовых идентификаторов символа\символов
     * @param int $iterate Номер барабана. Нужен для формирования оффсета
     * @return array
     */
    public function checkSymbol($symbol, $iterate) {
        $offsets = array();
        $visibleCount = $this->visibleCount - 1;
        for($i = 0; $i <= $visibleCount; $i++) {
            if(in_array($this->newSymbols[$i], $symbol)) {
                $offsets[] = $i * 5 + $iterate;
            }
        }
        return $offsets;
    }

    /**
     * Получение смещения барабана
     *
     * @return int $this->offset
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * Удаление выигрышныго символа линии и сдвиг предыдущих с добавлением 1 невидимого символа слота
     *
     * @param int $position
     */
    public function avalanche($position) {
        $del = $this->newSymbols[$position];
        unset($this->newSymbols[$position]);
        $lastSymbol = array_pop($this->newSymbols);
        array_unshift($this->newSymbols, $lastSymbol);
        $this->newSymbols = array_values($this->newSymbols);
        $this->newSymbols = array_merge(array_slice($this->newSymbols, 0, $this->visibleCount), array($del), array_slice($this->newSymbols, $this->visibleCount));

        $this->updateVisibleSymbols();
    }

    /**
     * Возвращает количество видимых символов на барабане
     *
     * @return int
     */
    public function getVisibleCount() {
        return $this->visibleCount;
    }

    /**
     * Получение массива новых символов барабана
     *
     * @return array
     */
    public function getNewSymbols() {
        return $this->newSymbols;
    }

    /**
     * Установка новых символов барабана
     *
     * @param array $symbols
     */
    public function setNewSymbols($symbols) {
        $this->symbols = $symbols;
        $this->newSymbols = $this->symbols;
    }

    /**
     * Установка смещения барабана
     *
     * @param int $offset
     */
    public function setRealOffset($offset) {
        $this->offset = $offset;
    }
}

?>
