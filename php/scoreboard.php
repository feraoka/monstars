<?php

class scoreboard {

  public $score = array();
  public $team = array();
  public $total = array();
  public $monstars;
  public $pointGot;
  public $pointLost;

  //  function __construct () {}

  function toString() {
    $a = array();
    $a = array_merge($a, $this->team);
    $a = array_merge($a, $this->total);
    foreach($this->score as $score) {
      $a = array_merge($a, $score);
    }
    return implode(",", $a);
  }

  function fromString($s) {
    $a = explode(",", $s);
    $this->team = array_slice($a, 0, 2);
    $this->total = array_slice($a, 2, 2);
    $score = array_slice($a, 4);
    $c = count($score);
    $this->score = array();
    for ($i = 0; $i < $c / 2; $i ++) {
      $this->score[$i][0] = array_shift($score);
      $this->score[$i][1] = array_shift($score);
    }
  }

  function toHtml() {
    $out = "";
    if (count($this->score) > 0) {
      $out .= "<table class=table1>";
      $out .= "<tr class=hdr>";
      $out .= "<td width=80><br></td>";
      $inning = 1;
      foreach ($this->score as $s) {
	$out .= "<td width=20 align=right>$inning</td>";
	$inning ++;
      }
      $out .= "<td width=20 align=right>計</td>";
      $out .= "</tr>";

      for ($t = 0; $t < 2; $t++) {
	$team = $this->team[$t];
	if ($team == "MonStars") {
	  $bgcolor = "row2";
	} else {
	  $bgcolor = "row1";
	}
	$out .= "<tr class=$bgcolor>";
	$out .= "<td>$team</td>";
	for ($i = 0; $i < $inning - 1; $i++) {
	  $s = $this->score[$i][$t];
	  $out .= "<td align=right>$s</td>";
	}
	$s = $this->total[$t];
	$out .= "<td align=right><b>$s</b></td>";
	$out .= "</tr>";
      }
      $out .= "</table>\n";
    }
    return $out;
  }

  function inning() {
    return count($this->score);
  }
}

?>
