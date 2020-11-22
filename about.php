<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php
require 'WiPiFunctions.php';
$hostname = getmyhostname();
print("<title>".$hostname.": About</title>");
?>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<div id="page">
 <div id="header">
<?php
#
#	Header section of the page
#
 $hostname = getmyhostname();
?>
 <h1> WiPi-Air:  <?php echo $hostname ?></h1>
 </div>
 <ol id="toc">
    <li><a href="index.php" >Home</a></li>
    <li><a href="music.php" >Music Server</a></li>
    <li><a href="changelog.php">Changelog</a></li>
    <li><a href="diagnostics.php">Diagnostics</a></li>
    <li class="current"><a href="about.php">About</a></li>
 </ol>
 <div id="body">

<?php
#
#	Footer section of page
#
?>
  <h2>About the WiPi-Air</h2>
  <p>
   Welcome to the configuration and set-up pages for the WiPi-Air.  The WiPi-Air is a sophisticated wireless music server and has been developed by bringing together a range of open source packages and integrating them in seemless package.
 </p>
  <p>
   This computer has the following components installed :
  <ul>
  <li>Raspberry Pi OS</li>
  <li>Samba file sharing</li>
  <li>Lighttpd & PHP Web Server</li>
  <li>WiringPi GPIO library</li>
  <li>Shairport music player</li>
  <li>Raspotify music player</li>
  <li>Forked-daapd music server</li>
  </ul>
  </p>
  <p>
   This software is provided & licensed in accordance with the software licenses contained within this distribution.
  </p>
  <p>
   <small>Overall &copy IT and Media Services 2013-<?php echo date("y"); ?></small>
  </p>
 </div>
</div>
<!-- s:853e9a42efca88ae0dd1a83aeb215047 -->
</body>
</html>
