<?php

require_once "scoreboard.php";

class scoreboardXml extends scoreboard {

  function __construct ($xml) {
    $this->monstars = $xml->getAttribute("monstars");
    $got = 0;
    $lost = 0;
    if ($this->monstars == "batFirst") {
      $lost = 1;
    } else if ($this->monstars == "fieldFirst") {
      $got = 1;
    }
    foreach ($xml->childNodes as $s) {
      if ($s->nodeType == XML_ELEMENT_NODE) {
	if ($s->nodeName == "score") {
	  if ($s->getAttribute("inning") == "total") {
	    $this->total[0] =  $s->getAttribute("batFirst");
	    $this->total[1] = $s->getAttribute("fieldFirst");
	  } else {
	    $this->score[$s->getAttribute("inning")][0] = $s->getAttribute("batFirst");
	    $this->score[$s->getAttribute("inning")][1] = $s->getAttribute("fieldFirst");
	  }
	} else if ($s->nodeName == "team") {
	  $this->team[0] = $s->getAttribute("batFirst");
	  $this->team[1] = $s->getAttribute("fieldFirst");
	}
      }
    }
    if ($this->monstars == NULL) {
      $this->pointGot = "NA";
      $this->pointLost = "NA";
    } else {
      $this->pointGot = $this->total[$got];
      $this->pointLost = $this->total[$lost];
    }
    if ($this->pointGot > $this->pointLost) {
      $this->result = 1;
    } else if ($this->pointGot < $this->pointLost) {
      $this->result = -1;
    } else {
      $this->result = 0;
    }
  }

}

?>
