<?php
include ("jpgraph.php");
include ("jpgraph_line.php");
require_once "MS_Main.php";
require_once "MS_Module_Bat_Rank_Ave.php";
require_once "MS_Module_Bat_Rank_RBI.php";
require_once "MS_Module_Bat_Rank_HR.php";
require_once "MS_Module_Bat_Rank_Steal.php";

session_start();

chdir("..");

$top = basename(__FILE__);

if (!isset($_SESSION['monstars'])) {
    return;
}

$monstars = $_SESSION['monstars'];

if (!isset($_GET)) {
    exit;
}

$modules = array(MS_Module_Bat_Rank_Ave::instance(),
                 MS_Module_Bat_Rank_RBI::instance(),
                 MS_Module_Bat_Rank_HR::instance(),
                 MS_Module_Bat_Rank_Steal::instance());


MS_DB::instance()->setFilterByYear($monstars->mYear);
extract($_GET);
foreach ($modules as $m) {
    if ($m->name == $module) {
        lineChart($m);
    }
}

function lineChart($inst)
{
    $data = $inst->lineChart();

    $colors = array("red",
                    "orange",
                    "blue",
                    "cadetblue3",
                    "chartreuse3",
                    "chocolate2",
                    "cyan3",
                    "cyan4",
                    "darkmagenta",
                    "darkolivegreen2",
                    "dodgerblue3",
                    "dodgerblue4",
                    "goldenrod",
                    "goldenrod2",
                    "gray2",
                    "lightsalmon",
                    "lightskyblue3",
                    "salmon1",
                    "steelblue4",
                    "darkorchid4",
                    "yellow",
                    "burlywood2", 
                    "bisque4",
                    "black");

    while (count($data['data']) > count($colors)) {
        $colors = array_merge($colors, $colors);
    }

    // Create the graph. These two calls are always required
    $graph = new Graph(600, 400, "auto");

    if (isset($title)) {
        $graph->title->Set($title);
        $graph->title->SetFont(FF_GOTHIC);
    }
    //$graph->SetShadow();
    $graph->SetFrame(true, array(0, 0, 0), 0);
    $graph->SetMarginColor('#cccccc');
    $graph->SetScale("textlin");
    $graph->img->SetMargin(40,100,20,80);

    // Create the linear plot
    foreach (array_keys($data['data']) as $name) {
        array_shift($data['data'][$name]);
        array_unshift($data['data'][$name], 0);
        $result = $data['data'][$name];
        $lineplot = new LinePlot($result);
        $lineplot->SetColor(array_shift($colors));
        $lineplot->SetWeight(1);
        $lineplot->SetLegend($name);
        $graph->Add($lineplot);
    }

    //$graph->yaxis->title->Set("点");
    //$graph->yaxis->title->SetFont(FF_GOTHIC);
    //$graph->yaxis->SetTitleMargin(30);
    //$graph->yaxis->SetLabelFormat('%d');
    //$graph->yaxis->SetTextLabelInterval(2, 1);
    //$graph->yaxis->SetTextTickInterval(10, 1);
    //$graph->yaxis->SetTickLabels(array(0, 1, 2, 3,4,5,6,7,8));

    $graph->yaxis->HideTicks();
    //$graph->xaxis->title->Set("Games");
    $graph->xaxis->SetTitleMargin(25); 
    array_unshift($data['date'], '');
    $graph->xaxis->SetTickLabels($data['date']);
    $graph->xaxis->SetLabelAngle(90);

    $graph->xaxis->HideTicks(); 
    $graph->legend->SetFont(FF_MINCHO, FS_NORMAL, 8);
    $graph->legend->Pos(0.04, 0.06);

    $graph->SetBackgroundGradient('azure1','azure3', GRAD_HOR,BGRAD_PLOT);
    $graph->ygrid->show(false, false);
    // Display the graph
    $graph->Stroke();
}

?>
