<?php;

include 'init.php';
include 'inspect.php';

$sentheader = 0;

function sendheader()
{
  global $entire_t0;
  global $mp3kbps;
  global $entire_duration;
  global $response_start;
  global $sentheader;
  global $nicedatetime;
  if (!$sentheader)
    {
      $sentheader = 1;
      $entire_bytes = $entire_duration * $mp3kbps * 1000 / 8;
      if ($response_start > 0)
	  header ("HTTP/1.1 206 Partial content");
      header ("Content-type: audio/mpeg");
      if ($response_start > 0)
	  header ("Content-Range: bytes $response_start-".($entire_bytes-1)."/$entire_bytes");
      header ("Content-length: ".($entire_bytes-$response_start));
      header ("Accept-ranges: bytes");
      if (!$_REQUEST["noattach"] && !preg_match('/Play/', $_REQUEST["action"])) {
	  header ("Content-disposition: attachment; filename=\"$nicedatetime.mp3\"");
      }
    }
}

$starttime = explode (".", ereg_replace(':','.',$_REQUEST["starttime"]));
$startdate = explode ("-", $_REQUEST["startdate"]);
$t0 = mktime ($starttime[0]+0, $starttime[1]+0, $starttime[2]+0,
	      $startdate[1]+0, $startdate[2]+0, $startdate[0]);
$nicedatetime = strftime("%Y-%m-%d %H:%M:%S", $t0);

if (preg_match('/Open|Stream/', $_REQUEST["action"]))
{
  header ("Content-type: audio/mpegurl");
  header ("Content-disposition: attachment; filename=\"$nicedatetime.m3u\"");
  $uri = "/download.php?startdate=".urlencode($_REQUEST["startdate"])
      ."&starttime=".urlencode(ereg_replace(':','.',$_REQUEST["starttime"]))
      ."&duration=".urlencode($_REQUEST["duration"])
      ."&action=Download"
      ."&noattach=1";
  $port = ":".$_SERVER["SERVER_PORT"];
  if ($port == ":80")
      $port = "";
  echo "http://".$_SERVER["HTTP_HOST"].$port.$uri;
  exit;
}

mb_http_output("pass");

$duration = $_REQUEST["duration"];
$duration = eregi_replace ("([0-9]*):?([0-9]*) *hours?", "\\1:\\2", $duration);
$duration = eregi_replace (" *minutes?", "", $duration);
if (ereg ("(([0-9]*):)?([0-9]*)", $duration, $regs))
{
  $duration = $regs[3] + $regs[1] * 60;
  $duration = $duration * 60;

  $entire_t0 = $t0;
  $entire_duration = $duration;
  $entire_bytes = floor ($duration * $mp3kbps * 1000 / 8);
  $response_start = 0;
  if (ereg ("bytes=([0-9]+)", $_SERVER["HTTP_RANGE"], $regs))
    {
      $response_start = $regs[1];
      $t0 += ($regs[1] * 8 / $mp3kbps / 1000);
    }

  $bytestodo = $entire_bytes;
  $started = 0;

  foreach ($fileendtimes as $i => $fileendtime)
    {
      $seek = 0;
      if ($fileendtime <= $t0)
	continue;
      if (!$started)
	{
	  $started = 1;
	  $filesize = filesize ("$mp3dir/t$fileendtime.mp3");
	  $seek = $filesize - floor(($fileendtime - $t0) * $mp3kbps * 1000 / 8);
	  if ($seek < 0)
	    $seek = 0;
	  sendheader();
	}
      $bufsize = 1048576;
      $safefilepath = escapeshellarg("$mp3dir/t$fileendtime.mp3");
      $fh = popen ("/usr/local/bin/mp3cat - - < $safefilepath", "r");
      while ($seek > 0)
	{
	  $buf = fread ($fh, $seek > $bufsize ? $bufsize : $seek);
	  if ($buf === false)
	    break;
	  $seek -= strlen ($buf);
	}
      while (1)
	{
	  if ($bytestodo <= 0) break;
	  $buf = fread ($fh, $bytestodo > $bufsize ? $bufsize : $bytestodo);
	  if ($buf === FALSE) break;
	  if (strlen($buf) == 0) break;
	  echo $buf;
	  $bytestodo -= strlen ($buf);
	}
      pclose ($fh);
      if ($bytestodo <= 0)
	break;
    }
}

?>
