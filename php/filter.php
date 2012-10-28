<?php

/**
 * $Id: filter.php 61 2007-04-29 04:23:24Z dai $
 */

require_once "db.php";

class filter {
    public $year;
    public $season = true;
    public $filterTeam = "";
    public $filterMember;
    public $filterMemberPlayed;
    public $beginGameId;
    public $endGameId;
    public $cond = "";
    public $condStr;
    public $condGames = array();

    function __construct()
    {
        // finding the last year
        $sql = "SELECT date
            FROM game_tbl
            ORDER BY date DESC;";
        $ans = query($sql)->fetch();
        $date = explode("/", $ans['date']);
        $this->setFilter($date[0]);
        $this->createGameList();
    }

    function setFilter($year, $season = true)
    {
        $this->year = $year;
        if ($year == "all") {
            $sql = "SELECT *
              FROM game_tbl
              ORDER BY gameId;";
            $this->condStr = "全試合";
        } else {
            if ($season) {
                $this->season = true;
                $sql = "SELECT *
                FROM game_tbl
                WHERE season = $year
                ORDER BY gameId;";
                $this->condStr = "{$year}年 シーズン";
            } else {
                $this->season = false;
                $begin = $year * 1000000;
                $end = ($year + 1) * 1000000;
                $sql = "SELECT *
                FROM game_tbl
                WHERE gameId >= $begin
                      AND
                      gameId <= $end
                ORDER BY gameId;";
                $this->condStr = "$year年";
            }
        }
        $ans = query($sql);
        if ($ans == "") {
            die ("no data");
        }
        $games = $ans->fetchAll();
        $first = array_shift($games);
        $this->beginGameId = $first['gameId'];
        $this->cond = "gameId >= {$this->beginGameId}";
        $last = array_pop($games);
        if ($last != NULL) {
            $this->endGameId = $last['gameId'];
            $this->cond .= " AND gameId <= {$this->endGameId}";
        } else {
            $this->endGameId = $this->beginGameId;
        }
    }

    function setFilterByNumberOfGames($n)
    {
        if ($n <= 0) return; // do nothing
        $sql = "SELECT gameId, date FROM game_tbl
            ORDER BY date DESC;";
        $ans = query($sql)->fetchAll();
        $c = count($ans);
        if ($c <= $n) $n = $c;
        $first = $ans[$n-1];
        $last = $ans[0];
        $this->beginGameId = $first['gameId'];
        $this->endGameId = $last['gameId'];
        $this->condStr = "最近 $n 試合";
    }

    function get($get)
    {
        // usually get() may be called in order to go to live
        extract($get);
        if (isset($year)) {
            if (isset($season)) {
                $this->setFilter($year, $season);
            } else {
                $this->setFilter($year);
            }
            $this->filterTeam = NULL;
            $this->filterMember = NULL;
        }
        if (isset($team)) {
            $this->filterTeam = $team;
            $this->setTeamFilter($team);
        }
        $this->createGameList();
    }

    function createGameList()
    {
        $sql = "SELECT gameId
            FROM game_tbl
            WHERE {$this->cond};";
        $ans = query($sql);
        if ($ans != "") {
            $this->condGames = $ans->fetchAll();
        } else {
            $this->condGames = array();
        }
    }

    function gameId2Date($id)
    {
        $date = substr($id, 0, 4) . "/";
        $date .= substr($id, 4, 2) . "/";
        $date .= substr($id, 6, 2);
        return $date;
    }

    function setTeamFilter($team)
    {
        $sql = "SELECT gameId
            FROM game_tbl
            WHERE team = \"$team\"";
        $sql .= ";";

        $games = query($sql);
        if ($games != "") {
            $game = $games->fetch();
            if ($this->cond != "") {
                $this->cond .= " AND ";
            }
            $this->cond .= " ( gameId = {$game['gameId']}";
            while ($game = $games->fetch()) {
                $this->cond .= " OR gameId = {$game['gameId']}";
            }
            $this->cond .= " ) ";
        }
    }

    function theLastGame()
    {
        $sql = "SELECT gameId
            FROM game_tbl
            ORDER BY gameId;";
        $ans = query($sql)->fetchAll();
        $last = array_pop($ans);
        return $last['gameId'];
    }

    function theFirstGame()
    {
        $sql = "SELECT gameId
            FROM game_tbl
            ORDER BY gameId;";
        $ans = query($sql)->fetchAll();
        $first = array_shift($ans);
        return $first['gameId'];
    }

    function post($post)
    {
        extract($post);

        if (isset($nGames) and $nGames > 0) {
            $this->cond = "";
            $this->setFilterByNumberOfGames($nGames);
        } else if (isset($gameBegin) and $gameBegin != "") {
            $this->beginGameId = $gameBegin;
            if ($gameEnd != "") {
                $this->endGameId = $gameEnd;
            } else {
                $this->endGameId = $this->theLastGame();
            }
        } else if (isset($gameEnd) and $gameEnd != "") {
            $this->beginGameId = $this->theFirstGame();
            $this->endGameId = $gameEnd;
        } else if (isset($year) and $year != "") {
            $this->setFilter($year, $season);
        }
        $this->cond = "gameId >= {$this->beginGameId}
                   AND
                   gameId <= {$this->endGameId}";
        if (isset($team) and $team != "") {
            if ($team == "" or $team == "any") {
                $this->filterTeam = "";
            } else {
                $this->filterTeam = $team;
                $this->setTeamFilter($team);
            }
        }

        if (isset($member) and $member != "") {
            if ($member == "" or $member == "any") {
                $this->filterMember = NULL;
            } else {
                $this->filterMember = $member;
                if (isset($played)) {
                    $this->filterMemberPlayed = true;
                    $sql = "SELECT gameId
                  FROM batting_tbl
                  WHERE memberId = \"$member\";";
                    $games = query($sql);
                    if ($games != "") {
                        $game = $games->fetch();
                        if ($this->cond != "") {
                            $this->cond .= " AND ";
                        }
                        $this->cond .= " ( gameId = {$game['gameId']}";
                        while ($game = $games->fetch()) {
                            $this->cond .= " OR gameId = {$game['gameId']}";
                        }
                        $this->cond .= " ) ";
                    }
                } else {
                    $this->filterMemberPlayed = false;
                    $sql = "SELECT MIN(gameId) firstGame, MAX(gameId) lastGame
                  FROM batting_tbl
                  WHERE memberId = \"$member\"
                  GROUP BY memberId;";
                    $result = query($sql)->fetch();
                    if ($this->cond != "") {
                        $this->cond .= " AND ";
                    }
                    $this->cond .= " ( gameId >= {$result['firstGame']}
                             AND
                             gameId <= {$result['lastGame']} ) ";
                }
            }
        } else {
            $this->filterMember = NULL;
            $this->filterMemberPlayed = NULL;
        }
        $this->createGameList();
    }

    function sqlCond()
    {
        return $this->cond;
    }

    function getYear($a)
    {
        $a = explode("/", $a['date']);
        return $a[0];
    }

    function formHtml($target)
    {
        $sql = "SELECT date, gameId
            FROM game_tbl
            ORDER BY date;";
        $games = query($sql)->fetchAll();

        $datePrev = "";
        $gameList = "<option selected>";
        $n = 1;
        foreach ($games as $game) {
            $gameList .= "<option value={$game['gameId']}>{$game['date']}";
            if ($datePrev == $game['date']) {
                // ダブルヘッダー
                $n++;
                $gameList .= " #$n";
            } else {
                $datePrev = $game['date'];
                $n = 1;
            }
        }

        $firstYear = $this->getYear(array_shift($games));
        $lastYear = $this->getYear(array_pop($games));
        $years = range($firstYear, $lastYear);
        $yearsList = "";

        foreach($years as $year) {
            $selected = "";
            if ($year == $this->year) {
                $selected = " selected";
            }
            $yearsList .= "<option value=" . $year;
            $yearsList .= " $selected>" . $year;
        }
    
        if ($this->season) {
            $seasonChecked = "checked";
        } else {
            $seasonChecked = "";
        }

        // member list
        $sql = "SELECT memberId, jName
            FROM member_tbl
            ORDER BY jName DESC;";
        $members = query($sql)->fetchAll();
        $memberList = "<option value=\"any\" selected>ANY";
        foreach ($members as $member) {
            $memberList .= "<option value=\"{$member['memberId']}\">{$member['jName']}";
            if ($member['memberId'] == $this->filterMember) {
                $filteredName = $member['jName'];
            }
        }

        // team list
        $sql = "SELECT DISTINCT team
            FROM game_tbl
            ORDER BY team;";
        $teams = query($sql)->fetchAll();
        if ($this->filterTeam == "") {
            $selected = "selected";
        } else {
            $selected = "";
        }

        $teamList = "<option value=\"any\" $selected>ANY";
        foreach ($teams as $team) {
            if ($this->filterTeam == $team['team']) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $teamList .= "<option value=\"{$team['team']}\" $selected>{$team['team']}";
        }

        $beginGameDate = $this->gameId2Date($this->beginGameId);
        $endGameDate = $this->gameId2Date($this->endGameId);

        $out = "<div class=closeButton>
              <input type=button value=\"×\" onClick=\"javascript:pullDown()\">
            </div>
    <h3 id=hoge>Current Filter</h3>
           <p>
             Period : $beginGameDate - $endGameDate <br>";
        if (isset($this->filterTeam)) {
            $out .= "Team : {$this->filterTeam} <br>";
        }
        if (isset($filteredName)) {
            $out .= "Member : $filteredName";
            if ($this->filterMemberPlayed) {
                $out .= " (played) ";
            }
            $out .= "<br>";
        }
        $out .= "</p>";

        $out .= "
      <h3>Filter settings</h3>
        <p>
        <form method=post action=$target name=\"filterForm\">
        <table>
          <tr><td align=right>
            <a href=$target&year=$lastYear&season=true>Live</a>
          </td><td></td></tr>
          <tr><td align=right> Year </td>
            <td>
              <select name=year onChange=\"javaScript:filterFormYearSelected()\"> <option value=all>All
                $yearsList </select>
                season <input type=checkbox name=season $seasonChecked>
            </td>
          </tr>
          <tr><td align=right> Last </td>
            <td>
              <input type=text name=nGames size=3 onChange=\"javaScript:filterFormNGamesSelected()\"> games
            </td>
          </tr>
          <tr><td align=right> Period </td>
            <td>
              <table> <tr><td align=right> From: </td>
              <td> <select name=gameBegin onChange=\"javaScript:filterFormPeriodSelected()\">
              $gameList
              </select> </td></tr>
              <tr><td align=right> To: </td>
              <td> <select name=gameEnd onChange=\"javaScript:filterFormPeriodSelected()\"> $gameList
              </select>
              </td></tr></table>
            </td>
          </tr>
          <tr><td align=right> Member </td>
            <td>
              <select name=member> $memberList </select>
              played <input type=checkbox name=played>
            </td>
          </tr>
          <tr><td align=right> Team </td>
            <td>
              <select name=team> $teamList </select>
            </td>
          </tr>
          <tr><td align=right>Class</td>
            <td>
              Ocean <input type=checkbox>
              Ocean Final <input type=checkbox>
            </td>
          </tr>
          <tr><td colspan=2 align=right>
            <input type=submit value=Ok> </td>
          </tr>
        </table>
      </form></p>";

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
