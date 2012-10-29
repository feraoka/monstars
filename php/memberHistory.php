<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "../jpgraph/src");
include ("jpgraph.php");
include ("jpgraph_gantt.php");

require_once "db.php";
define ('DB', "sqlite:../data/monstars.db");

$sql = "SELECT memberId, jName
        FROM member_tbl;";
$members = query($sql, DB);
foreach ($members as $member) {
  $name[$member['memberId']] = $member['jName'];
}

$sql = "SELECT memberId,
               MAX(game_tbl.date) lastDay,
               MIN(game_tbl.date) firstDay
        FROM game_tbl, batting_tbl
        WHERE game_tbl.gameId = batting_tbl.gameId
        GROUP BY memberId
        ORDER BY firstDay, lastDay;";

$ans = query($sql, DB);
$periods = array();
foreach ($ans as $row) {
  $a = array();
  $a['name'] = $name[$row['memberId']];
  $a['firstDay'] = $row['firstDay'];
  $a['lastDay'] = $row['lastDay'];
  array_push($periods, $a);
}

// A new graph with automatic size
$graph = new GanttGraph(800,0,"auto");
//$graph->SetShadow();

$graph->img->SetMargin(10,30,10,60);
$graph->setColor('#cccccc');
$graph->SetFrame(true, '#aaaaaa');

//  A new activity on row '0'
$n = 0;
foreach($periods as $period) {
  $firstDay = preg_replace("/\//", "-", $period['firstDay']);
  $lastDay = preg_replace("/\//", "-", $period['lastDay']);
  $label = new Text($period['name']);
  $label = $period['name'];
  $activity = new GanttBar($n, $label, $firstDay, $lastDay);
  $activity->title->SetFont(FF_GOTHIC);
  $activity->SetColor('red');
  $activity->SetPattern(GANTT_SOLID, 'red@0.6');
  $graph->Add($activity);
  $n++;
}

$graph->ShowHeaders(GANTT_HYEAR);
$graph->scale->year->grid->SetColor('gray');
$graph->scale->year->grid->Show(true);
$graph->hgrid->Show();
$graph->hgrid->SetRowFillColor('darkblue@0.9');

// Setup a vertical marker line 
$vline = new GanttVLine("1999-11-27");
$vline->SetDayOffset(0.5);
$vline->title->Set("1999/11/27\nオーシャン\n優勝");
$vline->title->SetFont(FF_GOTHIC);
$graph->Add($vline);
//$graph->SetFrame(true,'white');
$graph->title->Set("選手在籍期間");
$graph->title->SetFont(FF_GOTHIC);

// Setup a background gradient image
$graph->SetBackgroundGradient('#ddddff','#ddffff',GRAD_HOR,BGRAD_PLOT);

// Display the Gantt chart
$graph->Stroke();

?>
