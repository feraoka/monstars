<?php

define ('DATABASE', "sqlite:data/monstars.db");

function query($sql, $db = DATABASE) {
  try {
    $dbh = new PDO($db, '', '');
    $ans = $dbh->query($sql);
    $dbh = NULL;
  } catch (PDOException $e) {
    die ("Connection failed ($db): {$e->getMessage()}, error code: {$e->getCode()}\n");
  }
  return $ans;
}

?>