<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "../jpgraph/src");
include ("jpgraph.php");
include ("jpgraph_line.php");
include "monstars.php";
session_start();

chdir("..");

$top = basename(__FILE__);

if (isset($_SESSION['monstars'])) {
  $monstars = $_SESSION['monstars'];
} else {
  $monstars = new monstars($top);
}

$target = "rbi";
$title = "";
if (isset($_GET['target'])) {
  $target = $_GET['target'];
}
if (isset($_GET['title'])) {
  $title = $_GET['title'];
}

$data = $monstars->rbiLineChart($target);

$colors = array("red", "orange", "blue",
		"cadetblue3",
		"chartreuse3", "chocolate2",
		"cyan3", "cyan4", "darkmagenta",
		"darkolivegreen2", "dodgerblue3"
		, "dodgerblue4", "goldenrod", "goldenrod2",
		"gray2", "lightsalmon", "lightskyblue3",
		"salmon1", "steelblue4", "darkorchid4", "yellow","burlywood2", 
		"bisque4", "black",
		);

while (count($data[1]) > count($colors)) {
  $colors = array_merge($colors, $colors);
}
// Create the graph. These two calls are always required
$graph = new Graph(500, 250, "auto");    
$graph->title->Set($title);
$graph->title->SetFont(FF_GOTHIC);
//$graph->SetShadow();
$graph->SetFrame(true, array(0, 0, 0), 0);
$graph->SetMarginColor('#cccccc');
$graph->SetScale("textlin");
$graph->img->SetMargin(40,100,20,30);

// Create the linear plot
foreach ($data[1] as $result) {
  $name = array_shift($result);
  $lineplot = new LinePlot($result);
  $lineplot->SetColor(array_shift($colors));
  $lineplot->SetWeight(1);
  $lineplot->SetLegend($name);
  $graph->Add($lineplot);
}

//$graph->yaxis->title->Set("点");
//$graph->yaxis->title->SetFont(FF_GOTHIC);
//$graph->yaxis->SetTitleMargin(30);
//$graph->xaxis->title->Set("Games");
$graph->xaxis->SetTitleMargin(25); 
//$graph->xaxis->SetTickLabels($data[0]);
$graph->xaxis->SetLabelAngle(90);
//$graph->xaxis->HideTicks(true, true); 
$graph->legend->SetFont(FF_MINCHO, FS_NORMAL, 8);
$graph->legend->Pos(0.04, 0.06);

$graph->SetBackgroundGradient('azure1','azure3', GRAD_HOR,BGRAD_PLOT);
$graph->ygrid->show(false, false);
// Display the graph
$graph->Stroke();

?>
