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
 * Class ReelWithReel
 *
 * Барабаны с барабанами
 */

class ReelWithReel extends Reel {
    /**
     * @var array $reels Барабаны
     */
    public $reels = array();

    /**
     * ReelWithReel constructor.
     * Создание барабанов барабана
     *
     * @param array $reelsArray
     */
    public function __construct($reelsArray) {
        foreach($reelsArray as $r) {
            $this->reels[] = new Reel($r, 1);
        }
    }

    /**
     * Спин барабана
     *
     * @param array $bonus
     */
    public function spin($bonus = array()) {
        foreach($this->reels as $r) {
            $r->spin();
        }
    }

    /**
     * Получение видимых символов всех барабанов барабана
     *
     * @return array
     */
    public function getVisibleSymbols() {
        $visible = array();
        foreach($this->reels as $r) {
            $v = $r->getVisibleSymbols();
            foreach($v as $s) {
                $visible[] = $s;
            }
        }

        return $visible;
    }

    /**
     * Получение видимых символов определенного барабана
     *
     * @param int $pos
     * @return mixed
     */
    public function getVisibleSymbol($pos) {
        $visible = $this->reels[$pos]->getVisibleSymbols();
        return $visible[0];
    }

    /**
     * Проверка наличия символа в заданных оффсетах барабана
     *
     * @param array $symbol
     * @param int $iterate
     * @return array
     */
    public function checkSymbol($symbol, $iterate) {
        $visible = $this->getVisibleSymbols();
        $offsets = array();
        for($i = 0; $i < count($visible); $i++) {
            if(in_array($visible[$i], $symbol)) {
                $offsets[] = $i * 5 + $iterate;
            }
        }
        return $offsets;
    }

}