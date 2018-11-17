<?php

/**
 * Batting Table
 */

require_once "MS_DB.php";
require_once "MS_Table.php";

class MS_Bat_Table extends MS_Table
{
    public function __construct($link)
    {
        // set lavel
        $this->label = array('名前',
                             '試合',
                             '打席',
                             '打数',
                             '安打',
                             '本塁打',
                             '塁打',
                             '四死球',
                             '三振',
                             '打点',
                             '得点',
                             '盗塁',
                             '出塁率',
                             '打率',
                             'OPS',
                             'NOI');
        $this->align = array('名前' => 'left');
        $this->url = $link;
        $this->gray = array('出塁率', '打率', '長打率', 'OPS', 'NOI');
        $this->help = array('OPS' => 'http://ja.wikipedia.org/wiki/OPS_%28%E9%87%8E%E7%90%83%29',
                            'NOI' => 'http://ja.wikipedia.org/wiki/NOI_%28%E9%87%8E%E7%90%83%29');
    }

    // refresh data
    public function refresh()
    {
        // clear data
        $this->data = null;
        $this->total = null;

        $db = MS_DB::instance();
        $filter = $db->mFilter;

        // 打撃成績
        $sql = "SELECT memberId,
                 COUNT(*) 打席,
                 SUM(atBat) 打数,
                 SUM(hit1 + hit2 + hit3 + hr) 安打,
                 SUM(hit1) 単打,
                 SUM(hit2) 二塁打,
                 SUM(hit3) 三塁打,
                 SUM(hr) 本塁打,
                 SUM(hit) 塁打,
                 SUM(steal) 盗塁,
                 SUM(fball) 四死球,
                 SUM(dball) 死球,
                 SUM(fball) - SUM(dball) 四球,
                 SUM(sout) 三振,
                 SUM(rbi) 打点,
                 SUM(rrun) 得点,
                 SUM(run) 出塁,
                 CAST(SUM(hit) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) 長打率,
                 CAST(SUM(hit1 + hit2 + hit3 + hr) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) 打率,
                 CAST(SUM(run) AS FLOAT) / CAST(COUNT(*) AS FLOAT) 出塁率,
                 CAST(SUM(run) AS FLOAT) / CAST(COUNT(*) AS FLOAT)
                 + CAST(SUM(hit1 + hit2 * 2 + hit3 * 3 + hr * 4) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) OPS,
                 (CAST(SUM(run) AS FLOAT) / CAST(COUNT(*) AS FLOAT)
                 + CAST(SUM(hit1 + hit2 * 2 + hit3 * 3 + hr * 4) AS FLOAT) / CAST(SUM(atBat) AS FLOAT) / 3) 
                 * 1000 NOI
                FROM bat_tbl
                WHERE $filter";
        $sqlIndividual = $sql . " GROUP BY memberId ORDER BY 打率 DESC;";
        $ans = $db->query($sqlIndividual);
        if ($ans == "") {
            return $out . "No data";
        }
        if (!isset($_GET['sort'])) {
            $_GET['sort'] = "打率";
            $this->mKey = null;
        }

        // Total
        $this->total = $db->query($sql)->fetch();

        // 全試合数
        $sql = "SELECT COUNT(*) 試合
                FROM game_tbl U
                WHERE $filter
                and gameId = (SELECT gameId from batting_tbl S WHERE U.gameId = S.gameId);";
        $totalGames = $db->query($sql)->fetch();

        while ($batting = $ans->fetch()) {
            // 個人別出場試合数
            $sql = "SELECT memberId, COUNT(*) 試合
                    FROM batting_tbl
                    WHERE ($filter) and memberId = {$batting['memberId']}
                    GROUP BY memberId;";
            $numGames = $db->query($sql)->fetch();

            // 規定打席の判定
            if ($numGames['試合'] * 2 >= $totalGames['試合']) {
                $gray = false;
            } else {
                $gray = true;
            }

            // 通算成績の例外  100打席以上で規定打席
            if (MS_DB::instance()->mFilterMode == '通算成績'
                and $batting['打席'] > 100) {
                $gray = false;
            }

            $this->data[] =
                array('名前' => MS_DB::instance()->getNameByMemberId($batting['memberId']),
                      '試合' => $numGames['試合'],
                      '打席' => $batting['打席'],
                      '打数' => $batting['打数'],
                      '安打' => $batting['安打'],
                      '単打' => $batting['単打'],
                      '二塁打' => $batting['二塁打'],
                      '三塁打' => $batting['三塁打'],
                      '本塁打' => $batting['本塁打'],
                      '塁打' => $batting['塁打'],
                      '四死球' => $batting['四死球'],
                      '四球' => $batting['四球'],
                      '死球' => $batting['死球'],
                      '三振' => $batting['三振'],
                      '打点' => $batting['打点'],
                      '得点' => $batting['得点'],
                      '盗塁' => $batting['盗塁'],
                      '出塁率' => $this->roundup($batting['出塁率']),
                      '打率' => $this->roundup($batting['打率']),
                      '長打率' => $this->roundup($batting['長打率']),
                      'OPS' => $this->roundup($batting['OPS']),
                      'NOI' => sprintf("%4.0f", $batting['NOI']),
                      'gray' => $gray);
        }
        $this->total['名前'] = "Total";
        $this->total['試合'] = $totalGames['試合'];
        $this->total['出塁率'] = $this->roundup($this->total['出塁率']);
        $this->total['打率'] = $this->roundup($this->total['打率']);
        $this->total['長打率'] = $this->roundup($this->total['長打率']);
        $this->total['OPS'] = $this->roundup($this->total['OPS']);
        $this->total['NOI'] = sprintf("%4.0f", $this->total['NOI']);
    }

    private function roundup($float)
    {
        return preg_replace("/^0/", "", sprintf("%0.3f", $float));
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
