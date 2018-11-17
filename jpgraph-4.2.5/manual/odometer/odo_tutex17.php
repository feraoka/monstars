<?php
//=============================================================================
// File:	ODO_TUTEX17.PHP
// Description: Example 8 for odometer graphs
// Created:	2002-02-25
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
$graph = new OdoGraph(300,300);

//---------------------------------------------------------------------
// Add drop shadow for graph
//---------------------------------------------------------------------
$graph->SetShadow();

//---------------------------------------------------------------------
// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
//---------------------------------------------------------------------
$odo1 = new Odometer(); 
$odo2 = new Odometer(); 
$odo1->SetColor("lightyellow");
$odo2->SetColor("lightyellow");

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo1->needle->Set(73);
$odo2->needle->Set(37);

//---------------------------------------------------------------------
// Add drop shadow for needle
//---------------------------------------------------------------------
$odo1->needle->SetShadow();
$odo2->needle->SetShadow();

//---------------------------------------------------------------------
// Specify the position for the two odometers
//---------------------------------------------------------------------
$odo1->SetPos(180,110);
$odo1->SetSize(100);
$odo2->SetPos(110,250);
$odo2->SetSize(100);

//---------------------------------------------------------------------
// Set captions for the odometers
//---------------------------------------------------------------------
$odo1->caption->Set("(x,y) = (180,120)\nradius=100");
$odo2->caption->Set("(x,y) = (110,270)\nradius=100");

//---------------------------------------------------------------------
// Add the odometer to the graph
//---------------------------------------------------------------------
$graph->Add($odo1);
$graph->Add($odo2);

//---------------------------------------------------------------------
// ... and finally stroke and stream the image back to the browser
//---------------------------------------------------------------------
$graph->Stroke();

// EOF
?>