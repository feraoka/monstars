<?php

/**
 * バッティングランキング
 */

require_once "MS_Module_Base.php";
require_once "MS_DB.php";

abstract class MS_Module_Bat_Rank_Base extends MS_Module_Base
{
    const TOP_N = 10;

    protected $type; // target
    protected $icon; // icon
    protected $unit; // 単位
    protected $db; // link to MS database

    protected function __construct()
    {
        $this->db = MS_DB::instance();
    }

    public function link()
    {
        return $this->linkToSelf();
    }

    public function html()
    {
        $menu = MS_DB::instance()->quickJumpHtml($this->linkToSelf());
        $out = "<div id=contentMenu>$menu</div>";
        $out .= "<div id=content>";
        $out .= "<h3>$this->title</h3>";
         if (isset($this->icon)) {
             $out .= "<p><img src=\"{$this->icon}\" align=left></p>";
         }
        $out .= $this->rankHtml();
        $out .= "</div>";
        return $out;
    }

    protected function rankHtml()
    {
        $top = MS_DB::instance()->getTheTop($this->type, self::TOP_N);
        $out = "<table>";
        foreach ($top as $player) {
            if ($player['order'] == 1) {
                $bg = "class=first";
            } else {
                $bg = "class=row2";
            }
            $name = MS_DB::getNameByMemberId($player['memberId']);
            $out .= "<tr $bg class=top>
              <td align=right>{$player['order']}</td>
              <td align=center>
              $name
              </td>
              <td align=right>{$player['value']}{$this->unit}</td>
              </tr>";
        }
        $out .= "</table>";
        $hoge = $this->lineChart();
        //print_r($hoge); //XXX debug
        $encode = rawurlencode($this->name);
        $out .= "<img class=chart src=\"php/MS_Bat_Line_Chart.php?module=$encode\">";
        return $out;
    }

    public function lineChart() {
        $topn = MS_DB::instance()->getTheTop($this->type, self::TOP_N);

        $sql = "SELECT gameId, date FROM game_tbl
                WHERE {$this->db->mFilter}
                ORDER by gameId;";
        $ans = $this->db->query($sql);
        if ($ans == "") {
            return;
        }
        $games = $ans->fetchAll();

        if ($this->type == "average") {
            $total = "SUM(hit1 + hit2 + hit3 + hr)";
        } else {
            $total = "SUM({$this->type})";
        }

        $gameIdArray = array();
        $count = 0;
        $data = array();
        $atBats = array();
        $year = 0;

        foreach ($games as $id) {
            $sql = "SELECT
                      memberId,
                      $total {$this->type},
                      SUM(atBat) numAtBats
                    FROM bat_tbl
                    WHERE gameId = {$id['gameId']}
                    GROUP BY memberId;";
            $ans = $this->db->query($sql);
            if ($ans != "") {
                if ($this->db->mFilterMode == '通算成績') {
                    $yyyy = substr($id['date'], 0, 4);
                    if ($yyyy != $year) {
                        $year = $yyyy;
                        $gameIdArray[] = $year;
                    } else {
                        $gameIdArray[] = "";
                    }
                } else {
                    $gameIdArray[] = $id['date'];
                }
                while ($result = $ans->fetch()) {
                    $memberId = $result['memberId'];
                    if (isset($data[$memberId])) {
                        array_push($data[$memberId],
                                   end($data[$memberId]) + $result[$this->type]);
                        array_push($atBats[$memberId],
                                   end($atBats[$memberId]) + $result['numAtBats']);
                    } else {// new record
                        $data[$memberId] = array();
                        $atBats[$memberId] = array();
                        for ($i = 0; $i < $count; $i++) {
                            array_push($data[$memberId], '');
                            array_push($atBats[$memberId], '');
                        }
                        array_push($data[$memberId], $result[$this->type]);
                        array_push($atBats[$memberId], $result['numAtBats']);
                    }
                }
                $count++;

                // 記録のない場合、以前の結果を継続
                foreach (array_keys($data) as $player) {
                    if (count($data[$player]) < $count) {
                        array_push($data[$player], end($data[$player]));
                        array_push($atBats[$player], end($atBats[$player]));
                    }
                }
            }
        }

        // 打率の場合、データを計算
        if ($this->type == "average") {
            foreach(array_keys($data) as $player) {
                $count = count($data[$player]);
                for ($i = 0; $i < $count; $i++) {
                    $hit = array_shift($data[$player]);
                    $numAtBats = array_shift($atBats[$player]);
                    if ($numAtBats > 0) {
                        $ave = $hit / $numAtBats;
                    } else {
                        $ave = '';
                    }
                    array_push($data[$player], $ave);
                }
            }
        }

        // 上位からソート
        $orderedData = array();
        foreach ($topn as $top) {
            foreach(array_keys($data) as $aPlayer) {
                if ($top['memberId'] == $aPlayer) {
                    array_unshift($data[$aPlayer], $this->db->getNameByMemberId($aPlayer));
                    $orderedData[$this->db->getNameByMemberId($aPlayer)] = $data[$aPlayer];
                }
            }
        }
        return array('data' => $orderedData,
                     'date' => $gameIdArray);
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
