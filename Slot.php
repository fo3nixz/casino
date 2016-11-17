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
 * Class Slot
 *
 * Основная логика барабанов, спинов, бонусов.
 */
class Slot {
    // Используем трейты.
    use SymbolsWorker, BonusWorker;

    /**
     * @var array $symbols Ассотиативный массив буквенного и числового значения символа
     */
    public $symbols = array();

    /**
     * @var array $reels Содержит массив барабанов слота. Каждый элемент - экземпляр Reel
     */
    public $reels = array();

    /**
     * @var array $lines Массив выигрышных линий.
     * Количество линий в массиве зависит от того, сколько линий выбрано в игре.
     */
    public $lines = array();

    /**
     * @var array $wins Массив выплат.
     * symbol - буквенной идентификатор символа
     * count - количество символов
     * multiplier - множитель ставки на линию
     */
    public $wins = array();

    /**
     * @var int $totalMultiple Суммарный множитель после спина (учитывая бонусы спина)
     */
    private $totalMultiple = 0;

    /**
     * @var array $wild Массив вайлдов слота. Может быть как 1 элемент(чаще всего), так и несколько
     */
    public $wild = array();

    /**
     * @var array $scatter Массив скаттеров слота. Может быть как 1 элемент(чаще всего), так и несколько
     */
    public $scatter = array();

    /**
     * @var array $winLines Список выигрышных линий слота за спин
     */
    public $winLines = array();

    /**
     * @var array $report Содержит подробную информацию по результатам спина.
     *
     * winLines - массив линий, на которых выиграли
     * totalMultiple - полный множитель ставки на линию
     * offset - Массив сдвига барабанов
     * rows - Конечные символы строк(не барабанов, а именно строк) слота после спина и применения всех бонусов и т.д
     * startRows - Начальные строки слота после спина и ДО применения бонусов и т.д
     * bonusData - Дополнительная информация по бонусам
     * double - Множитель всех выигрышей
     * bet - Общая ставка
     * linesCount - Количество играющих линий
     * betOnLine - Ставка на линию
     * stops - Строка сдвига барабанов (отображает сдвиг второй строки)
     * totalWin - Общая сумма выигрыша
     */
    private $report = array();

    /**
     * @var array $bonusData Массив данных бонуса.
     * Используется для передачи дополнительной информации по бонусам (положение рандомных вайлдов и т.д)
     */
    private $bonusData = array();

    /**
     * @var int Номер запуска спина и бонусных спинов
     */
    private $step = 0;

    /**
     * Контроллер, который использует слот
     *
     * @var bool|object
     */
    private $ctrl = false;

    /**
     * @var int Номер отрисовки
     */
    public $drawID;

    /**
     * @var array Бонус спина
     */
    private $spinBonus = array();

    /**
     * @var int Количество строк слота
     */
    public $rows = 3;

    /**
     * Дополнительные вайлды-множители, которые при попадании на линюю умножают ее
     *
     * @var array
     */
    public $bonusWildsMultiple = array();

    /**
     * Итератор, для выхода из зациклившегося бонуса и респина
     *
     * @var int
     */
    public $bonusIterateCount = 0;

    /**
     * Множитель(multiplier) выигрышных линий
     *
     * @var int
     */
    public $double = 1;

    /**
     * @var float Ставка на линию с учетом индекса
     */
    public $betOnLine;

    /**
     * @var array Стартовые строки слота до применения бонусов
     */
    public $startRows;

    /**
     * @var array Стартовые строки слота до применения бонусов в расширенном виде(+1 строка снизу и сверху)
     */
    public $startFullRows;

    /**
     * Инициализация слота
     *
     * Получение выигрышных линий в зависимости от количества играющих линий
     * Создание массива символов(без повторов), которые учавствуют в выплате
     * Создание барабанов слота из раскладок слота.
     *
     * @param object $params Параметры текущей игры
     * @param int $linesCount Количество линий, по которым будет считаться выигрыш
     * @param float $bet Общая ставка
     * @param float $betOnLineIndex Дополнительный множитель для правильного рассчета ставки на линию
     */
    public function __construct($params, $linesCount, $bet, $betOnLineIndex = 1) {
        $this->params = $params;
        $this->lines = $this->getWinLines($params->winLines, $linesCount);
        $this->wins = $params->winPay;
        $this->winSymbols = $this->getWinSymbols();
        $this->symbols = $params->symbols;
        $this->wild = $params->wild;
        $this->scatter = $params->scatter;
        $this->drawID = -1;

        $this->bet = $bet;
        $this->linesCount = (int) $linesCount;
        $this->betOnLine = (float) ($bet * $betOnLineIndex / $linesCount);

        if(round($this->betOnLine,3) < 0.01) {
            die('bad bet');
        }
        $this->betOnLine = round($this->betOnLine, 2);

        foreach($params->reels[0] as $reel) {
            $this->reels[] = new Reel($reel);
        }
    }

    /**
     * Установка контроллера для слота
     *
     * @param Ctrl $ctrl
     */
    public function setCtrl(Ctrl $ctrl) {
        $this->ctrl = $ctrl;
    }

    /**
     * Установка новых параметров игры
     *
     * @param Params $params
     */
    public function setParams(Params $params) {
        $this->params = $params;

        $this->lines = $this->getWinLines($params->winLines, $this->linesCount);
        $this->wins = $params->winPay;
        $this->winSymbols = $this->getWinSymbols();
        $this->symbols = $params->symbols;
        $this->wild = $params->wild;
        $this->scatter = $params->scatter;
        $this->drawID = -1;
    }

    /**
     * Получаем список линий, в зависимости от выигрышных линий
     *
     * @param array $lines Все выигрышные линии слота
     * @param int $linesCount Количество играющих линий
     * @return array Текущие выигрышные линии слота
     */
    public function getWinLines($lines, $linesCount) {
        return array_slice($lines, 0, $linesCount);
    }

    /**
     * Получаем список символов, которые учавствуют в выплате
     *
     * @return array
     */
    public function getWinSymbols() {
        $s = array();
        $c = array();
        foreach($this->wins as $win) {
            $symbol = $win['symbol'];
            if(!in_array($symbol, $c)) {
                $s[] = array('symbol' => $symbol);
                $c[] = $symbol;
            }
        }
        return $s;
    }

    /**
     * Установка новых барабанов
     *
     * @param array $params Создание новых барабанов из переданной раскладки
     */
    public function setReels($params) {
        $this->reels = array();
        foreach($params as $reel) {
            $this->reels[] = new Reel($reel);
        }
    }

    /**
     * Получение количество барабанов слота
     *
     * @return int
     */
    public function getReelsCount() {
        return count($this->reels);
    }
    /**
     * Устанавливает новые барабаны с нужным количеством видимых символов
     *
     * @param array $reels
     * @param array $config Массив, содержащий количество видимых символов для каждого барабана
     */
    public function createCustomReels($reels, $config) {
        $this->reels = array();

        for($i = 0; $i < count($reels); $i++) {
            $this->reels[] = new Reel($reels[$i], $config[$i]);
        }
    }

    /**
     * Получает массив, в котором содержатся данные для восстановление состояния барабанов
     *
     * @return array
     */
    public function getReelsCreateArray() {
        $config = array();
        foreach($this->reels as $r) {
            $config[] = array(
                'symbols' => $r->getNewSymbols(),
                'offset' => $r->getOffset(),
                'visible' => $r->getVisibleSymbols(),
            );
        }

        return $config;
    }

    /**
     *
     * Восстанавливает состояние барабанов при обвалах и т.д
     *
     * @param array $config Массив данных(состояние барабанов) из метода getReelsCreateArray
     */

    public function setReelsCreate($config) {
        $n = 0;
        foreach($this->reels as $r) {
            $r->setNewSymbols($config[$n]['symbols']);
            $r->setRealOffset($config[$n]['offset']);
            $r->setVisibleSymbols($config[$n]['visible']);
            $n++;
        }
    }

    /**
     * Установка нового массива вайлдов
     *
     * @param array $wilds
     */
    public function setWilds($wilds) {
        $this->wild = $wilds;
    }

    /**
     * Установка нового массива скаттеров
     *
     * @param array $scatter
     */
    public function setScatter($scatter) {
        $this->scatter = $scatter;
    }

    /**
     * Крутим барабаны и создаем репорт спина.
     *
     * Инициализация начальных параметров репорта.
     * Кручение барабанов
     * Проверка и запуск бонусов
     * Создание репорта
     *
     * @param array $bonus Бонус(или бонусы) для спина
     * @return array $report Полная информация по спину
     */
    public function spin($bonus = array()) {
        $this->resetSlotData();

        $this->step = 0;

        foreach($this->reels as $reel) {
            $reel->spin($bonus);
        }

        $this->startRows = $this->getRows();
        $this->startFullRows = $this->getFullRows();

        $this->spinBonus = $bonus;

        return $this->getReport();
    }

    /**
     * Проверка бонусов и создание репорта спина
     *
     * @return array Полный репорт спина
     */
    public function getReport() {
        if($this->params->extraLine) {
            if(!isset($this->params->extraLineConfig['afterBonus'])) {
                $extraLines = $this->getExtraLine($this->params->extraLineConfig);
                foreach($extraLines as $e) {
                    $this->winLines[] = $e;
                }
            }

        }

        $this->checkBonus();

        if($this->params->extraLine) {
            if(isset($this->params->extraLineConfig['afterBonus'])) {
                $extraLines = $this->getExtraLine($this->params->extraLineConfig);
                foreach($extraLines as $e) {
                    $this->winLines[] = $e;
                }
            }
        }

        $this->drawID++;

        return $this->makeReport();
    }

    /**
     * Сброс основных параметров слота до спина
     */
    public function resetSlotData() {
        $this->totalMultiple = 0;
        $this->winLines = array();
        $this->report = array();
        $this->double = 1;
        $this->bonusData = array();
        $this->bonusWildsMultiple = array();
    }

    /**
     * Установка дефолтных (нулевых) раскладок барабанов для слота. Нужно при респине.
     */
    public function setDefaultReels() {
        $this->setReels($this->params->reels[0]);
    }

    /**
     * Установка бонуса спина
     *
     * @param array $bonus
     */
    public function setBonus($bonus) {
        $this->spinBonus = $bonus;
    }

    /**
     * Создание репорта спина
     *
     * @return array
     */
    public function makeReport() {
        foreach($this->winSymbols as $winLine) {
            // Номер символа

            $alias = $winLine['symbol'];
            // ARRAY
            $v = $this->params->getSymbolID($winLine['symbol']);
            // Получаем все комбинации
            if($this->params->winLineType == 'left') {
                $SymbolwinLines = $this->getLeft($v);
            }
            if($this->params->winLineType == 'leftRight') {
                $SymbolwinLines = $this->getLeftRight($v);
            }
            elseif($this->params->winLineType == '243') {
                $SymbolwinLines = $this->get243($v);
            }
            elseif ($this->params->winLineType == 'ways') {
                $SymbolwinLines = $this->getWays($v);
            }
            elseif ($this->params->winLineType == 'waysRight') {
                $SymbolwinLines = $this->getWaysRight($v);
            }
            elseif ($this->params->winLineType == 'waysLeftRight') {
                $SymbolwinLines = $this->getWaysLeftRight($v);
            }
            elseif ($this->params->winLineType == 'waysLeftRightMiddle') {
                $SymbolwinLines = $this->getWaysLeftRightMiddle($v);
            }
            elseif ($this->params->winLineType == 'lineWays') {
                $SymbolwinLines = $this->getLineWays($v);
            }
            else {
                $SymbolwinLines = $this->getLeft($v);
            }

            foreach($SymbolwinLines as $w) {
                $symbolIncluded = false;

                foreach($w['useSymbols'] as $useSymbol) {
                    if(in_array($useSymbol, $v) || isset($w['alias'])) {
                        $symbolIncluded = true;
                    }
                }

                if($this->params->checkSymbolCount($v, $w['count'], $w['type']) && $symbolIncluded) {
                    $multiplier = $this->params->getWinMultiplier($v, $w['count'], $w['type']);
                    $addMultiplier = array();
                    foreach($this->bonusWildsMultiple as $bonusWild) {
                        if(in_array($bonusWild['offset'], $w['line'])) {
                            $multiplier *= $bonusWild['multiple'];
                            $addMultiplier[] = $bonusWild['multiple'];
                        }
                    }
                    if($this->params->payOnlyHighter) {
                        $f = true;
                        foreach ($this->winLines as $k=>$zzz) {
                            if($this->params->winLineType == 'left' || $this->params->winLineType == 'leftRight') {
                                if ($zzz['id'] == $w['id'] + 1) {
                                    if ($zzz['multiple'] > $multiplier * $w['double']) {
                                        $f = false;
                                    } else {
                                        $f = true;
                                        unset($this->winLines[$k]);
                                    }
                                }
                            }
                            else {
                                if($zzz['id'] == $w['id'] && strlen($zzz['id'] == strlen($w['id']))) {
                                    if($zzz['multiple'] > $multiplier * $w['double']) {
                                        $f = false;
                                    } else {
                                        $f = true;
                                        unset($this->winLines[$k]);
                                    }
                                }
                            }
                        }
                        if ($f) {
                            $addArray = array(
                                'line' => $w['line'],
                                'multiple' => round($multiplier * $w['double']),
                                'symbol' => $v,
                                'alias' => $alias,
                                'count' => $w['count'],
                                'id' => $w['id'] + 1,
                                'double' => round($w['double']),
                                'withWild' => $w['withWild'],
                                'addMultiplier' => $addMultiplier,
                                'direction' => $w['direction'],
                                'useSymbols' => $w['useSymbols'],
                                'type' => $w['type'],
                            );
                            if(!empty($w['collecting'])) {
                                $addArray['collecting'] = $w['collecting'];
                            }
                            $this->winLines[] = $addArray;
                        }
                    }
                    else {
                        $this->totalMultiple += $multiplier * $w['double'];
                        $addArray = array(
                            'line' => $w['line'],
                            'multiple' => round($multiplier * $w['double']),
                            'symbol' => $v,
                            'alias' => $alias,
                            'count' => $w['count'],
                            'id' => $w['id'] + 1,
                            'collecting' => $w['collecting'],
                            'double' => round($w['double']),
                            'withWild' => $w['withWild'],
                            'addMultiplier' => $addMultiplier,
                            'direction' => $w['direction'],
                            'useSymbols' => $w['useSymbols'],
                            'type' => $w['type'],
                        );
                        if(!empty($w['collecting'])) {
                            $addArray['collecting'] = $w['collecting'];
                        }
                        $this->winLines[] = $addArray;

                    }
                }
            }
        }

        if($this->params->payOnlyHighter) {
            $this->totalMultiple = 0;
            foreach($this->winLines as $w) {
                $this->totalMultiple += $w['multiple'];
            }
        }

        $this->totalMultiple = round($this->totalMultiple);

        $this->report = array(
            'winLines' => $this->winLines,
            'totalMultiple' => round($this->totalMultiple),
            'offset' => $this->getOffsets(),
            'reels' => $this->getReels(),
            'rows' => $this->getRows(),
            'fullRows' => $this->getFullRows(),
            'startRows' => $this->startRows,
            'startFullRows' => $this->startFullRows,
            'bonusData' => $this->bonusData,
            'double' => round($this->double),
            'bet' => round($this->bet, 2),
            'linesCount' => round($this->linesCount),
            'betOnLine' => round($this->betOnLine, 2),
            'stops' => implode(',', $this->getOffsets()[1]),
            'totalWin' => round($this->betOnLine * $this->totalMultiple, 2),
            'spinWin' => round($this->betOnLine * $this->totalMultiple, 2),
            'drawID' => $this->drawID,
            'addDraws' => '',
        );

        return $this->report;
    }

    /**
     * Получение дополнительных линий по конфигу
     *
     * @param $config Конфиг
     * @return array Список выигрышных линий в такой же формате, как и в report
     */
    public function getExtraLine($config) {
        $winLines = array();

        $lineId = 0;
        foreach($this->lines as $line) {
            $lineSymbol = $this->getLineSymbols($line);
            if($config['any']) {
                $diffCount = count(array_diff($lineSymbol, $config['symbols']));
            }
            else {
                $diffCount = count(array_diff($config['symbols'], $lineSymbol));
            }
            if($diffCount == 0) {
                $winLines[] = array(
                    'line' => $line,
                    'multiple' => $config['multiplier'],
                    'symbol' => $config['alias'],
                    'alias' => $config['alias'],
                    'count' => count($lineSymbol),
                    'id' => $lineId + 1,
                    'collecting' => false,
                    'double' => false,
                    'withWild' => false,
                    'addMultiplier' => array(),
                    'direction' => 'left',
                    'useSymbols' => $lineSymbol,
                    'type' => 'line',
                );
            }
            $lineId++;
        }

        return $winLines;
    }

    /**
     * Получаем список для каждого символа по каждой линии слева на право
     *
     * Возвращает массив, содержащий описание линий, на которых символов >= minWinCount
     *
     * @param array $symbol Массив числовых идентификаторов символа
     * @return array
     */
    private function getLeft($symbol) {
        $winLines = array();
        $lineId = 0;
        $bonusWildPosition = array();
        foreach($this->bonusWildsMultiple as $b) {
            $bonusWildPosition[] = $b['offset'];
        }
        foreach($this->lines as $line) {
            $lineSymbol = $this->getLineSymbols($line);
            $cnt = 0;
            $f = true;
            $double = 1;
            $withWild = false;
            $lineSymbolCount = 0;
            $collecting = false;
            $useSymbols = array();
            foreach($lineSymbol as $s) {
                $symbolPosition = $line[$lineSymbolCount];
                if(in_array($s, $symbol) && $f) {
                    $cnt++;
                    $useSymbols[] = $s;
                    if($this->params->doubleCount) {
                        if(!empty($this->params->doubleCountConfig[$s])) {
                            $p = $this->params->doubleCountConfig[$s];
                            if(in_array($symbol[0], $p)) {
                                $cnt++;
                            }
                        }
                    }
                }
                elseif(in_array($s, $this->wild) && $f && !in_array($symbol[0], $this->scatter) && count(array_intersect($symbol, $this->params->symbolWithoutWild)) == 0) {
                    if($this->params->blockWildsOnReel) {
                        if(in_array($lineSymbolCount, $this->params->blockWildReels)) {
                            $f = false;
                        }
                        else {
                            $withWild = true;
                            $cnt++;
                            $useSymbols[] = $s;
                            if($this->params->doubleCount) {
                                if(!empty($this->params->doubleCountConfig[$s])) {
                                    $p = $this->params->doubleCountConfig[$s];
                                    if(in_array($symbol[0], $p)) {
                                        $cnt++;
                                    }
                                }
                            }
                            if($this->params->doubleIfWild) {
                                if(!in_array($symbolPosition, $bonusWildPosition)) {
                                    $double = 2;
                                }
                            }
                        }
                    }
                    else {
                        $withWild = true;
                        $cnt++;
                        $useSymbols[] = $s;
                        if($this->params->doubleCount) {
                            if(!empty($this->params->doubleCountConfig[$s])) {
                                $p = $this->params->doubleCountConfig[$s];

                                if(in_array($symbol[0], $p)) {
                                    $cnt++;
                                }
                            }
                        }
                        if($this->params->doubleIfWild) {
                            if(!in_array($symbolPosition, $bonusWildPosition)) {
                                $double = 2;
                            }
                        }
                    }

                }
                elseif($this->params->collectingPay && !in_array($s, $symbol) && $f && $lineSymbolCount > 0) {
                    if(is_array($this->params->collectingSymbols[0])) {
                        $g = false;
                        foreach($this->params->collectingSymbols as $cs) {
                            if(in_array($s, $cs) && in_array($symbol[0], $cs)) {
                                $collecting = true;
                                $cnt++;
                                $useSymbols[] = $s;
                                if($this->params->doubleCount) {
                                    if(!empty($this->params->doubleCountConfig[$s])) {
                                        $p = $this->params->doubleCountConfig[$s];
                                        if(in_array($symbol[0], $p)) {
                                            $cnt++;
                                        }
                                    }
                                }
                                $g = true;
                            }
                        }
                        if(!$g) {
                            $f = false;
                        }
                    }
                    else {
                        if(in_array($s, $this->params->collectingSymbols) && in_array($symbol[0], $this->params->collectingSymbols)) {
                            $collecting = true;
                            $cnt++;
                            $useSymbols[] = $s;
                            if($this->params->doubleCount) {
                                if(!empty($this->params->doubleCountConfig[$s])) {
                                    $p = $this->params->doubleCountConfig[$s];
                                    if(in_array($symbol[0], $p)) {
                                        $cnt++;
                                    }
                                }
                            }
                        }
                        else {
                            $f = false;
                        }
                    }

                }
                else {
                    $f = false;
                }
                $lineSymbolCount++;
            }
            if($cnt >= $this->params->minWinCount) {
                if($this->params->allCanDouble) {
                    $winLines[] = array(
                        'line' => $line,
                        'count' => $cnt,
                        'id' => $lineId,
                        'double' => $this->double * $double,
                        'withWild' => $withWild,
                        'collecting' => $collecting,
                        'direction' => 'left',
                        'useSymbols' => $useSymbols,
                        'type' => 'line',
                    );
                }
                else {
                    $banString = (string) $symbol[0] .'-'. (string) $cnt;
                    if(in_array($banString, $this->params->banSymbols)) {
                        $resultDouble = $double;
                    }
                    else {
                        $resultDouble = $this->double * $double;
                    }
                    $winLines[] = array(
                        'line' => $line,
                        'count' => $cnt,
                        'id' => $lineId,
                        'double' => $resultDouble,
                        'withWild' => $withWild,
                        'collecting' => $collecting,
                        'direction' => 'left',
                        'useSymbols' => $useSymbols,
                        'type' => 'line',
                    );
                }

            }


            $lineId++;
        }
        return $winLines;
    }

    /**
     * Получаем список для каждого символа по каждой линии справо налево
     *
     * Возвращает массив, содержащий описание линий, на которых символов >= minWinCount
     *
     * @param array $symbol Массив числовых идентификаторов символа
     * @return array
     */
    private function getRight($symbol) {
        $winLines = array();
        $lineId = 0;
        $bonusWildPosition = array();
        foreach($this->bonusWildsMultiple as $b) {
            $bonusWildPosition[] = $b['offset'];
        }
        foreach($this->lines as $line) {
            $lineSymbol = array_reverse($this->getLineSymbols($line));
            $cnt = 0;
            $f = true;
            $double = 1;
            $withWild = false;
            $lineSymbolCount = 0;
            $collecting = false;
            $useSymbols = array();
            foreach($lineSymbol as $s) {
                $symbolPosition = $line[$lineSymbolCount];
                if(in_array($s, $symbol) && $f) {
                    $cnt++;
                    $useSymbols[] = $s;
                    if($this->params->doubleCount) {
                        if(!empty($this->params->doubleCountConfig[$s])) {
                            $p = $this->params->doubleCountConfig[$s];
                            if(in_array($symbol[0], $p)) {
                                $cnt++;
                            }
                        }
                    }
                }
                elseif(in_array($s, $this->wild) && $f && !in_array($symbol[0], $this->scatter) && count(array_intersect($symbol, $this->params->symbolWithoutWild)) == 0) {
                    if($this->params->blockWildsOnReel) {
                        if(in_array($lineSymbolCount, $this->params->blockWildReels)) {
                            $f = false;
                        }
                        else {
                            $withWild = true;
                            if($this->params->doubleIfWild) {
                                if(!in_array($symbolPosition, $bonusWildPosition)) {
                                    $double = 2;
                                }
                            }

                            $cnt++;
                            $useSymbols[] = $s;
                            if($this->params->doubleCount) {
                                if(!empty($this->params->doubleCountConfig[$s])) {
                                    $p = $this->params->doubleCountConfig[$s];
                                    if(in_array($symbol[0], $p)) {
                                        $cnt++;
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $withWild = true;
                        if($this->params->doubleIfWild) {
                            if(!in_array($symbolPosition, $bonusWildPosition)) {
                                $double = 2;
                            }
                        }

                        $cnt++;
                        $useSymbols[] = $s;
                        if($this->params->doubleCount) {
                            if(!empty($this->params->doubleCountConfig[$s])) {
                                $p = $this->params->doubleCountConfig[$s];
                                if(in_array($symbol[0], $p)) {
                                    $cnt++;
                                }
                            }
                        }
                    }

                }
                elseif($this->params->collectingPay && !in_array($s, $symbol) && $f && $lineSymbolCount > 0) {
                    if(is_array($this->params->collectingSymbols[0])) {
                        $g = false;
                        foreach($this->params->collectingSymbols as $cs) {
                            if(in_array($s, $cs) && in_array($symbol[0], $cs)) {
                                $collecting = true;
                                $cnt++;
                                $useSymbols[] = $s;
                                if($this->params->doubleCount) {
                                    if(!empty($this->params->doubleCountConfig[$s])) {
                                        $p = $this->params->doubleCountConfig[$s];
                                        if(in_array($symbol[0], $p)) {
                                            $cnt++;
                                        }
                                    }
                                }
                                $g = true;
                            }
                        }
                        if(!$g) {
                            $f = false;
                        }
                    }
                    else {
                        if(in_array($s, $this->params->collectingSymbols) && in_array($symbol[0], $this->params->collectingSymbols)) {
                            $collecting = true;
                            $cnt++;
                            $useSymbols[] = $s;
                            if($this->params->doubleCount) {
                                if(!empty($this->params->doubleCountConfig[$s])) {
                                    $p = $this->params->doubleCountConfig[$s];
                                    if(in_array($symbol[0], $p)) {
                                        $cnt++;
                                    }
                                }
                            }
                        }
                        else {
                            $f = false;
                        }
                    }
                }
                else {
                    $f = false;
                }
                $lineSymbolCount++;
            }
            if($cnt != count($this->reels)) {
                if($cnt >= $this->params->minWinCount) {
                    if($this->params->allCanDouble) {
                        $winLines[] = array(
                            'line' => $line,
                            'count' => $cnt,
                            'id' => $lineId,
                            'double' => $this->double * $double,
                            'withWild' => $withWild,
                            'collecting' => $collecting,
                            'direction' => 'right',
                            'useSymbols' => $useSymbols,
                            'type' => 'line',
                        );
                    }
                    else {
                        $banString = (string) $symbol[0] .'-'. (string) $cnt;
                        if(in_array($banString, $this->params->banSymbols)) {
                            $resultDouble = $double;
                        }
                        else {
                            $resultDouble = $this->double * $double;
                        }
                        $winLines[] = array(
                            'line' => $line,
                            'count' => $cnt,
                            'id' => $lineId,
                            'double' => $resultDouble,
                            'withWild' => $withWild,
                            'collecting' => $collecting,
                            'direction' => 'right',
                            'useSymbols' => $useSymbols,
                            'type' => 'line',
                        );
                    }

                }
            }
            $lineId++;
        }
        return $winLines;
    }

    /**
     * Получение Left to Right и Right to Left линий
     *
     * @param array $symbol
     * @return array
     */
    private function getLeftRight($symbol) {
        $left = $this->getLeft($symbol);
        $right = $this->getRight($symbol);

        return array_merge($left, $right);
    }

    /**
     * Получаем список выигрышных линий для символа по 243 ways
     *
     * Возвращает массив, содержащий описание линий, на которых символов >= minWinCount
     *
     * @param array $symbol Массив числовых идентификаторов символа
     * @return array
     */
    private function get243($symbol) {
        $winLines = array();
        $cnt = 0;
        $offsets = array();
        /*
         * Получаем длину всех путей, закидываем оффсеты символов и тип символа(обычный или вайлд)
         */
        $symbolPresent = false;

        $bonusWildPosition = array();
        foreach($this->bonusWildsMultiple as $b) {
            $bonusWildPosition[] = $b['offset'];
        }
        for($i = 0; $i <= 4; $i++) {
            $offsets[$i] = array();
            $update = false;
            for($j = 0; $j < $this->rows; $j++) {
                $symbolOffset = $this->getSymbolPositionOnReel($symbol, $i, $j);
                $wildOffset = $this->getSymbolPositionOnReel($this->wild, $i, $j);
                if($symbolOffset !== false) {
                    $symbolPresent = true;
                    $update = true;
                    $type = 'symbol';
                    if($this->wild[0] == $symbol[0]) {
                        $type = 'wild';
                    }
                    $offsets[$i][] = array(
                        'offset' => $symbolOffset,
                        'type' => $type,
                    );
                }
                elseif($wildOffset !== false) {
                    $update = true;
                    $type = 'wild';
                    if(in_array($wildOffset, $bonusWildPosition)) {
                        $type = 'symbol';
                    }
                    $offsets[$i][] = array(
                        'offset' => $wildOffset,
                        'type' => $type,
                    );
                }
            }

            if($update) {
                $cnt++;
            }
            else {
                break;
            }
        }
        if($cnt >= $this->params->minWinCount && $symbolPresent) {
            for($ccc = 0; $ccc <= 4; $ccc++) {
                if(empty($offsets[$ccc])) $offsets[$ccc] = array();
            }

            $s1 = count($offsets[0]);
            do {
                if(isset($offsets[0][$s1-1])) {
                    $item = $offsets[0][$s1-1];
                    $s1Offset = $item['offset'];
                    $s1Wild = ($item['type'] == 'wild') ? true : false;
                }
                $s2 = count($offsets[1]);
                do {
                    if(isset($offsets[1][$s2-1])) {
                        $item = $offsets[1][$s2-1];
                        $s2Offset = $item['offset'];
                        $s2Wild = ($item['type'] == 'wild') ? true : false;
                    }
                    $s3 = count($offsets[2]);
                    do {
                        if(isset($offsets[2][$s3-1])) {
                            $item = $offsets[2][$s3-1];
                            $s3Offset = $item['offset'];
                            $s3Wild = ($item['type'] == 'wild') ? true : false;
                        }
                        $s4 = count($offsets[3]);
                        do {
                            if(isset($offsets[3][$s4-1])) {
                                $item = $offsets[3][$s4-1];
                                $s4Offset = $item['offset'];
                                $s4Wild = ($item['type'] == 'wild') ? true : false;
                            }
                            $s5 = count($offsets[4]);
                            do {
                                if(isset($offsets[4][$s5-1])) {
                                    $item = $offsets[4][$s5-1];
                                    $s5Offset = $item['offset'];
                                    $s5Wild = ($item['type'] == 'wild') ? true : false;
                                }
                                $resultOffsets = array();
                                if(isset($s1Offset)) $resultOffsets[] = $s1Offset;
                                if(isset($s2Offset)) $resultOffsets[] = $s2Offset;
                                if(isset($s3Offset)) $resultOffsets[] = $s3Offset;
                                if(isset($s4Offset)) $resultOffsets[] = $s4Offset;
                                if(isset($s5Offset)) $resultOffsets[] = $s5Offset;

                                $double = 1;
                                if($this->params->doubleIfWild) {
                                    if(isset($s1Wild)) if($s1Wild) $double = 2;
                                    if(isset($s2Wild)) if($s2Wild) $double = 2;
                                    if(isset($s3Wild)) if($s3Wild) $double = 2;
                                    if(isset($s4Wild)) if($s4Wild) $double = 2;
                                    if(isset($s5Wild)) if($s5Wild) $double = 2;
                                }
                                $withWild = ($double > 1) ? true : false;
                                $winLines[] = array(
                                    'line' => $resultOffsets,
                                    'count' => $cnt,
                                    'id' => implode('', $resultOffsets),
                                    'double' => $this->double * $double,
                                    'withWild' => $withWild,
                                    'collecting' => false,
                                );
                                $s5--;
                            } while ($s5 > 0);
                            $s4--;
                        } while ($s4 > 0);
                        $s3--;
                    } while ($s3 > 0);
                    $s2--;
                } while ($s2 > 0);
                $s1--;
            } while ($s1 > 0);
        }


        return $winLines;
    }

    /**
     * Получаем список выигрышных линий слева-направо для символа по ways
     *
     * Возвращает массив, содержащий описание линий, на которых символов >= minWinCount
     *
     * @param array $symbol Массив числовых идентификаторов символа
     * @return array
     */
    private function getWays($symbol) {
        $reelConfig = $this->params->reelConfig;
        $alias = $this->params->getSymbolByID($symbol);
        $ways = new Ways($symbol, $alias, $this->params->doubleIfWild, $this->double, $this->params->minWinCount, 'left');
        $isWild = !!count(array_intersect($symbol, $this->wild));
        for($i = 0; $i < count($reelConfig); $i++) {
            $reelSymbols = $this->reels[$i]->getVisibleSymbols();
            $matches = array();

            for($j = 0; $j < count($reelSymbols); $j++) {
                $rs = $reelSymbols[$j];
                $offset = $j * count($this->params->reelConfig) + $i;
                $type = '';
                $matched = false;

                if(in_array($rs, $symbol)) {
                    $type = 'symbol';
                    $matched = true;
                }
                elseif(in_array($rs, $this->wild) && count(array_intersect($symbol, $this->params->symbolWithoutWild)) == 0) {

                    $type = 'wild';
                    $matched = true;
                }
                elseif($this->params->collectingPay) {
                    if(is_array($this->params->collectingSymbols[0])) {
                        $g = false;
                        foreach($this->params->collectingSymbols as $cs) {
                            if(in_array($rs, $cs) && count(array_intersect($symbol, $cs)) > 0) {
                                $g = true;
                            }
                        }
                        if($g) {
                            $type = 'collecting';
                            $matched = true;
                        }
                    }
                    else {
                        if(in_array($rs, $this->params->collectingSymbols) && count(array_intersect($symbol, $this->params->collectingSymbols)) > 0) {
                            $type = 'collecting';
                            $matched = true;
                        }
                    }

                }
                if($isWild && $matched) {
                    $type = 'wild';
                }

                if($matched) {
                    $matches[] = array(
                        'offset' => $offset,
                        'type' => $type,
                        'symbol' => $rs,
                    );
                }
            }



            $ways->addMatches($matches);

        }

        return $ways->getWinLines();
    }

    /**
     * Получаем список выигрышных справа-налево линий для символа по ways
     *
     * Возвращает массив, содержащий описание линий, на которых символов >= minWinCount
     *
     * @param array $symbol Массив числовых идентификаторов символа
     * @return array
     */
    private function getWaysRight($symbol) {
        $reelConfig = $this->params->reelConfig;
        $alias = $this->params->getSymbolByID($symbol);
        $ways = new Ways($symbol, $alias, $this->params->doubleIfWild, $this->double, $this->params->minWinCount, 'right');
        $isWild = !!count(array_intersect($symbol, $this->wild));
        for($i = count($reelConfig) - 1; $i >= 0; $i--) {
            $reelSymbols = $this->reels[$i]->getVisibleSymbols();
            $matches = array();
            for($j = 0; $j < count($reelSymbols); $j++) {
                $rs = $reelSymbols[$j];
                $offset = $j * count($this->params->reelConfig) + $i;
                $type = '';
                $matched = false;
                if(in_array($rs, $symbol)) {
                    $type = 'symbol';
                    $matched = true;
                }
                elseif(in_array($rs, $this->wild)) {
                    $type = 'wild';
                    $matched = true;
                }
                elseif($this->params->collectingPay) {
                    if(is_array($this->params->collectingSymbols[0])) {
                        $g = false;
                        foreach($this->params->collectingSymbols as $cs) {
                            if(in_array($rs, $cs) && count(array_intersect($symbol, $cs)) > 0) {
                                $g = true;
                            }
                        }
                        if($g) {
                            $type = 'collecting';
                            $matched = true;
                        }
                    }
                    else {
                        if(in_array($rs, $this->params->collectingSymbols) && count(array_intersect($symbol, $this->params->collectingSymbols)) > 0) {
                            $type = 'collecting';
                            $matched = true;
                        }
                    }
                }
                if($isWild && $matched) {
                    $type = 'wild';
                }

                if($matched) {
                    $matches[] = array(
                        'offset' => $offset,
                        'type' => $type,
                        'symbol' => $rs,
                    );
                }
            }
            $ways->addMatches($matches);

        }
        return $ways->getWinLines();
    }

    /**
     * Получение выигрышных путей слота по средним барабанам(Например, 2,3,4 из 1-5 барабана)
     *
     * @param array $symbol
     * @return array
     */
    private function getWaysMiddle($symbol) {
        $reelConfig = $this->params->reelConfig;
        $alias = $this->params->getSymbolByID($symbol);
        $ways = new Ways($symbol, $alias, $this->params->doubleIfWild, $this->double, $this->params->minWinCount, 'middle');
        $isWild = !!count(array_intersect($symbol, $this->wild));
        for($i = 1; $i < (count($reelConfig)-1); $i++) {
            $reelSymbols = $this->reels[$i]->getVisibleSymbols();
            $matches = array();
            for($j = 0; $j < count($reelSymbols); $j++) {
                $rs = $reelSymbols[$j];
                $offset = $j * count($this->params->reelConfig) + $i;
                $type = '';
                $matched = false;
                if(in_array($rs, $symbol)) {
                    $type = 'symbol';
                    $matched = true;
                }
                elseif(in_array($rs, $this->wild) && count(array_intersect($symbol, $this->params->symbolWithoutWild)) == 0) {

                    $type = 'wild';
                    $matched = true;
                }
                elseif($this->params->collectingPay) {
                    if(is_array($this->params->collectingSymbols[0])) {
                        $g = false;
                        foreach($this->params->collectingSymbols as $cs) {
                            if(in_array($rs, $cs) && count(array_intersect($symbol, $cs)) > 0) {
                                $g = true;
                            }
                        }
                        if($g) {
                            $type = 'collecting';
                            $matched = true;
                        }
                    }
                    else {
                        if(in_array($rs, $this->params->collectingSymbols) && count(array_intersect($symbol, $this->params->collectingSymbols)) > 0) {
                            $type = 'collecting';
                            $matched = true;
                        }
                    }

                }
                if($isWild && $matched) {
                    $type = 'wild';
                }

                if($matched) {
                    $matches[] = array(
                        'offset' => $offset,
                        'type' => $type,
                        'symbol' => $rs,
                    );
                }
            }
            $ways->addMatches($matches);

        }
        return $ways->getWinLines();
    }

    /**
     * Получаем список выигрышных слева-направо и справа-налево линий для символа по ways
     *
     * Возвращает массив, содержащий описание линий, на которых символов >= minWinCount
     *
     * @param array $symbol Массив числовых идентификаторов символа
     * @return array
     */
    private function getWaysLeftRight($symbol) {
        $left = $this->getWays($symbol);
        $right = $this->getWaysRight($symbol);

        return array_merge($left, $right);
    }

    /**
     * Получение центральных путей слева направо
     *
     * @param array $symbol
     * @return array
     */
    private function getWaysLeftRightMiddle($symbol) {
        $left = $this->getWays($symbol);
        $right = $this->getWaysRight($symbol);
        $middle = $this->getWaysMiddle($symbol);

        $exceeded = false;
        foreach($left as $w) {
            if($w['count'] > 3) {
                $exceeded = true;
            }
        }
        foreach($right as $w) {
            if($w['count'] > 3) {
                $exceeded = true;
            }
        }

        if($exceeded) {
            return array_merge($left, $right);
        }
        else {
            return array_merge($left, $right, $middle);
        }


    }

    /**
     * Получение линий Left to Right + путей Left to Right
     * Используется в играх, где за дополнительную ставку на линию включается поиск пути
     *
     * @param array $symbol
     * @return array
     */
    private function getLineWays($symbol) {
        $line = $this->getLeft($symbol);
        $ways = $this->getWays($symbol);

        $normalWays = array();

        $reelsCount = $this->getReelsCount();

        foreach($ways as $w) {
            $wOffsets = $w['line'];
            $f = true;
            foreach($line as $l) {
                $cnt = $l['count'];
                $lineOffsets = array();
                for($i = 0; $i < $cnt; $i++) {
                    $lineOffsets[] = $l['line'][$i] * $reelsCount + $i;
                }
                if(count(array_diff($wOffsets, $lineOffsets)) == 0) {
                    $f = false;
                }
            }
            if($f) {
                $normalWays[] = $w;
            }
        }

        return array_merge($line, $normalWays);
    }

    /**
     * Получение выигрышных линий слота независимо от того, находятся рядом барабаны или нет
     *
     * @param int $symbol
     * @return array
     */
    public function getAnyPosWinLines($symbol) {
        $winLines = array();
        $lineId = 0;
        foreach($this->lines as $line) {
            $lineSymbol = $this->getLineSymbols($line);
            $cnt = 0;
            $double = 1;
            $withWild = false;
            $lineSymbolCount = 0;
            $collecting = false;
            $useSymbols = array();
            $colNumber = array();
            foreach($lineSymbol as $s) {
                if($s == $symbol) {
                    $cnt++;
                    $useSymbols[] = $s;
                    $colNumber[] = $lineSymbolCount;
                }
                $lineSymbolCount++;
            }
            if($cnt >= $this->params->minWinCount) {
                $winLines[] = array(
                    'line' => $line,
                    'count' => $cnt,
                    'id' => $lineId,
                    'double' => $this->double * $double,
                    'withWild' => $withWild,
                    'collecting' => $collecting,
                    'direction' => 'left',
                    'useSymbols' => $useSymbols,
                    'colNumber' => $colNumber,
                    'type' => 'line',
                );

            }
            $lineId++;
        }
        return $winLines;
    }

    /**
     * Проверяем, есть ли выигрышные линии у слота после спина.
     * Данная функция нужна для бонусов, которые срабатывают, если есть выигрышные линии.
     *
     * @return bool
     */
    public function checkWinLinesPresent() {
        $present = false;
        foreach ($this->winSymbols as $winLine) {
            $v = $this->params->getSymbolID($winLine['symbol']);
            if ($this->params->winLineType == 'left') {
                $SymbolwinLines = $this->getLeft($v);
            }
            elseif ($this->params->winLineType == '243') {
                $SymbolwinLines = $this->get243($v);
            }
            elseif ($this->params->winLineType == 'ways') {
                $SymbolwinLines = $this->getWays($v);
            }
            else {
                $SymbolwinLines = $this->getLeft($v);
            }
            foreach ($SymbolwinLines as $w) {
                if ($this->params->checkSymbolCount($v, $w['count'], $w['type'])) {
                    $present = true;
                    return $present;
                }
            }
        }
        return $present;
    }

    /**
     * Получение списка символов, которые находятся на выигрышной линии
     *
     * @param int $line Номер выигрышной линии
     * @return array
     */
    private function getLineSymbols($line) {
        $lineSymbols = array();
        foreach($line as $k=>$v) {
            $lineSymbols[] = $this->reels[$k]->getVisibleSymbol($v);
        }
        return $lineSymbols;
    }

    /**
     * Получаем Offset по указанной линии в нужном количестве
     *
     * @param array $line Выигрышная линия
     * @param int $count Размер возвращаемого массива $offsets
     * @return array
     */
    public function getOffsetsByLine($line, $count) {
        $offsets = array();

        $position = array(
            // Первая строка слота
            array(0, 1, 2, 3, 4),
            // Вторая строка слота
            array(5, 6, 7, 8, 9),
            // Третья строка слота
            array(10, 11 , 12, 13, 14),
            // Четвертая строка
            array(15, 16, 17, 18, 19),
            // Пятая строка
            array(20, 21, 22, 23, 24),
            // Шестая строка
            array(25, 26, 27, 28, 29),
            // Седьмая строка
            array(30, 31, 32, 33, 34),
            // Восьмая строка
            array(35, 36, 37, 38, 39),


        );
        for($i = 0; $i < $count; $i++) {
            $offsets[] = $position[$line[$i]][$i];
        }
        return $offsets;
    }

    /**
     * Получаем Offset символов барабана по его номеру
     *
     * @param int $reelNumber
     * @return array
     */
    public function getReelOffset($reelNumber) {
        $position = array(
            // Первая строка слота
            array(0, 1, 2, 3, 4),
            // Вторая строка слота
            array(5, 6, 7, 8, 9),
            // Третья строка слота
            array(10, 11 , 12, 13, 14),
            // Четвертая строка
            array(15, 16, 17, 18, 19),
        );

        return array($position[0][$reelNumber], $position[1][$reelNumber], $position[2][$reelNumber]);
    }

    /**
     * Получение оффсета(смещения) барабанов
     *
     * Массив формируется как для строк слота, а не барабанов
     *
     * @return array
     */
    public function getOffsets() {
        $offsets = array();
        for($i = 1; $i <= $this->rows; $i++) {
            $offsets["$i"] = array();
        }
        foreach($this->reels as $r) {
            for($j = 1; $j <= $this->rows; $j++) {
                if($j <= $r->getVisibleCount()) {
                    $offsets["$j"][] = $r->getOffset() + $j;
                }
            }
        }
        return $offsets;
    }

    /**
     * Получение видимых символов по барабанам
     *
     * Массив формируется как для барабанов, а не строк слота
     *
     * @return array
     */
    public function getReels() {
        $reelsArray = array();
        foreach($this->reels as $r) {
            $reelsArray[] = $r->getVisibleSymbols();
        }

        return $reelsArray;
    }

    /**
     * Получение видимых символов барабанов
     *
     * Массив формируется как для строк слота, а не барабанов
     *
     * @return array
     */
    public function getRows() {
        $rows = array();
        for($i = 1; $i <= $this->rows; $i++) {
            $rows["$i"] = array();
        }

        foreach($this->reels as $r) {
            $symbols = $r->getVisibleSymbols();
            for($j = 1; $j <= count($symbols); $j++) {
                $rows["$j"][] = $symbols[$j - 1];
            }
        }
        return $rows;
    }

    /**
     * Получение видимых символов барабанов + предыдущие и последующие символы
     *
     * @return array
     */
    private function getFullRows() {
        $rows = array();
        for($i = 1; $i <= ($this->rows + 2); $i++) {
            $rows["$i"] = array();
        }

        foreach($this->reels as $r) {
            $symbols = $r->getFullVisibleSymbols();
            for($j = 1; $j <= count($symbols); $j++) {
                $rows["$j"][] = $symbols[$j - 1];
            }
        }
        return $rows;
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
}

?>
