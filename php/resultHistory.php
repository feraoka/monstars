<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "../jpgraph/src");
include ("jpgraph.php");
include ("jpgraph_line.php");
include ("jpgraph_plotband.php");
require_once "db.php";

define ('DB', "sqlite:../data/monstars.db");

$sql = "SELECT *
        FROM game_tbl
        ORDER BY date;";
$ans = query($sql, DB);

$date = array();
$dataWon = array();
$dataLost = array();
$numOfWons = 0;
$numOfLosts = 0;
$offset = 0;
$currentYear = NULL;
$begin = 0;
$ranges = array();
$labels = array();

foreach ($ans as $game) {
  if ($game['type'] == "紅白戦" or !isset($game['pointGot'])) {
    // no game
  } else {

    if ($game['pointGot'] > $game['pointLost']) {
      $numOfWons ++;
    } else if ($game['pointGot'] < $game['pointLost']) {
      $numOfLosts ++;
    } else {
    }
    array_push ($date, $game['date']);
    array_push ($dataWon, $numOfWons);
    array_push ($dataLost, $numOfLosts);

    $year = substr($game['date'], 0, 4);
    if ($year != $currentYear) {
      if ($currentYear != NULL) {
	array_push($ranges, array($begin, $offset - 1, $currentYear));
      }
      $currentYear = $year;
      $begin = $offset;
      array_push($labels, $year);
    } else {
      array_push($labels, "");
    }
    $offset++;
  }
}
array_push($ranges, array($begin, $offset - 1, $currentYear));

$graph = new Graph(600, 400, "auto");
$graph->SetScale("textlin");

//$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(40,30,20,70);
$graph->setColor('#cccccc');
$graph->SetFrame(true, '#aaaaaa');

// Create a line
$plotWon = new LinePlot($dataWon);
$plotWon->SetColor("blue");
$plotWon->SetLegend("勝ち数");
$plotWon->SetFillGradient('blue@0.6','white@0.6');

$graph->Add($plotWon);
$plotLost = new LinePlot($dataLost);
$plotLost->SetColor("red");
$plotLost->SetLegend("負け数");
$plotLost->SetFillGradient('red@0.6','white@0.6');
$graph->Add($plotLost);

// Setup the titles
$graph->title->Set("勝敗推移");
$graph->xaxis->title->Set("Year");
$graph->xaxis->SetTitlemargin(30);
//$graph->yaxis->title->Set("Y-title");
$graph->legend->SetPos(0.05,0.5,'right','center'); 

$graph->title->SetFont(FF_GOTHIC);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->legend->SetFont(FF_GOTHIC);

$graph->xaxis->SetTickLabels($labels);
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->HideTicks(true, true); 
$cols = array("#ddeeff", "#ddffff");
$n = 0;
foreach ($ranges as $range) {
  $graph->AddBand(new PlotBand(VERTICAL,BAND_SOLID, $range[0], $range[1], $cols[$n], 2));
  $n = ($n + 1) & 1;
}

// Display the graph
$graph->Stroke();

?>
