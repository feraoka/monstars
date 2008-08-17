<?php

require_once "scoreboard.php";

class scoreboardDb extends scoreboard {

  function __construct ($game) {
    $this->fromString($game['scoreboard']);
  }

}

?>
