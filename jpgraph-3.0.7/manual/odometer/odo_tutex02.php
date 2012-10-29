<?php
//=============================================================================
// File:	ODO_TUTEX02.PHP
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
$graph = new OdoGraph(250,140);

//---------------------------------------------------------------------
// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
//---------------------------------------------------------------------
$odo = new Odometer(ODO_FULL); 

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo->needle->Set(30);

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