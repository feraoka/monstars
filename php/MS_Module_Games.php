<?php

/**
 * Teams Module
 */

require_once "MS_Module_Base.php";
require_once "MS_DB.php";
require_once "MS_Game_Table.php";
require_once "MS_Module_Game.php";

final class MS_Module_Games extends MS_Module_Base
{
    private $mTable;

    protected function __construct()
    {
        $this->name = "対戦成績";
        $this->title = $this->name;
        $this->mTable = new MS_Game_Table($this->linkToSelf());
    }

    public static function instance()
    {
        static $inst = null;
        if (!isset($inst)) {
            $inst = new MS_Module_Games;
        }
        return $inst;
    }

    public function link()
    {
        return $this->linkToSelf();
    }

    public function linkToTeam($team)
    {
        return $this->linkToSelf() . "&team=" . rawurlencode($team);
    }

    public function html()
    {
        $out = "";
        $teamFilter = false;
        if (isset($_GET)) {
            extract($_GET);
            if (isset($team)) {
                MS_DB::instance()->mFilter = "team = \"$team\"";
                $teamFilter = true;
            }
        }
        if (!$teamFilter) {
            $menu = MS_DB::instance()->quickJumpHtml($this->linkToSelf());
        } else {
            $menu = "<h4>$team</h4>";
        }
        $out .= "<div id=contentMenu>$menu</div>";
        $out .= "<div id=content>" . $this->summaryHtml();
        $this->mTable->refresh(MS_Module_Game::instance(), $this);
        $out .= "<h3>Games</h3>";
        $out .= $this->mTable->html();
        $out .= "</div>";
        return $out;
    }

    private function summaryHtml()
    {
        $cond = MS_DB::instance()->mFilter;
        $sql = "SELECT * FROM game_tbl WHERE $cond ORDER BY date;";
        $ans = MS_DB::instance()->query($sql);
        if ($ans == "") {
            return "No data";
        }
        $games = $ans->fetchAll();

        $statGame = 0;
        $statWon = 0;
        $statLost = 0;
        $statDrew = 0;
        $statConWons = 0;
        $statConLosts = 0;
        $statPointGot = 0;
        $statPointLost = 0;
        $statPointGotAtWon = 0;
        $statPointLostAtWon = 0;
        $statPointGotAtLost = 0;
        $statPointLostAtLost = 0;
        $statAveWin = 0;

        $conWons = 0;
        $conLosts = 0;
        foreach ($games as $game) {
            if ($game['type'] == "紅白戦" or !isset($game['pointGot'])) {
                // not a game
            } else {
                $statGame++;
                if ($game['pointGot'] > $game['pointLost']) {
                    $statWon++;
                    $conWons++;
                    $conLosts = 0;
                    if ($conWons > $statConWons) $statConWons = $conWons;
                    $statPointGotAtWon += $game['pointGot'];
                    $statPointLostAtWon += $game['pointLost'];
                } else if ($game['pointGot'] < $game['pointLost']) {
                    $statLost++;
                    $conWons = 0;
                    $conLosts++;
                    if ($conLosts > $statConLosts) $statConLosts = $conLosts;
                    $statPointGotAtLost += $game['pointGot'];
                    $statPointLostAtLost += $game['pointLost'];
                } else {
                    $statDrew++;
                }
                $statPointGot += $game['pointGot'];
                $statPointLost += $game['pointLost'];
            }
        }
        if ($statWon + $statLost > 0) {
            $statAveWin = sprintf("%0.3f", $statWon / ($statWon + $statLost));
        } else {
            $statAveWin = "-";
        }

        if ($statGame > 0) {
            $statAvePointGot = sprintf("%0.1f", $statPointGot / $statGame);
            $statAvePointLost = sprintf("%0.1f", $statPointLost / $statGame);
        } else {
            $statAvePointGot = "-";
            $statAvePointLost = "-";
        }

        if ($statWon > 0) {
            $statAvePointGotAtWon = sprintf("%0.1f", $statPointGotAtWon / $statWon);
            $statAvePointLostAtWon = sprintf("%0.1f", $statPointLostAtWon / $statWon);
        } else {
            $statAvePointGotAtWon = "-";
            $statAvePointLostAtWon = "-";
        }

        if ($statLost > 0) {
            $statAvePointGotAtLost = sprintf("%0.1f", $statPointGotAtLost / $statLost);
            $statAvePointLostAtLost = sprintf("%0.1f", $statPointLostAtLost / $statLost);
        } else {
            $statAvePointGotAtLost = "-";
            $statAvePointLostAtLost = "-";
        }
        $out = "<h3 class=main>Summary</h3>
        <p>
        <em>$statGame</em> 戦 <em>$statWon</em> 勝 <em>$statLost</em> 敗 <em>$statDrew</em> 分
        </p>
        <h3 class=main>Statistics</h3>
        <p>
        勝率 <em>$statAveWin</em> <br>
        総得点 <em>$statPointGot</em> / 総失点 <em>$statPointLost</em> <br>
        平均得点 <em>$statAvePointGot</em> / 平均失点 <em>$statAvePointLost</em> <br>
        連勝記録 <em>$statConWons</em> / 連敗記録 <em>$statConLosts</em> <br>
        勝ち試合 平均得点 <em>$statAvePointGotAtWon</em> / 平均失点 <em>$statAvePointLostAtWon</em> <br>
        負け試合 平均得点 <em>$statAvePointGotAtLost</em> / 平均失点 <em>$statAvePointLostAtLost</em> <br>
        </p>";

        return $out;
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
