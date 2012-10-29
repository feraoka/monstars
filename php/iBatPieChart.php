<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "../jpgraph/src");
include ("jpgraph.php");
include ("jpgraph_pie.php");
include ("jpgraph_pie3d.php");
include "monstars.php";
session_start();

function individualBattingSummaryPieChart($memberId, $cond, $db) {
    $sql = "SELECT raw,
                   atBat,
                   hit,
                   gameId -- For debugging
            FROM bat_tbl
            WHERE memberId = $memberId and $cond;";
    $rows = query($sql, $db)->fetchAll();
    $numBats = 0;
    $sout = 0;
    $others = 0;
    $fball = 0;
    $gida = 0;
    $hit = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $gOut = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $fOut = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $unknownOut = 0;
    $unknown = 0;
    foreach ($rows as $bat) {
        $numBats++;
        $raw = $bat['raw'];
        $raw = ereg_replace("\*$", "", $raw);
        $data = explode("-", $raw);
        if ($data[1] == "R") {
            $numBats --;
        } else if ($data[1] == "K") {
            $sout ++;
        } else if ($data[1] == "I") {
            $others ++;
        } else if ($data[1] == "B" or $data[1] == "D") {
            $fball ++;
        } else if ($data[2] == "G") {
            $gida ++;
        } else if ($data[2] == "H1" or $data[2] == "H2"
                   or $data[2] == "H3" or $data[2] == "HR") {
            ereg("^([1-9])([GFL]*)", $data[1], $dir);
            if (isset($dir[1])) 
                $hit[$dir[1]] ++;
        } else if ($data[2] == "O" or $data[2] == "E") {
            $dir = array();
            ereg("^([1-9])([GFL])", $data[1], $dir);
            if (isset($dir[2]) and $dir[2] == "G") {
                $gOut[$dir[1]] ++;
            } else if (isset($dir[2]) and ($dir[2] == "F" or $dir[2] == "L")) {
                $fOut[$dir[1]] ++;
            } else {
                $unknownOut ++;
            }
        } else {
            $unknown ++;
        }
    }
    $gOutSum = array_sum($gOut);
    $fOutSum = array_sum($fOut);
    $hitSum = array_sum($hit);
    $data = array($hitSum, $fball, $sout, $gOutSum, $fOutSum, $unknownOut, $unknown);
    $dataLegend = array("安打", "四死球", "三振", "ゴロアウト", "フライアウト", "その他のアウト", "不明");
    return array($data, $dataLegend);
}

if (isset($_SESSION['monstars'])) {
    $monstars = $_SESSION['monstars'];
    $data = individualBattingSummaryPieChart($monstars->memberId,
                                             $monstars->filter->sqlCond(),
                                             "sqlite:../data/monstars.db");

    $graph = new PieGraph(400, 200, "auto");
    //$graph->SetShadow();
    $graph->title->Set("打席サマリ");
    $graph->title->SetFont(FF_MINCHO);
    $graph->setColor('#cccccc');
    $graph->SetFrame(true, '#aaaaaa');

    $p1 = new PiePlot($data[0]);
    $p1->SetCenter(0.35);
    $p1->SetSize(0.3);
    $p1->SetLegends($data[1]);
    $graph->Add($p1);
    $graph->legend->SetFont(FF_MINCHO);
    $graph->Stroke();
}

?>
