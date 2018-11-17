<?php
//=============================================================================
// File:	ODO_TUTEX07.PHP
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
$graph = new OdoGraph(570,250);

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
$odo7 = new Odometer(); 
$odo8 = new Odometer(); 
$odo9 = new Odometer(); 

$odo1->SetColor("lightyellow");
$odo2->SetColor("lightyellow");
$odo3->SetColor("lightyellow");
$odo4->SetColor("lightyellow");
$odo5->SetColor("lightyellow");
$odo6->SetColor("lightyellow");
$odo7->SetColor("lightyellow");
$odo8->SetColor("lightyellow");
$odo9->SetColor("lightyellow");


$odo1->SetMargin(10);
$odo2->SetMargin(10);
$odo3->SetMargin(10);
$odo4->SetMargin(10);
$odo5->SetMargin(10);
$odo6->SetMargin(10);
$odo7->SetMargin(10);
$odo8->SetMargin(10);
$odo9->SetMargin(10);

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo1->needle->Set(75);
$odo2->needle->Set(75);
$odo3->needle->Set(75);
$odo4->needle->Set(75);
$odo5->needle->Set(75);
$odo6->needle->Set(75);
$odo7->needle->Set(75);
$odo8->needle->Set(75);
$odo9->needle->Set(75);

//---------------------------------------------------------------------
// Set different needle types for each odometer
//---------------------------------------------------------------------
$odo1->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_SS);
$odo2->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_SM);
$odo3->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_SL);
$odo4->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_MS);
$odo5->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_MM);
$odo6->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_ML);
$odo7->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_LS);
$odo8->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_LM);
$odo9->needle->SetStyle(NEEDLE_STYLE_ENDARROW,NEEDLE_ARROW_LL);

$odo1->caption->Set("SS");
$odo2->caption->Set("SM");
$odo3->caption->Set("SL");
$odo4->caption->Set("MS");
$odo5->caption->Set("MM");
$odo6->caption->Set("ML");
$odo7->caption->Set("LS");
$odo8->caption->Set("LM");
$odo9->caption->Set("LL");


$odo1->caption->SetFont(FF_FONT1);
$odo2->caption->SetFont(FF_FONT1);
$odo3->caption->SetFont(FF_FONT1);
$odo4->caption->SetFont(FF_FONT1);
$odo5->caption->SetFont(FF_FONT1);
$odo6->caption->SetFont(FF_FONT1);
$odo7->caption->SetFont(FF_FONT1);
$odo8->caption->SetFont(FF_FONT1);
$odo9->caption->SetFont(FF_FONT1);

$row1 = new LayoutHor( array($odo1,$odo2,$odo3) );
$row2 = new LayoutHor( array($odo4,$odo5,$odo6) );
$row3 = new LayoutHor( array($odo7,$odo8,$odo9) );
$col1 = new LayoutVert( array($row1,$row2,$row3) );
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