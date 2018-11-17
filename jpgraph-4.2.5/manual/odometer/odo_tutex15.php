<?php
//=============================================================================
// File:	ODO_TUTEX15.PHP
// Description: Example 8 for odometer graphs
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
$graph = new OdoGraph(250,140);

//---------------------------------------------------------------------
// Add drop shadow for graph
//---------------------------------------------------------------------
$graph->SetShadow();

//---------------------------------------------------------------------
// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
//---------------------------------------------------------------------
$odo = new Odometer(); 
$odo->SetColor("lightyellow");

//---------------------------------------------------------------------
// Setup the scale
//---------------------------------------------------------------------
$odo->scale->Set(100,600);
$odo->scale->SetTicks(50,2);
$odo->scale->SetTickColor("brown");
$odo->scale->SetTickLength(0.05);
$odo->scale->SetTickWeight(2);

$odo->scale->SetLabelPos(0.75);
$odo->scale->label->SetFont(FF_FONT1, FS_BOLD);
$odo->scale->label->SetColor("brown");

$odo->scale->SetLabelFormat("%dC");


//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo->needle->Set(280);


//---------------------------------------------------------------------
// Add drop shadow for needle
//---------------------------------------------------------------------
$odo->needle->SetShadow();

//---------------------------------------------------------------------
// Add the odometer to the graph
//---------------------------------------------------------------------
$graph->Add($odo);

//---------------------------------------------------------------------
// ... and finally stroke and stream the image back to the browser
//---------------------------------------------------------------------
$graph->Stroke();

// EOF
?>