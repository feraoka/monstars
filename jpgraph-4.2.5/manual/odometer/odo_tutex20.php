<?php
//=============================================================================
// File:	ODO_TUTEX20.PHP
// Description: Example 20 for odometer tutorial 
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
$graph = new OdoGraph(500,210);

//---------------------------------------------------------------------
// Setup the graph format and titles
//---------------------------------------------------------------------
$graph->SetShadow();
$graph->title->Set("Adding image title");
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->title->SetColor("white");
$graph->caption->Set("... and an informative caption!");
$graph->caption->SetColor("white");

//---------------------------------------------------------------------
// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
//---------------------------------------------------------------------
$odo1 = new Odometer(); 
$odo2 = new Odometer(); 
$odo3 = new Odometer(); 
$odo4 = new Odometer(); 
$odo5 = new Odometer(); 

$odo1->SetColor("lightyellow");
$odo2->SetColor("lightyellow");
$odo3->SetColor("lightyellow");
$odo4->SetColor("lightyellow");
$odo5->SetColor("lightyellow");

//---------------------------------------------------------------------
// Setup captions
//---------------------------------------------------------------------
$odo1->caption->Set("Some caption..");
$odo2->caption->Set("Some caption..");
$odo3->caption->Set("Some caption..");
$odo4->caption->Set("Some caption..");
$odo5->caption->Set("Some caption..");

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo1->needle->Set(80);
$odo2->needle->Set(80);
$odo3->needle->Set(80);
$odo4->needle->Set(40);
$odo5->needle->Set(40);

//---------------------------------------------------------------------
// Create the layout
//---------------------------------------------------------------------
$row1 = new LayoutHor( array($odo1,$odo2) );
$row2 = new LayoutHor( array($odo3,$odo4,$odo5) );
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