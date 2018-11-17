<?php
//=============================================================================
// File:	ODO_TUTEX16.PHP
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
$graph = new OdoGraph(350,100);

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
// Adjust start and end angle for the scale
//---------------------------------------------------------------------
$odo2->scale->SetAngle(110,250);

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo1->needle->Set(70);
$odo2->needle->Set(70);

//---------------------------------------------------------------------
// Add drop shadow for needle
//---------------------------------------------------------------------
$odo1->needle->SetShadow();
$odo2->needle->SetShadow();


//---------------------------------------------------------------------
// Specify the layout for the two odometers
//---------------------------------------------------------------------
$row = new LayoutHor( array($odo1,$odo2) );

//---------------------------------------------------------------------
// Add the odometer to the graph
//---------------------------------------------------------------------
$graph->Add($row);

//---------------------------------------------------------------------
// ... and finally stroke and stream the image back to the browser
//---------------------------------------------------------------------
$graph->Stroke();

// EOF
?>