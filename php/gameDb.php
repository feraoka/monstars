<?php

require_once "game.php";
require_once "db.php";
require_once "scoreboardDb.php";
require_once "battingDb.php";

class gameDb extends MS_Game {
  function __construct($gameId) {

    // 試合情報
    $sql = "SELECT * FROM game_tbl
            WHERE gameId = $gameId;";
    $theGame = query($sql)->fetch();

    $this->gameId = $gameId;
    $this->season = $theGame['season'];
    $this->date = $theGame['date'];
    $this->time = $theGame['time'];
    $this->type = $theGame['type'];
    $this->location = $theGame['location'];
    $this->team = $theGame['team'];
    $this->comment = $theGame['comment'];
    $this->scoreboard = new scoreboardDb($theGame);
    $this->battings = array();

    // 打撃成績
    $sql = "SELECT * FROM batting_tbl, member_tbl
            WHERE gameId = $gameId
                  AND
                  batting_tbl.memberId = member_tbl.memberId
            ORDER BY bOrder;";
    $ans = query($sql);

    //print "inning={$scoreboard->inning()}<br>";

    foreach ($ans as $batter) {
      array_push($this->battings, new battingDb($batter));
    }

  }
}

?>
