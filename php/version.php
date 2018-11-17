<?php

define ('INFOFILE', "lastupdate.txt");

function getLastRevision() {
  return NULL;
}

function getLastDate() {
  $info = file(INFOFILE);
  $lastDate = NULL;
  foreach ($info as $line) {
    $lastDate = preg_replace("/^Date:\S*/", "", $line);
  }
  return $lastDate;
}

?>
