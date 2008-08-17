<?php

require_once "batting.php";
require_once "db.php";

class battingDb extends batting {
    function __construct($batter) {
        $this->memberId = $batter['memberId'];
        $this->name = $batter['name'];
        $this->order = $batter['bOrder'];
        $this->position = $batter['position'];
        $sql = "SELECT * FROM batting_tbl, bat_tbl
            WHERE
              batting_tbl.batterId = {$batter['batterId']}
              AND
              batting_tbl.batterId = bat_tbl.batterId;";
        $bats = query($sql);
        foreach ($bats as $bat) {
            array_push($this->bats, new bat($bat['raw']));
        }
    }
}

?>