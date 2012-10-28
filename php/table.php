<?php

// data[]: title
// data[]: url
// data[]: labels
// data[]: keys
// data[]: data array
class table {
  private $bg = array("row1", "row2");
  private $title;
  private $url;
  private $label = array();
  private $keys = array();
  private $data = array();

  private $key;
  private $reverse;

  function __construct($data) {
    $this->title = $data[0];
    $this->url = $data[1];
    $this->label = $data[2];
    $this->data = $data[3];
    $this->align = $data[4];
  }

  function sort($a, $b) {
    if ($a[$sortKey] < $b[$sortKey]) return 1;
    else if ($a[$sortKey] > $b[$sortKey]) return -1;
    else return 0;
  }

  function rsort($a, $b) {
    if ($a[$sortKey] > $b[$sortKey]) return 1;
    else if ($a[$sortKey] < $b[$sortKey]) return -1;
    else return 0;
  }

  function toHtml($key = NULL, $reverse = NULL) {
    if (isset($key)) {
      $this->key = $key;
      if (isset($reverse) and $reverse == "reverse") {
	$this->reverse = true;
	$cmp = '>';
      } else {
	$cmp = '<';
	$this->reverse = false;
      }
      $function = '';
      $function .= 'if ($a[\'' . $key . '\'] ' . $cmp . ' $b[\'' . $key . '\']) return 1;';
      $function .= 'else if ($b[\'' . $key . '\'] ' . $cmp . ' $a[\'' . $key . '\']) return -1;';
      $function .= 'else return 0;';
      usort($this->data, create_function('$a, $b', $function));
    }
    $out = "<table>";
    if (isset($this->title)) {
      $out .= "<tr><th class=title>{$this->title}</th></tr>";
    }
    $out .= "<tr>";
    foreach ($this->label as $label) {
      $keyEnc = rawurlencode($label);
      if ($this->key == $label and !$this->reverse) {
	$out .= "<th class=hdr align=center><a href={$this->url}sort=$keyEnc&reverse=true>$label</a></th>";
      } else {
	$out .= "<th class=hdr align=center><a href={$this->url}sort=$keyEnc>$label</a></th>";
      }
    }
    $out .= "</tr>";
    $i = 0;
    foreach ($this->data as $row) {
      $out .= "<tr>";
      foreach (array_keys($this->label) as $labelIdx) {
	$data = $row[$this->label[$labelIdx]];
	if (isset($this->align)
	    and isset($this->align[$this->label[$labelIdx]])
	    and $this->align[$this->label[$labelIdx]] == 'left') {
	  $align = "align=left";
	} else {
	  $align = "align=right";
	}
	$out .= "<td class={$this->bg[$i]} $align>$data</td>";
      }
      $out .= "</tr>";
      $i ^= 1;
    }
    $out .= "</table>";
    return $out;
  }

}


?>
