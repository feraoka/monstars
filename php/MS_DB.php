<?php

/**
 * $Id$
 * MonStarsデータベースアクセスクラス
 */

require_once "MS_DB_Base.php";

define('MSDB', './data/monstars.db');

class MS_DB extends MS_DB_Base
{
    public $mFilterMode; // filter condition in readable
    public $mFilter; // filter for sql

    // ToDo: can not be private, why?
    public function __construct()
    {
        parent::__construct(MSDB);
        $this->setFilterByYear($this->getCurrentYear());
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_DB;
        }
        return $inst;
    }

    public function setFilterByYear($year, $season = true)
    {
        $where = "";
        if ($year == "all" || $year == "All" || $year == "通算") {
            $this->mFilterMode = "通算成績";
        } else {
            if ($season) {
                $this->mFilterMode = "{$year}年 シーズン";
                $where = "WHERE season = $year";
            } else {
                $this->mFilterMode = "$year年";
                $begin = $year * 1000000;
                $end = ($year + 1) * 1000000;
                $where = "WHERE gameId >= $begin AND gameId <= $end";
            }
        }

        $sql = "SELECT gameId
                FROM game_tbl
                $where
                ORDER BY gameId;";
        $ans = $this->query($sql);
        if ($ans == "") {
            die ("no data");
        }
        $games = $ans->fetchAll();

        $first = array_shift($games);
        $last = array_pop($games);

        if ($last == NULL) {
            $this->mFilter = "gameId = {$first['gameId']}";
        } else {
            $this->mFilter = "gameId >= {$first['gameId']} AND gameId <= {$last['gameId']}";
        }
    }

    public function getCurrentYear()
    {
        $sql = "SELECT season FROM game_tbl ORDER BY season DESC";
        $ans = $this->query($sql);
        if ($ans == "") {
            die("no record");
        }
        $last = $ans->fetch();
        return $last['season'];                
    }

    public function getYearList($order = "DESC")
    {
        $sql = "SELECT season FROM game_tbl
                GROUP BY season ORDER BY season $order";
        $ans = $this->query($sql);
        if ($ans == "") {
            die("no record");
        }
        $list = array();
        while ($year = $ans->fetch()) {
            $list[] = $year['season'];
        }
        return $list;
    }

    public function quickJumpHtml($link)
    {
        $out = "<h4><a href=\"javaScript:treeMenu('treeMenuQuickJump')\">
                {$this->mFilterMode}</a></h4>
                <div id=\"treeMenuQuickJump\" style=\"display:none\">
                <ul>";
        $yearList = $this->getYearList();
        array_unshift($yearList, "all");
        foreach ($yearList as $year) {
            $out .= "<li><a href=$link&year=$year>$year</a>";
        }
        $out .= "</ul></div>";
        return $out;
    }

    /**
     * return player name in japanese
     */
    public function convertName($name)
    {
        static $jnames = null;
        if (!isset($jnames)) {
            $db = MS_DB::instance();
            $sql = "SELECT jName, name FROM member_tbl;";
            $ans = $db->query($sql);
            while ($player = $ans->fetch()) {
                $jnames[$player['name']] = $player['jName'];
            }
        }
        if (isset($jnames[$name])) {
            return $jnames[$name];
        } else {
            return $name; // not found
        }
    }

    /**
     * return player name in japanese
     */
    public function getNameByMemberId($memberId)
    {
        static $jnames = null;
        if (!isset($jnames)) {
            $db = MS_DB::instance();
            $sql = "SELECT jName, memberId FROM member_tbl;";
            $ans = $db->query($sql);
            while ($player = $ans->fetch()) {
                $jnames[$player['memberId']] = $player['jName'];
            }
        }
        if (isset($jnames[$memberId])) {
            return $jnames[$memberId];
        } else {
            return null; // not found
        }
    }

    /**
     * return top of type
     *
     * filterで指定する範囲の項目$typeについて、人数が$nまでの順位を
     * $resultのarrayで返す。$n = NULLの場合はすべて返す
     * $result = (['order'] => 順位, ['memberId'] => ID, ['value'] => 値)
     */
    public function getTheTop($type, $n = NULL) {
        if ($type == "average") {
            $target = "CAST(SUM(hit1 + hit2 + hit3 + hr) AS FLOAT)
                       / CAST(SUM(atBat) AS FLOAT)";
            // 全試合数
            $sql = "SELECT COUNT(*) numGames
                    FROM game_tbl U
                    WHERE $this->mFilter
                    and gameId = (SELECT gameId from batting_tbl S WHERE U.gameId = S.gameId);";
            $game = $this->query($sql)->fetch();

            // 個人別出場試合数
            $sql = "SELECT memberId, COUNT(*) numGames
                    FROM batting_tbl
                    WHERE $this->mFilter
                    GROUP BY memberId;";
            $games = $this->query($sql)->fetchAll();

            foreach ($games as $g) {
                $numGames[$g['memberId']] = $g['numGames'];
            }
        } else {
            $target = "SUM($type)";
        }
        $sql = "SELECT memberId, COUNT(*) 打席, $target
                FROM bat_tbl
                WHERE $this->mFilter
                GROUP BY memberId
                ORDER BY $target DESC;";
        $ans = $this->query($sql)->fetchAll();
        $i = 1; //人数
        $order = 0;
        $orderDelta = 1;
        $last = 0;
        $ret = array();

        while ($top = array_shift($ans)) {
            if ($type == "average") {
                if ($this->mFilterMode == '通算成績') {
                    // 通算成績の例外  100打席以上で規定打席
                    if ($top['打席'] < 100) {
                        continue;
                    }
                } else {
                    if ($numGames[$top['memberId']] * 2 < $game['numGames']) {
                        //打率タイプで規定打席未満はスキップ
                        continue;
                    }
                }
            }

            if ($n != NULL and $i > $n and $last != $top[$target]) {
                break;
            }
            if ($top[$target] == 0) {
                break;
            }

            if ($last == $top[$target]) {
                $orderDelta ++;
            } else {
                $order += $orderDelta;
                $orderDelta = 1;
                $last = $top[$target];
            }
            $result = array();
            $result['order'] = $order;
            $result['memberId'] = $top['memberId'];
            if ($type == "average") {
                $result['value'] = sprintf("%0.3f", $top[$target]);
            } else {
                $result['value'] = $top[$target];
            }
            array_push($ret, $result);
            $i++;
        }
        return $ret;
    }


}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
