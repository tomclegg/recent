<?php

$earliest = 0;
$latest = 0;
$fileendtimes = array();
$dh = opendir ($mp3dir);
while ($filename = readdir ($dh))
{
  if (ereg ("t([0-9]+)\\.mp3", $filename, $regs))
    {
      if ($earliest == 0 || $earliest > $regs[1])
	$earliest = $regs[1];
      if ($latest == 0 || $latest < $regs[1])
	$latest = $regs[1];
      $fileendtimes[] = $regs[1];
    }
}
closedir ($dh);
sort ($fileendtimes, SORT_NUMERIC);

?>
