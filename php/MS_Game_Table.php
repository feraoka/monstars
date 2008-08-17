<?php

/*
 * Games Table
 *
 * 年月日 対戦相手 結果 得点 失点 備考
 *
 */

require_once "MS_DB.php";
require_once "MS_Table.php";

final class MS_Game_Table extends MS_Table
{
    public function __construct($link)
    {
        // set label
        $this->label = array("年月日",
                             "対戦相手",
                             "結果",
                             "得点",
                             "失点",
                             "備考");
        $this->align = array('対戦相手' => 'left',
                             '結果' => 'center',
                             '備考' => 'left');

        $this->url = $link;
    }

    // refresh data
    public function refresh($gameModule, $gamesModule)
    {
        $this->data = NULL;
        $filter = MS_DB::instance()->mFilter;
        $sql = "SELECT * FROM game_tbl WHERE $filter;";
        $ans = MS_DB::instance()->query($sql);
        if ($ans == "") {
            return;
        }

        while ($game = $ans->fetch()) {
            if ($game['type'] == "紅白戦" or !isset($game['pointGot'])) {
                $result = "-";
            } else if ($game['pointGot'] > $game['pointLost']) {
                $result = "<img src=\"images/win.png\">";
            } else if ($game['pointGot'] < $game['pointLost']) {
                $result = "<img src=\"images/lose.png\">";
            } else {
                $result = "<img src=\"images/draw.png\">";
            }
            $linkToGame = $gameModule->linkToGame($game['gameId']);
            $date = "<a href=$linkToGame>{$game['date']}</a>";
            $linkToTeam = $gamesModule->linkToTeam($game['team']);
            $teamLink = "<a href=$linkToTeam>{$game['team']}</a>";
            $this->data[] = array('年月日' => $date,
                                  '対戦相手' => $teamLink,
                                  '結果' => $result,
                                  '得点' => $game['pointGot'],
                                  '失点' => $game['pointLost'],
                                  '備考' => $game['type']);
        }
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
