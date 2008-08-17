<?php

/*
 * Team Table
 *
 *
 */

require_once "MS_DB.php";
require_once "MS_Table.php";
require_once "MS_Module_Games.php";

final class MS_Team_Table extends MS_Table
{
    public function __construct($link)
    {
        // set label
        $this->label = array("チーム",
                             "試合",
                             "勝",
                             "負",
                             "分",
                             "勝率");
        $this->align = array('チーム' => 'left');

        $this->url = $link;
    }

    // refresh data
    public function refresh()
    {
        // clear data
        $this->data = NULL;

        $db = MS_DB::instance();
        $sql = "SELECT team, COUNT(*) numGames FROM game_tbl
                GROUP BY team ORDER BY numGames DESC, date DESC;";
        $ans = $db->query($sql);
        if ($ans == "") return;
        while ($row = $ans->fetch()) {
            extract($row);
            $win = 0;
            $lose = 0;
            $draw = 0;
            $sql = "SELECT * FROM game_tbl WHERE team = \"$team\";";
            $games = $db->query($sql);
            while ($game = $games->fetch()) {
                if ($game['type'] == "紅白戦" or !isset($game['pointGot'])) {
                    // no game
                } else if ($game['pointGot'] > $game['pointLost']) {
                    $win++;
                } else if ($game['pointGot'] < $game['pointLost']) {
                    $lose++;
                } else {
                    $draw++;
                }
            }
            if ($win + $lose > 0) {
                $wRate = sprintf("%0.3f", $win / ($win + $lose));
                $wRate = ereg_replace("^0", "", $wRate);
            } else {
                $wRate = "-";
            }
            $linkToTeam = MS_Module_Games::instance()->linkToTeam($team);
            $teamLink = "<a href=$linkToTeam>$team</a>";
            $this->data[] = array('チーム' => $teamLink,
                                  '試合' => $row['numGames'],
                                  '勝' => $win,
                                  '負' => $lose,
                                  '分' => $draw,
                                  '勝率' => $wRate);
        }
    }
}
