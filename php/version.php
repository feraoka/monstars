<?php

define ('INFOFILE', "svnInfo.txt");

function getLastRevision() {
  $info = file(INFOFILE);
  $jLastRevision = "最終変更リビジョン";
  $eLastRevision = "Last Changed Rev";
  $lastRevision = NULL;
  foreach ($info as $line) {
    if (ereg("($jLastRevision|$eLastRevision): ([0-9]+)", $line, $a)) {
      $lastRevision = $a[2];
    }
  }
  return $lastRevision;
}

function getLastDate() {
  $info = file(INFOFILE);
  $jLastDate = "最終変更日時";
  $eLastDate = "Last Changed Date";
  $lastDate = NULL;
  foreach ($info as $line) {
    if (ereg("($jLastDate|$eLastDate): ([0-9]+-[0-9]+-[0-9]+ [0-9]+:[0-9]+)", $line, $a)) {
      $lastDate = $a[2];
    }
  }
  return $lastDate;
}

?>
