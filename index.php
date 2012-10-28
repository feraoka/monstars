<?php
include "php/MS_Main.php";

session_start();

$top = basename(__FILE__);

if (isset($_SESSION['monstars'])) {
  $monstars = $_SESSION['monstars'];
  if (isset($_GET['session']) and $_GET['session'] == "clear") {
      session_destroy();
      $monstars = NULL;
      $monstars = new MS_Main($top);
  }
} else {
  $monstars = new MS_Main($top);
}

$html = $monstars->html();

// HTML header
print "
<html>
<head>
  <META http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"> 
  <link rel=\"stylesheet\" href=\"default.css\" type=\"text/css\" />
  <LINK REL=\"SHORTCUT ICON\" HREF=\"favicon.ico\">
  <title>MonStars</title>
<SCRIPT language=\"JavaScript\">
<!--
// Tree Menu
function treeMenu(trList)
{
  trMenu = document.getElementById(trList).style;
  if (trMenu.display == 'none') {
    trMenu.display = 'block';
  } else {
    trMenu.display = 'none';
  }
}

//-->
</SCRIPT>

</head>
<body>
  <div id=container>
  $html
  </div>
</body>
</html>
";

$_SESSION['monstars'] = $monstars;

?>
