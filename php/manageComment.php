<?php

/* ToDo
 * - SSL support
 */

session_start();

if (isset($_SESSION['passcode'])) {
  $pass = $_SESSION['passcode'];
}

extract($_POST);

require_once "comment.php";
define('PASSCODE_FILE', "../var/passcode.txt");

if (isset($newpass)) {
  $fp = fopen(PASSCODE_FILE, "w");
  $ret = fwrite($fp, crypt($newpass));
  fclose($fp);
}

if (!file_exists(PASSCODE_FILE)) {
  $body = "no passcode file<br>
<form method=\"POST\" action=\"manageComment.php\">
<input type=\"text\" name=\"newpass\">
<input type=\"submit\" value=\"Init passcode\">
</form>";
} else {
  $passfile = rtrim(file_get_contents(PASSCODE_FILE));
  if (isset($pass) && crypt($pass, $passfile) == $passfile) {
    $db = new MS_Comment('../var/comment.db');
    $comments = $db->get_all();
    $list = "<table>";
    foreach ($comments as $c) {
      $list .= "<tr>
                <td><input type=\"checkbox\" name={$c['id']}>
               <td>{$c['date']}</td>
               <td>{$c['time']}</td>
               <td>{$c['gameId']}</td>
               <td>{$c['name']}</td>
               <td>{$c['comment']}</td>
              </tr>";
    }
    $list .= "</table>";
    $body = "
<form method=\"POST\" action=\"manageComment.php\">
<input type=\"submit\" value=\"Delete\">
<br>
$list
</form>
";

  } else {
    $body = "
<form method=\"POST\" action=\"manageComment.php\">
passcode: <input type=\"text\" name=\"pass\">
<input type=\"submit\" value=\"Enter\">
<br>
$list
</form>
";
  }
}

$passcode = rtrim(file_get_contents("passcode.txt"));

print "
<html>
<head>
  <META http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"> 
  <link rel=\"stylesheet\" href=\"../default.css\" type=\"text/css\" />
  <LINK REL=\"SHORTCUT ICON\" HREF=\"favicon.ico\">
  <title>MonStars</title>
</head>
<body>
$body
</body>
</html>
";

// $db = new comment;
// $records = $db->get_all();
// print_r($records);
$_SESSION['passcode'] = $pass;

?>
