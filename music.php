<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>WiPi-Air Music Server page</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<?php
require 'WiPiFunctions.php';
#
#	Define local variables
#
$remoteenabled = FALSE;
$libraryrescanned = FALSE;
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
 <ol id="toc">
    <li><a href="index.php" >Home</a></li>
    <li class="current"><a href="music.php" >Music Server</a></li>
    <li><a href="changelog.php">Changelog</a></li>
    <li><a href="diagnostics.php">Diagnostics</a></li>
    <li><a href="about.php">About</a></li>
 </ol>

 <div id="body">

  <?php
#
#	Obtain configuration data from files or returned POST
#
#
#  var_dump($_POST);

  $remotename = "";

#    if (isset($_POST["submit"])) echo "Submit received", $_POST["submit"], "<br>";
#    if (isset($_POST["checkbox"])) echo "checkbox received", $_POST["checkbox"], "<br>";

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
    switch ($_POST["submit"])
    {
    case "Network Share":
      $host     = test_input($_POST["host"]);
      $share    = test_input($_POST["share"]);
      $user     = test_input($_POST["user"]);
      $password = test_input($_POST["password"]);
      if (($host == "") OR ($share == ""))
      {
         updatedaapd_share(TRUE, $host, $share, $user, $password);
      } else {
         updatedaapd_share(FALSE, $host, $share, $user, $password);
      }
	 break;
    case "Spotify Login":
      $user     = test_input($_POST["user"]);
      $password = test_input($_POST["password"]);
      $daapd=getdaapdname();
      echo "<font color='Red'>Logging into Spotify...will take some time to sync playlists and complete<font color='Black'>";
      notifyspotify($daapd, $user, $password);
	 break;
    case "Spotify Logout":
      $user     = "WiPi-Air";
      $password = "logout";
      $daapd=getdaapdname();
      echo "<font color='Red'>Logging out from Spotify...will restart server<font color='Black'>";
      notifyspotify($daapd, $user, $password);
      sleep(1);
      processrestart("daapd");
      sleep(5);
	 break;
    case "Enable Remote":
      $daapd=getdaapdname();
      $remotename = test_input($_POST["remotename"]);
      enableremote($daapd, $remotename, test_input($_POST["pin1"]), test_input($_POST["pin2"]), test_input($_POST["pin3"]), test_input($_POST["pin4"]));
      $remoteenabled = TRUE;
	 break;
    case "Restart Server":
      processrestart("daapd");
	 break;
    case "Reset Library":
      $daapd=getdaapdname();
      libraryrescan($daapd, TRUE);
      $libraryrescanned = TRUE;
	 break;
    case "Library Rescan":
      $daapd=getdaapdname();
      libraryrescan($daapd, FALSE);
      $libraryrescanned = TRUE;
	 break;
    }
    switch ($_POST["daapdenabled"])
    {
    case "TRUE":
      enabledaapd(TRUE);
	 break;
    case "FALSE":
      enabledaapd(FALSE);
	 break;
    }
    }
  ?>


<?php
#
#	WiPi-Air iTumes Music Server Configuration - only show if configuration file available
#
    $daapd=getdaapdname();
    if  ($daapd !== FALSE)
    {
?>
       <h2>WiPi-Air iTunes Music Server:</h2>
       <p>
<?php
       if  ($daapd !== "")
       {
        $network_share = getdaapd_share($host, $share, $user, $password);
?>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	<input type="hidden" name="daapdenabled" value="FALSE">
	Music Server: <input type="checkbox" name="daapdenabled" value="TRUE" onchange="this.form.submit()" checked>
	<?php
	$df = disk_free_space("/mnt/storage")/(1024*1024*1024);
	echo "Enabled", "<br>";
        echo "Place music files in: \\\\", $hostname,"\Music  (", sprintf("%.1f", $df), "GB free)";
        if ($network_share !== FALSE)
        {
           echo " or on Network Share: \\\\", $host,"\\", $share;
        } else {
           echo " or choose Network Share";
        }
	$spotifyuser = getspotifyuser();
	?>
	</form>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	<br>
	<input type="submit" name="submit" value="Network Share">
	Server:  <input type="text" name="host"     value="<?php echo $host ?>"     size= 17 maxlength=15 pattern="[a-zA-Z0-9_-]+"  title="Alphanumeric and - or _">
	Share:   <input type="text" name="share"    value="<?php echo $share ?>"    size= 16 maxlength=14 pattern="[a-zA-Z0-9_- ]+" title="Alphanumeric and - or _ or space">
	User:    <input type="text" name="user"     value="<?php echo $user ?>"     size= 16 maxlength=14 pattern="[a-zA-Z0-9_-]+"  title="Alphanumeric and - or _">
	Password:<input type="text" name="password" value="<?php echo $password ?>" size= 16 maxlength=14 pattern="[a-zA-Z0-9_-]+"  title="Alphanumeric and - or _">
	<?php if ($remoteenabled) echo "<font color='Red'>Enabling remote...<font color='Black'>"; ?>
	<br><br>
	<?php $user = $spotifyuser; ?>
	</form>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	<?php if (!$spotifyuser) echo " <input type='submit' name='submit' value='Spotify Login'>" ?>
	<?php if ($spotifyuser)  echo " <input type='submit' name='submit' value='Spotify Logout'>" ?>
	User:    <input type="text" name="user"     value="<?php echo $user ?>"     size= 32 maxlength=30 pattern="[a-zA-Z0-9_-.]+"  title="Alphanumeric and -_._">
	Password:<input type="text" name="password" value="<?php echo $password ?>" size= 22 maxlength=20 pattern="[a-zA-Z0-9_-.!]+"  title="Alphanumeric and -_.!_">
	<br><br>
	</form>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	<input type="submit" name="submit" value="Enable Remote">
	Name: <input type="text" name="remotename" value="<?php echo $remotename ?>" size= 24 maxlength=20 pattern="[a-zA-Z0-9_- ]+" required title="Alphanumeric and - or _ or space">
	Pin:
	<input type="text" name="pin1" size= 1 maxlength=1 pattern="[0-9]+" required title="Numeric">
	<input type="text" name="pin2" size= 1 maxlength=1 pattern="[0-9]+" required title="Numeric">
	<input type="text" name="pin3" size= 1 maxlength=1 pattern="[0-9]+" required title="Numeric">
	<input type="text" name="pin4" size= 1 maxlength=1 pattern="[0-9]+" required title="Numeric">
	<?php if ($remoteenabled) echo "<font color='Red'>Enabling remote...<font color='Black'>"; ?>
	</form>
	<p>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
        <input type="submit" name="submit" value="Restart Server">
	<input type="submit" name="submit" value="Reset Library">
	<input type="submit" name="submit" value="Library Rescan">
	<?php if ($libraryrescanned) echo "<font color='Red'>Rescan initiated...<font color='Black'>";?>
	</form>
<?php
	}
	else
	{
?>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
	<input type="hidden" name="daapdenabled" value="FALSE">
	Music Server: <input type="checkbox" name="daapdenabled" value="TRUE" onchange="this.form.submit()">
	<?php echo "Disabled, check box to enable - then restart";?>
	</form>
<?php
	}
   }
?>
  </p>
 
  <p>
   <small>Overall &copy IT and Media Services 2013-<?php echo date("y"); ?></small>
  </p>
 </div>
</div>
<!-- s:853e9a42efca88ae0dd1a83aeb215047 -->
</body>
</html>
