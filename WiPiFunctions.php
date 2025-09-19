<?php
#
#
#	This file contains the common variables and functions required for
#	the WiPi-Air web front end file and parameter handling
#
#
#	The files used to hold the key parameters
#	all files must allow R/W access
#
#	/etc/hostname
#
#	Define the major functions to be used:
#	- extractstring/updatestring/updatekey
#	- getmyhostname/updatemyhostname
#	- getWiPiAirname/updateWiPiAirname
#	- getneworknames/updatenetworknames

function extractstring($string,$key)
{
#
#	Extract the element in quotes after the defined key
#	Extract looking for newline if quotes not found
#
  $o = strpos($string, $key);
  $o = $o + strlen($key);
  $x = strpos($string, '"', $o)+1;
  if ($x > $o) {
    $y = strpos($string, '"', $x);
  } else {
    $x = $o;
    $y = strpos($string, "\n", $x);
  }

return substr($string, $x, $y-$x);
}

function updatestring($string,$key,$name)
{
#
#	Update the element in quotes after the defined key
#	Update looking for newline if quotes not found
#
  $o = strpos($string, $key);
  $x = strpos($string, '"', $o)+1;
  if ($x > $o) {
    $y = strpos($string, '"', $x);
  } else {
    $x = $o + strlen($key);
    $y = strpos($string, "\n", $x);
  }

return substr_replace($string, $name, $x, $y-$x);
}

function updatekey($string, $key1, $key2)
{
#
#	Update and replace the key with the alternate key
#
  $x = strpos($string, $key1);
  $l = strlen($key1);

return substr_replace($string, $key2, $x, $l);
}

function display_signal($input)
{
#
#	Extract the Signal level from an iwconfig result
#	If not found don't output anything
#
$key0 = 'Link Quality';
$key1 = 'Signal level';
$key2 = 'dBm';
$key3 = '/100';
$quality = 0;
$x = $y = $z =0;

#	Identify line 4 or 5 for Signal information

$idx = 4;
if (isset($input[$idx])){
   $x = strpos($input[$idx], $key1);
}
if (!$x) {
   $idx = 5;
   if (isset($input[$idx])){
      $x = strpos($input[$idx], $key1);
   }
   if (!$x) return; 
}

#	Extract Link Quality
$linkqual = 0;
$x = strpos($input[$idx], $key0);
if ($x) {
   $x = $x + strlen($key0)+1;
   $y =strpos($input[$idx], $key3, $x);
   if ($y) {
      $s = substr($input[$idx], $x, $y-$x);
      $linkqual = intval($s);
   }
}

#	Extract Signal Level in dBm or /100
$sigdbm = 0;
$x = strpos($input[$idx], $key1);
$x = $x + strlen($key1)+1;
$y =strpos($input[$idx], $key2, $x);
$z =strpos($input[$idx], $key3, $x);
if (($z) and ((!$y) OR ($z < $y))) {
   $quality = 1;
   $y = $z;
}

#	If signal level found
if (($y) or ($z)) {
   $s = substr($input[$idx], $x, $y-$x);
   $sigdbm = intval($s);
}
#
# However if measure is quality (x/100) based convert to dBm using:
#	dBm = (Quality/2) - 100
#
if ($quality) {
    $sigdbm = ($sigdbm / 2) - 100;
}
#
# Driver quality is a mix of algorithms and often over stated relative to dBm
# back calculate from the dBm figure
#
$linkqual = (($sigdbm + 100) * 2);
#
if (($linkqual ==0) or ($sigdbm == 0))	{ echo "<font color='Red'><b>No Signal (",	$sigdbm, $key2, ")</b><font color='Black'>"; }
elseif ($linkqual < 55) 			{ echo "<font color='Red'><b>Poor Signal  (",    $sigdbm, $key2, ")</b><font color='Black'>"; }
elseif ($linkqual < 65) 			{ echo "<font color='Orange'><b>Fair Signal  (",	$sigdbm, $key2, ")</b><font color='Black'>"; }
elseif ($linkqual < 75) 			{ echo "<font color='Green'><b>Good Signal  (",	$sigdbm, $key2, ")</b><font color='Black'>"; }
else                   			{ echo "<font color='Green'><b>Excellent Signal  (", $sigdbm, $key2, ")</b><font color='Black'>"; }
return;
}

function getmyhostname()
{
$hostnamefile = '/etc/hostname';
#
#	Get the hostname from the appropriate config file
#
return (rtrim(file_get_contents($hostnamefile)));
}

function updatemyhostname($name)
{
$hostnamefile = '/etc/hostname';
#
#	Update the Host name speaker name in the file
#
if (getmyhostname($hostnamefile) !== $name)
{
  if (file_put_contents($hostnamefile, $name."\n") !== FALSE)
  {
  return (TRUE);
  }
  else
  {
  return (FALSE);
  }
}
else
{
  return (FALSE);
}
}

function getmykey($file, $primekey)
{
#
#	Get the WiPi-Air Key from a file
#
  $filedata = file_get_contents($file);
  if ($filedata !== FALSE)
  {
    $position = strpos($filedata, $primekey);
    if ($position !== FALSE)
    {
      $output = extractstring($filedata,$primekey);
    }
    else
    {
      $output = "";
    }
  }
  else
  {
  $output = FALSE;
  }
  return $output;
}

function updatemykey($file, $primekey, $name)
{
#
#	Update the WiPi-Air Key in a file
#
  if(getmykey($file, $primekey) !== $name)
  {
    $filedata = file_get_contents($file);
    if ($filedata !== FALSE)
    {
      $output = updatestring($filedata,$primekey,$name);
      if (file_put_contents($file, $output) === FALSE)
      {
#       Write error
        echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
        return(FALSE);
      }
    } else {
#     Read error
      return(FALSE);
    }
    return (TRUE);
  }
  else
  {
    return(FALSE);
  }
}

function getRaspotify($primekey)
{
$WiPiAirfile = '/etc/raspotify/conf';
  return getmykey($WiPiAirfile,$primekey);
}

function updateRaspotify($primekey, $name)
{
$WiPiAirfile = '/etc/raspotify/conf';
  return updatemykey($WiPiAirfile,$primekey,$name);
}

function getWiPiAirname() {return getRaspotify('LIBRESPOT_NAME=');}
function getWiPiAirGPIO() {return (getRaspotify('LIBRESPOT_ONEVENT=') !== "" ? "-g" : "");}
function getInitialVolume() {return getRaspotify('LIBRESPOT_INITIAL_VOLUME=');}

function updateWiPiAirname($name){
  return updateRaspotify('LIBRESPOT_NAME',$name);
}

function updateWiPiAirGPIO($name){
  return updateRaspotify('LIBRESPOT_ONEVENT=', ($name !== "") ? "/usr/bin/spotify_gpio.sh" : "");
}

function updateInitialVolume($value){
  return updateRaspotify('LIBRESPOT_INITIAL_VOLUME=', $value);
}

function getnetworknames()
{
#$networkdir = '/etc/NetworkManager/system-connections/';
#$key1 = '.ssid:';
#$key2 = '.psk:';
#
$cmd_ssid = "-s -g 802-11-wireless.ssid con show ";
$cmd_psk  = "-s -g 802-11-wireless-security.psk con show ";
#
#	Get the WiFi network parameters defined by key1 & key2
#
    $i = 0;
    $done = FALSE;
    while ( $done !== TRUE )
    {
	unset($filedata);
	exec(("nmcli ".$cmd_ssid.($i+1)), $filedata, $error);
	if ($error == 0)
	{
#echo "Process entry ", $i, "<br>";
           $output[2*($i)] = implode("",$filedata);
	   unset($filedata);
	   exec(("sudo nmcli ".$cmd_psk.($i+1)),  $filedata, $error);
           $output[2*($i)+1] = implode("", $filedata);
	}
	else
	{
#echo "Entries Complete ", $i, "<br>";
   	  $done = TRUE;
	}
        $i = $i+1;
    }
    return $output;
}

function updatenetworknames($nets)
{
#
#	Update the WiFi network parameters defined by key1 & key2
#


$existing = getnetworknames();

if ($existing !== $nets)
{
  $n = count($nets)/2;
  $i = 0;
  $d = count($existing)/2;

  while ($i !== $n)
  {
#echo "Apply names d:i:n [", $d, ":", $i, ":", $n, "]<br>";
	switch (true)			# check for additions/removals
	{
    	case (($d-$n)<0):			# missing entries in directory
	   exec(("sudo nmcli con clone 1 ".$d+1), $result, $error);
#echo "New Clone - error code ", $error, "<br>";
	   $existing[(2*$d)]   = "ssid";	# dummy entries
	   $existing[(2*$d)+1] = "psk";		# to force update of clone
	   $d = $d + 1;
	   break;
   	case (($d-$n)>0):				# more entries in directory
	   exec(("sudo nmcli con delete ".$d), $result, $error);
#echo "Old Connection - error code ", $error, "<br>";
	   $d = $d - 1;
	   break;

	case (($d-$n)==0):		# matching entries ...so update the detail
	   if ($existing[(2*$i)] !== $nets[(2*$i)]) {
#	      echo  "Update SSID: ", $i+1;
	      exec(("sudo nmcli con mod ".($i+1)." ssid '".$nets[(2*$i)]."'"), $result, $error);
#	      echo "- error code ssid ", $error, "<br>";
	   }
	   if ($existing[(2*$i)+1] !== $nets[(2*$i)+1]) {
#	      echo  "Update Psk : ", $i+1;
	      exec(("sudo nmcli con mod ".($i+1). " wifi-sec.psk ".$nets[(2*$i)+1]), $result, $error);
#	      echo "- error code psk ", $error, "<br>";
	   }
	   $i = $i +1;
	   break;
	}
 }
  return (TRUE);
}
#echo "Network names the same <br>";
}

function test_input($data)
{
#
#	Input validaton function
#
$data = trim($data);
$data = stripslashes($data);
$data = htmlspecialchars($data);
return $data;
}

function addnetwork(&$net,&$count,$add)
{
#
#	Add or delete a Wi-Fi network entry
#
if ($add)
{
$net[(2*$count)]="";
$net[(2*$count)+1]="";
$count=$count+1;
}
else
{
$count=$count-1;
unset($net[(2*$count)]);
unset($net[(2*$count)+1]);
}
}

function processrestart($name)
{
#
#	Request shutdown deamon to restart a server process 
#
      $tmp = "/var/www/restart.tmp";
      $file = "/var/www/restart." .  $name . "-restart";
#      echo "WiPi-Air Restart request", " using ", $file, "<br><br>";
      file_put_contents($tmp, "Please restart\n");
      $p = rename($tmp, $file);
      if ($p === FALSE) echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
}

function requestrestart($shut)
{
#
#	Ask Shutdown.py to  Reboot Pi
#
      $tmp = "/var/www/restart.tmp";
      $file = ($shut ? "/var/www/restart.force-restart" : "/var/www/restart.force-shutdown");
#      echo "WiPi-Air Restart request", " using ", $file, "<br><br>";
      file_put_contents($tmp, "Please restart\n");
      $p = rename($tmp, $file);
      if ($p === FALSE) echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
}
?>

