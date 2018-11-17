<?php
//=============================================================================
// File:	ODO_TUTEX05.PHP
// Description: Example 2 for odometer tutorial 
// Created:	2002-02-22
// Author:      Johan Persson (johanp@aditus.nu)
// Version:	$Id$
// 
// Comment:
// Example file for odometer graph. This examples demonstrates the simplest
// possible graph using all default values for colors, sizes etc.
//
// Copyright (C) 2002 Johan Persson. All rights reserved.
//=============================================================================
include ("../../src/jpgraph.php");
include ("../../src/jpgraph_odo.php");

//---------------------------------------------------------------------
// Create a new odometer graph (width=250, height=200 pixels)
//---------------------------------------------------------------------
$graph = new OdoGraph(570,180);

//---------------------------------------------------------------------
// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
//---------------------------------------------------------------------
$odo1 = new Odometer(); 
$odo2 = new Odometer(); 
$odo3 = new Odometer(); 
$odo4 = new Odometer(); 
$odo5 = new Odometer(); 
$odo6 = new Odometer(); 

$odo1->SetColor("lightyellow");
$odo2->SetColor("lightyellow");
$odo3->SetColor("lightyellow");
$odo4->SetColor("lightyellow");
$odo5->SetColor("lightyellow");
$odo6->SetColor("lightyellow");

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo1->needle->Set(80);
$odo2->needle->Set(80);
$odo3->needle->Set(80);
$odo4->needle->Set(40);
$odo5->needle->Set(40);
$odo6->needle->Set(40);

$odo1->needle->SetWeight(8);


//---------------------------------------------------------------------
// Set different needle types for each odometer
//---------------------------------------------------------------------
$odo1->needle->SetStyle(NEEDLE_STYLE_SIMPLE);
$odo2->needle->SetStyle(NEEDLE_STYLE_STRAIGHT);
$odo3->needle->SetStyle(NEEDLE_STYLE_ENDARROW);
$odo4->needle->SetStyle(NEEDLE_STYLE_SMALL_TRIANGLE);
$odo5->needle->SetStyle(NEEDLE_STYLE_MEDIUM_TRIANGLE);
$odo6->needle->SetStyle(NEEDLE_STYLE_LARGE_TRIANGLE);

$odo1->caption->Set("NEEDLE_STYLE_SIMPLE");
$odo2->caption->Set("NEEDLE_STYLE_STRAIGHT");
$odo3->caption->Set("NEEDLE_STYLE_ENDARROW");
$odo4->caption->Set("NEEDLE_STYLE_SMALL_TRIANGLE");
$odo5->caption->Set("NEEDLE_STYLE_MEDIUM_TRIANGLE");
$odo6->caption->Set("NEEDLE_STYLE_LARGE_TRIANGLE");


$odo1->caption->SetFont(FF_FONT1);
$odo2->caption->SetFont(FF_FONT1);
$odo3->caption->SetFont(FF_FONT1);
$odo4->caption->SetFont(FF_FONT1);
$odo5->caption->SetFont(FF_FONT1);
$odo6->caption->SetFont(FF_FONT1);

$row1 = new LayoutHor( array($odo1,$odo2,$odo3) );
$row2 = new LayoutHor( array($odo4,$odo5,$odo6) );
$col1 = new LayoutVert( array($row1,$row2) );
//---------------------------------------------------------------------
// Add the odometer to the graph
//---------------------------------------------------------------------
$graph->Add($col1);

//---------------------------------------------------------------------
// ... and finally stroke and stream the image back to the browser
//---------------------------------------------------------------------
$graph->Stroke();

// EOF
?>