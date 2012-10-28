<?php

function todoHtml() {
  $out = "<h3>ToDo List</h3>";
  $out .= "<img src=\"images/rinda.png\">";
  $fp = fopen("todo.txt", "r");
  $ul = false;
  while (!feof($fp)) {
    $line = fgets($fp);
    if (ereg("^- (.*)", $line, $a)) {
      if ($ul == true) {
	$out .= "</ul>";
	$ul = false;
      }
      $out .= "<li>$a[1]";
    } else if (ereg("^ +(.*)", $line, $a)) {
      if ($ul == false) {
	$out .= "<ul>";
	$ul = true;
      }
      $out .= "$a[1]<br>";
    } else {
      if ($ul == true) {
	$out .= "</ul>";
	$ul = false;
      }
      $out .= "<h4>$line</h4>";
    }
  }
  fclose($fp);
  return $out;
}

?>
