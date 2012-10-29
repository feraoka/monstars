<?php

/**
 * $Id: bat.php 141 2007-09-03 14:59:53Z dai $
 * バッティングデータクラス
 */

/**
 * バッティングテキストデータフォーマット
 *
 * [イニング]-[打球位置]-[記録]-[打点]-[盗塁][*]
 * イニング： 1-9
 * 打球位置： [1-9][GFOBDL] G=ゴロ F=フライ L=ライナー
 *  O=オーバー B=四球 D=死球 I=打撃妨害 R=代走
 *  注：8G9は右中間への打球 7G8は左中間への打球を示す
 * 記録： H[1-3R](ヒット,HRはホームラン),O(アウト),G(犠打),E(エラー)
 * 打点： 0-4
 * 盗塁： 0-3
 * 最後の"*"は本塁への帰還(得点)
 */

class bat {
    public $raw;
    public $inning;
    public $hit;
    public $hit1;
    public $hit2;
    public $hit3;
    public $hr;
    public $rbi;
    public $rrun;
    public $steal;
    public $direction;
    public $ball;
    public $sout;
    public $fball; //四死球
    public $dball; //死球
    public $run;
    public $atBat;

    function __construct($raw = NULL) {
        $this->raw = NULL;
        if ($raw != NULL) {
            if ($this->init($raw) == false) {
                die ("Invalid format [$raw]\n");
                //throw new Exception("Invalid format", 1);
            }
        } else {
            die ("null data");
        }
    }

    function init($raw) {
        // Clearing all variables
        $this->raw = NULL;
        $this->inning = NULL;
        $this->hit = 0; // 塁打
        $this->hit1 = 0; // 単打
        $this->hit2 = 0; // 二塁打
        $this->hit3 = 0; // 三塁打
        $this->hr = 0; // 本塁打
        $this->rbi = 0; // 打点
        $this->rrun = 0; // 得点
        $this->steal = 0; // 盗塁
        $this->direction = 0; // 打球方向
        $this->ball = NULL; // 打球
        $this->sout = 0; // 三振
        $this->fball = 0; // 四死球
        $this->dball = 0; // 死球
        $this->run = 0; // 出塁
        $this->atBat = 1;

        // removing //
        $raw = preg_replace("/\/\/$/", "", $raw);

        // Checking data format
        $rawNoRrun = preg_replace("/\\*$/", "", $raw);
        if ($rawNoRrun != $raw) {
            $this->rrun = 1;
        }

        $array = explode("-", $rawNoRrun);
        if (count($array) < 2) {
            return false;
        }

        if (!preg_match("/^[1-9][0-9]?$/", $array[0])) {
            return false;
        }

        $this->inning = $array[0];
    
        // K:三振, B:四球, D:死球, I:打撃妨害, R:代走
        if (!preg_match("/^([KBDIR]|[1-9\?]?[GFLO\?]|[1-9][GFLO\?]?)$/", $array[1])) {
            return false;
        }

        if (preg_match("/^([1-9\?])[GFLO\?]?$/", $array[1], $a)) {
            $this->direction = $a[1];
        }

        if (preg_match("/^[1-9\?]?([GFLO\?])$/", $array[1], $a)) {
            $this->ball = $a[1];
        }

        if ($array[1] == "K") {
            $this->sout = 1;
        }

        if (preg_match("/^[BD]$/", $array[1])) {
            $this->fball = 1;
            $this->run = 1;
            $this->atBat = 0;
        }
        if (preg_match("/^D$/", $array[1])) {
            $this->dball = 1;
        }

        if (isset($array[2]) && $array[2] != NULL) {
            if ($array[2] == "G") { // 犠打
                $this->atBat = 0;
            } else if ($array[2] == "E") { // エラー
                $this->run = 1;
            } else if ($array[2] == "O") {
                // do nothing
            } else if (preg_match("/^H([1-3R])$/", $array[2], $a)) {
                if ($array[2] == "HR") {
                    $this->hit = 4;
                } else {
                    $this->hit = $a[1];
                }
                if ($this->hit == 1) $this->hit1 = 1;
                if ($this->hit == 2) $this->hit2 = 1;
                if ($this->hit == 3) $this->hit3 = 1;
                if ($this->hit == 4) $this->hr = 1;
                 $this->run = 1;
            } else {
                return false;
            }
        }
    
        if (isset($array[3]) && $array[3] != NULL) {
            if (preg_match("/^[0-4]$/", $array[3])) {
                $this->rbi = $array[3];
            } else {
                return false;
            }
        }

        if (isset($array[4]) && $array[4] != NULL) {
            if (preg_match("/^[0-3]$/", $array[4])) {
                $this->steal = $array[4];
            } else {
                return false;
            }
        }

        if (isset($array[5])) {
            return false;
        }

        $this->raw = $raw;
        return true;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * End:
 */

?>
