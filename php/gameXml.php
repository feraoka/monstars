<?php

require_once "game.php";
require_once "scoreboardXml.php";
require_once "battingXml.php";

class gameXml extends MS_Game {
  function __construct($xml) {
    $date = $xml->getAttribute("date");
    ereg("([0-9]+)/0?([0-9]+)/0?([0-9]+)", $date, $a);
    $year = $a[1] > 90 ? $a[1] + 1900 : $a[1] + 2000;
    $month = $a[2];
    $day = $a[3];
    $this->date = sprintf("%4d/%02d/%02d", $year, $month, $day);
    $this->season = $xml->getAttribute("season");
    $this->time = $xml->getAttribute("time");
    $this->type = $xml->getAttribute("game");// XXX should be 'type'
    $this->location = $xml->getAttribute("loc");// XXX should be 'location'
    //$result = $xml->getAttribute("result");
    $this->team = $xml->getAttribute("team");
    $this->comment = strip_tags($xml->getAttribute("comment"), "<br>");

    if ($this->time == "") {
      $t = 0; // N/A
    } else {
      ereg("([0-9]+):([0-9]+)", $this->time, $a);
      $t = $a[1];
    }
    $this->gameId = sprintf("%4d%02d%02d%02d", $year, $month, $day, $t);

    foreach ($xml->childNodes as $game) {
      if ($game->nodeType == XML_ELEMENT_NODE) {
	if ($game->nodeName == "scoreboard") {
	  $this->scoreboard = new scoreboardXml($game);
	}
	if ($game->nodeName == "batters") {
	  foreach ($game->childNodes as $b) {
	    if ($b->nodeType == XML_ELEMENT_NODE
		&& $b->nodeName == "batter") {
	      array_push($this->battings, new battingXml($b));
	    }
	  }
	}
      }
    }
  }
}

?>