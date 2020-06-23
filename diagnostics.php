<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php
require 'WiPiFunctions.php';
$hostname = getmyhostname();
print("<title>".$hostname.": Diagnostics</title>");
?>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<?php
#
#	Key Parameters
#
$Shairportlogfile = "/var/log/shairport.log";
$Daapdlogfile = "/var/log/forked-daapd.log";
$Monitorlogfile = "/var/log/monitor.log";
$Dmesglogfile = "Dmesg";
$Displaylines = 30;
$class_shairport = "";
$class_daapd = "";
$class_monitor = "";
$class_system = "";
?>

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
<?php
#
#	Get diagnostic mode eiether as a parameter or as hidden field on forms
#
$diagnostic_mode = (!empty($_GET['diagmode']) ? $_GET['diagmode'] : (isset($_POST['Diagselect']) ? $_POST['Diagselect'] : "" ));
#echo $diagnostic_mode, "<br>";
switch($diagnostic_mode)
{
case "Forked-Daapd":
    $logfile =  $Daapdlogfile;
    $class_daapd = "current";
    break;
case "Monitor":
    $logfile =  $Monitorlogfile;
    $class_monitor = "current";
    break;
case "System":
    $logfile =  $Dmesglogfile;
    $class_system = "current";
    break;
default:
    $diagnostic_mode = "Shairport";
    $logfile =  $Shairportlogfile;
    $class_shairport = "current";
    break;
}
#echo $diagnostic_mode, "<br>";
#echo $logfile, "<br>";
#
?>
 <ol id="toc">
    <li><a href="index.php" >Home</a></li>
    <li><a href="music.php" >Music Server</a></li>
    <li><a href="changelog.php">Changelog</a></li>
    <li class="current"><a href="diagnostics.php">Diagnostics</a></li>
    <li><a href="about.php">About</a></li>
 </ol>
 <ol id="toc1">
    <li class=<?php echo $class_shairport ?>><a href="diagnostics.php?diagmode=Shairport">WiPi-Air Speaker</a></li>
    <li class=<?php echo $class_daapd     ?>><a href="diagnostics.php?diagmode=Forked-Daapd">WiPI-Air Music Player</a></li>
    <li class=<?php echo $class_monitor   ?>><a href="diagnostics.php?diagmode=Monitor">System Monitor</a></li>
    <li class=<?php echo $class_system    ?>><a href="diagnostics.php?diagmode=System">System Information</a></li>
 </ol>

 <div id="body">

<?php
#
#	POST Handling
#
#	var_dump($_POST);
#	echo "<br><br>";
#
	if (isset($_POST['submit'])) {
	  switch ($_POST["submit"]) {
	  case "Select Diagnostics":
#	   No extra functions to perform
	    break;

	  case "Update":
	    $buffer = test_input($_POST["buffer"]);
	    $delay_time = test_input($_POST["delay_time"]);
	    updateWiPiAirbuffer($buffer);
	    updateWiPiAirdelay($delay_time);
	    break;

	  case "Refresh Display":
	    $Displaylines = test_input($_POST["displaylines"]);
	    break;

	  case "Delete Logfile":
	    $logfile = ($Shairportselect =="checked" ? $Shairportlogfile : $logfile);
	    $logfile = ($Daapdselect    =="checked" ? $Daapdlogfile     : $logfile);
	    $logfile = ($Shutdownselect  =="checked" ? $Shutdownlogfile  : $logfile);
	    if (unlink($logfile) == 0) {echo "<font color='red'>Delete (",$logfile,") failed - check permissions<font color='black'><br><br>";}
	    break;
	  }
	}
	  if(isset($_POST['verbose'])) {
	  switch($_POST["verbose"]) {
          case "TRUE":
	    updateWIPiAirdebug("-v");

	    break;
          case "FALSE":
	    updateWIPiAirdebug("");

	    break;
	  }
	}


?>
<?php
#
#
#
switch($diagnostic_mode)
{
case "Forked-Daapd":
?>
    <h2>WiPi-Air Music Player (Forked-daapd) Diagnostics</h2>
    <p>

    No options currently available.
    <p>
<?php
    break;

case "Monitor":
?>
    <h2>WiPi-Air System Monitor Diagnostics</h2>
    <p>

    No options currently available.
    <p>
<?php
    break;

case "System":
?>
    <h2>WiPi-Air System Diagnostics</h2>
    <p>
<?php
	# Display disk usage
	#
        $df = disk_free_space("/root")/(1024*1024*1024);
        echo "System root partition disk usage : ", sprintf("%.1f", $df), "GB free", "<br><br>";

	# Display System Temp
	#
	exec('vcgencmd measure_temp', $temp, $ret);
	echo "System ";
        foreach ($temp as $value) { echo $value, "<br><br>"; }

	# Display available networks
	#
        echo "Wireless networks scan:", "<br>";
        exec('iwlist wlan0 scan | grep -A 8 Address', $iwout, $iwret);
	echo "<pre>";
        foreach ($iwout as $value) { echo $value, "<br>"; }
	echo "</pre>";

    break;

default:
	$verbose = ((getWiPiAirdebug() == "-v")? "checked":"unchecked");
	$buffer = getWIPiAirbuffer();
	$delay_time = getWIPiAirdelay();
?>
	<h2>WiPi-Air Speaker (Shairport) Diagnostics</h2>
	<p>

	<form  method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	<input type="hidden" name="verbose" value="FALSE">
	Verbose diagnostics: <input type="checkbox" name="verbose" Value="TRUE" <?php echo $verbose ?> onchange="this.form.submit()">
	<input type="hidden" name="Diagselect" value=<?php echo $diagnostic_mode ?>>
	</form>
	<form  method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	Network buffer size: <input type="text" name="buffer"       value=<?php echo $buffer ?>       size=5 maxlength=3 pattern="[0-9]+" required title="Numeric">
	Sync interval: <input type="text" name="delay_time" value =<?php echo $delay_time ?> size=6 maxlength=4 pattern="[0-9]+" required title="Numeric">
	<input type="submit" name="submit" value="Update">
	<input type="hidden" name="Diagselect" value=<?php echo $diagnostic_mode ?>>
	</form>
	<p>
<?php
    break;
}

?>
<?php
#
#	Display the selected logfile if available
#
?>
	<h2>Logfile:</h2>
	<p>

	<form  method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	Lines to display: <input type="text" name="displaylines" value=<?php echo $Displaylines ?> size=5 maxlength=3 pattern="[0-9]+" required title="Numeric">
	<input type="submit" name="submit" value="Refresh Display">
	<?php if ($logfile !== $Dmesglogfile) { ?> <input type="submit" name="submit" value="Delete Logfile"><?php } ?>
	<input type="hidden" name="Diagselect" value=<?php echo $diagnostic_mode ?>>
	</form>
       <p>
<?php
	echo $diagnostic_mode, " log file: ";
        if ($logfile == $Dmesglogfile) {
           $cmd = sprintf("dmesg -T | tail -n%s",$Displaylines);
        } else {
           $cmd = sprintf("tail -n%s %s",$Displaylines,$logfile);
        }
	exec($cmd , $tail, $ret);

	switch (count($tail))
	{
	case 0:
	  echo "No log file available", "<br>";
	  break;
	default:
	  echo "<br>", "<pre>";
	  foreach ($tail as $value) { echo $value, "<br>"; }
	  echo "</pre><br>";
	}

?>

<?php
#
#	Footer section of page
#
?>
	<form  method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	<input type="submit" name="submit" value="Refresh Display"><br>
	<input type="hidden" name="displaylines" value=<?php echo $Displaylines ?>>
	<input type="hidden" name="Diagselect" value=<?php echo $diagnostic_mode ?>>
	</form>
  <br><br>
   <small>Overall &copy IT and Media Services 2013-<?php echo date("y"); ?></small>
  </p>
 </div>
</div>
<!-- s:853e9a42efca88ae0dd1a83aeb215047 -->
</body>
</html>
