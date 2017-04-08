<?php
$starttime = microtime();
include "config-default.php";
if (file_exists ("config.php"))
{
  include "config.php";
}
?>