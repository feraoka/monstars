<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "../jpgraph/src");
include ("jpgraph.php");
include ("jpgraph_line.php");
include "monstars.php";
session_start();

function individualBattingAverageLineChart($memberId, $cond, $db) {
    $data = array();
    $dataTotal = array();
    $game = array();

    $numSamplings = 25;
    $sql = "SELECT raw,
                   atBat,
                   hit,
                   gameId -- For debugging
            FROM bat_tbl
            WHERE memberId = $memberId and $cond;";
    $rows = query($sql, $db)->fetchAll();
    $hits = array();
    $year = NULL;
    $totalBats = 0;
    $totalHits = 0;
    foreach ($rows as $bat) {
        if ($bat['atBat']) {
            if ($bat['hit'] > 0) {
                $theHit = 1;
            } else {
                $theHit = 0;
            }
            $totalBats ++;
            $totalHits += $theHit;
            $n = array_push($hits, $theHit);
            if ($n <= $numSamplings) {
                $totalAve = sprintf("%0.3f", $totalHits / $totalBats);
                array_push($dataTotal, $totalAve);
                array_push($data, '');
            } else {
                array_shift($hits);
                $numHits = 0;
                foreach ($hits as $h) {
                    $numHits += $h;
                }
                $ave = sprintf("%0.3f", $numHits / $numSamplings);
                array_push($data, $ave);
                $totalAve = sprintf("%0.3f", $totalHits / $totalBats);
                array_push($dataTotal, $totalAve);
            }
            if ($year != substr($bat['gameId'], 0, 4)) {
                $year = substr($bat['gameId'], 0, 4);
                array_push($game, $year);
            } else {
                array_push($game, "");
            }
        }
        $lastYear = substr($bat['gameId'], 0, 4);
    }

    //データが$numSamplingsに満たない場合、平均値を返す
//     if ($n <= $numSamplings) {
//         $numHits = 0;
//         foreach ($hits as $h) {
//             $numHits += $h;
//         }
//         $ave = sprintf("%0.3f", $numHits / $numSamplings);
//         array_push($data, $ave);
//         array_push($game, $lastYear);
//         array_push($dataTotal, $ave);
//     }
    return array($data, $game, $dataTotal);
}

if (isset($_SESSION['monstars'])) {
    $monstars = $_SESSION['monstars'];
    $data = individualBattingAverageLineChart($monstars->memberId,
                                              $monstars->filter->sqlCond(),
                                              "sqlite:../data/monstars.db");

    // Create the graph. These two calls are always required
    $graph = new Graph(400, 250, "auto");    
    $graph->title->Set("打率推移");
    $graph->title->SetFont(FF_GOTHIC);
    //$graph->SetShadow();
    $graph->SetScale("textlin");
    $graph->img->SetMargin(50,30,20,60);
    $graph->setColor('#cccccc');
    $graph->SetFrame(true, '#aaaaaa');

    // Create the linear plot
    // $lineplot = new LinePlot($data[0]);
    // $lineplot->SetColor("blue");
    // $lineplot->SetFillGradient('blue@0.6','white@0.6');
    // $lineplot->SetLegend("25打数平均");
    //$graph->Add($lineplot);

    $lineplot2 = new LinePlot($data[2]);
    $lineplot2->SetColor("red");
    $lineplot2->SetLegend("通算");
    $lineplot2->SetFillGradient('red@0.6','white@0.6');
    $graph->Add($lineplot2);

    $graph->yaxis->title->Set("Ave");
    $graph->yaxis->SetTitleMargin(30);
    $graph->xaxis->title->Set("Year");
    $graph->xaxis->SetTitleMargin(25); 
    $graph->xaxis->SetTickLabels($data[1]);
    //$graph->xaxis->SetTextLabelInterval(100);
    $graph->xaxis->SetLabelAngle(90);
    $graph->xaxis->HideTicks(true, true); 
    //$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
    $graph->yaxis->scale->SetAutoMin(0); 
    $graph->yaxis->scale->SetAutoMax(1); 
    $graph->legend->SetFont(FF_MINCHO);

    // Display the graph
    $graph->Stroke();
}

/*
 * Local variables:
 * tab-width: 4
 * End:
 */

?>
