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
#	/etc/shairport.conf
#       /etc/wpa_supplicant/wpa_supplicant.conf
#       /etc/forked-daapd.conf
#
#	Define the major functions to be used:
#	- extractstring/updatestring/updatekey
#	- getmyhostname/updatemyhostname
#	- getWiPiAirname/updateWiPiAirname
#	- getWiPiAirDebug/updateWiPiAirDebug
#	- getWiPiAirBuffer/updateWiPiAirBuffer
#	- getneworknames/updatenetworknames
#	- getdaapd/enabledaapd

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
$x = strpos($input[$idx], $key1);
if (!$x) {
   $idx = 5;
   $x = strpos($input[$idx], $key1);
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
return (file_get_contents($hostnamefile));
}

function updatemyhostname($name)
{
$hostnamefile = '/etc/hostname';
#
#	Update the Host name speaker name in the file
#
if (getmyhostname($hostnamefile) !== $name)
{
  if (file_put_contents($hostnamefile, $name) !== FALSE)
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

function getWirelessMode()
{
$NetworkConfig = '/etc/Wireless/RT2870STA/RT2870STA.dat';
  return getmykey($NetworkConfig,'WirelessMode=');
}

function updateWirelessMode($band1, $band2, $ACmode)
{
$NetworkConfig = '/etc/Wireless/RT2870STA/RT2870STA.dat';
  if ($ACmode)
  {
	$value = "13";
	if ((!$band1) && ($band2)) { $value = "14"; }
  } else {
	$value = "10";
	if (($band1) && (!$band2)) { $value = "6"; }
	if ((!$band1) && ($band2)) { $value = "8"; }
  }
  return updatemykey($NetworkConfig,'WirelessMode=', $value);
}

function getWiPiAir($primekey)
{
$WiPiAirfile = '/etc/shairport.conf';
  return getmykey($WiPiAirfile,$primekey);
}

function updateWiPiAir($primekey, $name)
{
$WiPiAirfile = '/etc/shairport.conf';
  return updatemykey($WiPiAirfile,$primekey,$name);
}

function getWiPiAirname() {return getWiPiAir('NAME=');}
function getWiPiAirdebug() {return getWiPiAir('DEBUG=');}
function getWiPiAirbuffer() {return getWiPiAir('BUFFER_FILL=');}
function getWiPiAirdelay() {return getWiPiAir('DELAY=');}

function updateWiPiAirname($name){return updateWiPiAir('NAME=',$name);}
function updateWiPiAirdebug($name){return updateWiPiAir('DEBUG=',$name);}
function updateWiPiAirbuffer($name){return updateWiPiAir('BUFFER_FILL=',$name);}
function updateWiPiAirdelay($name){return updateWiPiAir('DELAY=',$name);}

function extractBSSid($string,$key)
{
#
#	Extract the BDDid which may or may not be present in string
#
  $o = strpos($string, $key);
  if ($o) {
    $x = $o+strlen($key);
    $y = $x+17;
    return substr($string, $x, $y-$x);
  } else {
    return "";
  }
}

function updateBSSid($string,$key,$name)
{
#
#	Update the BSSid, add to end as we use template
#
  $o = strpos($string, $key);

  if ($o) {
    if ($name == "") {
       $newname = "";
    } else {
       $newname = $key . $name;
    }
    $y = $o + strlen($key)+17;
    return substr_replace($string, $newname, $o+1, $y-$o);
  } else {
    if ($name == "") {
       return $string;
    } else {
       $o = strpos($string, "}");
       $newname = $key . $name . "\n";
       return substr_replace($string, $newname, $o, 0);
    }
  }
}

function getnetworknames()
{
$networkfile = '/etc/wpa_supplicant/wpa_supplicant.conf';
$primekey = 'network={';
$key1 = 'ssid=';
$key2 = 'psk=';
$key3 = 'bssid=';
#
#	Get the WiFi network parameters defined by key1 & key2
#
  $filedata = file_get_contents($networkfile);
  if ($filedata !== FALSE)
  {
    $position = strpos($filedata, $primekey);
    if ($position !== FALSE)
    {
      $networks = explode($primekey, $filedata);
      $n = count($networks);
      $i = 1;
      while ($i !== $n)
      {
        $output[3*($i-1)] = extractstring($networks[$i],$key1);
        $output[(3*($i-1))+1] = extractstring($networks[$i],$key2);
        $output[(3*($i-1))+2] = extractBSSid($networks[$i],$key3);
        $i = $i+1;
       }
    }
    else
    {
      $output = "no networks";
    }
  }
  else
  {
  $output = "no file";
  }
  return $output;
}

function updatenetworknames($nets)
{
$networkfile = '/etc/wpa_supplicant/wpa_supplicant.conf';
$primekey = 'network={';
$key1 = 'ssid=';
$key2 = 'psk=';
$key3 = 'bssid=';
#
#	Update the WiFi network parameters defined by key1 & key2
#
if (getnetworknames() !== $nets)
{

  $filedata = file_get_contents($networkfile);
  if ($filedata !== FALSE)
  {
    $position = strpos($filedata, $primekey);
    if ($position !== FALSE)
    {
      $networks = explode($primekey, $filedata);
#	Clear down the old array after default entry 
      $i = 3;
      $n = count($networks);
      while ($i !== $n)
      {
        unset($networks[$i]);
        $i=$i+1;
      }
#	Then rebuild the array using default entry as template
      $i = 0;
      $n = count($nets)/3;
      while ($i !== $n)
      {
        $i = $i+1;
        $networks[$i] = updatestring($networks[1],$key1,$nets[3*($i-1)]);
        $networks[$i] = updatestring($networks[$i],$key2,$nets[3*($i-1)+1]);
        $networks[$i] = updateBSSid($networks[$i],$key3,$nets[3*($i-1)+2]);
       }
       $output = implode($primekey, $networks);
	  if (file_put_contents($networkfile, $output) === FALSE)
       {
#	Write error
	  echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
          return(FALSE);
       }
    }
    else
    {
#	No network data
	return(FALSE);
    }
  }
  else
  {
#	No file
	return(FALSE);
  }
  return (TRUE);
}
else
{
return (FALSE);
}
}

function getdaapdname()
{
$daapdfile = '/etc/forked-daapd.conf';
$primekey = 'directories = {';
#
#	Get the directories for daapd server
#
   return (getmykey($daapdfile, $primekey));
}

function getspotifyuser()
{
#
#	Ger the Username of the logged on Spotify user
#
$spotifyfile = '/var/cache/forked-daapd/libspotify/settings';
$primekey = "autologin_username\":";
$secondkey = "autologin_canonical_username\":";
#
    $name = getmykey($spotifyfile, $secondkey);
    if ($name) {
	$name = getmykey($spotifyfile, $primekey);
    }
    return $name;
}

function enabledaapd($enable)
{
$daapdfile = '/etc/forked-daapd.conf';
$primekey = 'directories = {';
$alternatekey = 'Directories = {';
#
#	Enable/Disbale daapd by changing the key to the music directory
#
   $filedata = file_get_contents($daapdfile);
   if ($enable)
   {
      $output= updatekey($filedata, $alternatekey, $primekey);
   }
   else
   {
      $output= updatekey($filedata, $primekey, $alternatekey);
   }
   if (file_put_contents($daapdfile, $output) === FALSE)
   {
      echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
      return(FALSE);
   }
   return(TRUE);
}

function getdaapd_share(&$host, &$share, &$user, &$password)
{
$drivefile = '/etc/fstab';
$key1 = "//";
$key2 = "/";
$key3 = "/mnt";
$key4 = "user=\"";
$key5 = "\"";
$key6 = "password=\"";
$key7 = "\"";
$key8 = "0 0";
#
#	Extract the network drive details from /etc/fstab
#
   $host = "";
   $share = "";
   $user = "";
   $password = "";
#
   $filedata = file_get_contents($drivefile);
   if ($filedata !== FALSE){
     $a = strpos($filedata, $key1);
     if ($a !== FALSE) {
#	Having found the entry we parse the line assuming all components exist
        $a = $a + strlen($key1);
        $b = strpos($filedata, $key2, $a);
        $host =     substr($filedata, $a, $b-$a);

        $b = $b + strlen($key2);
        $c = strpos($filedata, $key3, $b);
        $share =    substr($filedata, $b, $c-$b);
        $share =    str_replace("\\040", " ", $share);

        $d = strpos($filedata, $key4, $c);
        $d = $d + strlen($key4);
        $e = strpos($filedata, $key5, $d);
        $user =     substr($filedata, $d, $e-$d);

        $f = strpos($filedata, $key6, $e);
        $f = $f + strlen($key6);
        $g = strpos($filedata, $key7, $f);
        $password = substr($filedata, $f, $g-$f);
        return(TRUE);
     } else {
        return(FALSE);
     }
   } else {
     echo "<font color='Red'>File Missing - check permissions<font color='Black'>", "<br><br>";
     return(FALSE);
   }
}

function updatedaapd_share($delete, $host, $share, $user, $password)
{
$drivefile = '/etc/fstab';
$key1 = "//";
$key2 = "/";
$key3 = "/mnt";
$key4 = "user=\"";
$key5 = "\"";
$key6 = "password=\"";
$key7 = "\"";
$key8 = "0 0";
$key9 = "#";
#
#
#	Update the network drive details from /etc/fstab
#
   $filedata = file_get_contents($drivefile);
   if ($filedata !== FALSE){

#	Contruct output line
     $line = "";
     $share =    str_replace(" ", "\\040", $share);
     if ($delete !== TRUE) {$line = sprintf("//%s/%s /mnt/network cifs user=\"%s\",password=\"%s\",rw,file_mode=0777,dir_mode=0777 0 0\n", $host, $share, $user, $password); };

#	Identify locations for replace

     $a = strpos($filedata, $key1);
     if ($a !== FALSE) {
#	Having found the entry we parse the line assuming all components exist
        $a = $a;
        $b = strpos($filedata, $key8, $a);
        $b = $b + strlen($key8) + 1;
     } else {
        $a = strpos($filedata, $key9);
        $b = $a;
     }
     $filedata = substr_replace($filedata, $line, $a, $b-$a);
     if (file_put_contents($drivefile, $filedata) === FALSE)
     {
        echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
        return(FALSE);
     }
   } else {
     echo "<font color='Red'>File Missing - check permissions<font color='Black'>", "<br><br>";
     return(FALSE);
   }
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
$net[(3*$count)]="";
$net[(3*$count)+1]="";
$net[(3*$count)+2]="";
$count=$count+1;
}
else
{
$count=$count-1;
unset($net[(3*$count)]);
unset($net[(3*$count)+1]);
unset($net[(3*$count)+2]);
}
}

function notifyspotify($directory, $user, $password)
{
#
#	Notify forked daapd of Spotify Username & Password
#
      $tmp = "/var/www/login.tmp";
      $file = $directory . "/login.spotify";
      $contents = $user . "\n" . $password;
#      echo "Notify Spotify: ", $user, " ", $password, " in ", $file, "<br>", $contents;
      file_put_contents($tmp, $contents);
      $p = rename($tmp, $file);
      if ($p === FALSE) echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
      sleep(1);
      unlink($file);
}

function enableremote($directory, $remotename, $pin1, $pin2, $pin3, $pin4)
{
#
#	Enable remote for forked daapd
#
      $tmp = "/var/www/remote.tmp";
      $file = $directory . "/remote.remote";
      $contents = $remotename . "\n" . $pin1 . $pin2 . $pin3 . $pin4;
#      echo "Enable Remote: ", $remotename, " ", $pin1, $pin2, $pin3, $pin4, " in ", $file, "<br>", $contents;
      file_put_contents($tmp, $contents);
      $p = rename($tmp, $file);
      if ($p === FALSE) echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
      sleep(1);
      unlink($file);
}

function libraryrescan($directory, $full)
{
#
#	Force a rescan of Library for forked daapd
#
      $tmp = "/var/www/rescan.tmp";
      if ($full === TRUE) {
         $file = $directory . "/rescan.full-rescan";
      }
      else {
         $file = $directory . "/rescan.init-rescan";
      }
#      echo "Library Rescan", " in ", $file, "<br><br>";
      file_put_contents($tmp, "Please rescan\n");
      $p = rename($tmp, $file);
      if ($p === FALSE) echo "<font color='Red'>Write failed - check permissions<font color='Black'>", "<br><br>";
      sleep(1);
      unlink($file);
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

