<?php include "init.php"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>cjly - recent sounds</title>
<link rel="stylesheet" type="text/css" href="style.css" media="screen" />
<script type="text/javascript"><!--

var starthour = -1;
var endhour = -1;
var datewas = -1;
function selectdate(x)
{
  document.getElementById('startdate').value = x;
}
function selectstarttime(x)
{
  starthour = x;
  update();
}
function selectendtime(x)
{
  endhour = x;
  update();
}
function update()
{
  if (endhour <= starthour)
    endhour = starthour;
  if (starthour >= 0)
    {
      var duration = 1 + endhour - starthour;
      var s = endhour == starthour ? '' : 's';
      var colonzero_s = document.getElementById('starttime').value.indexOf(':');
      var mmss = '';
      if (colonzero_s > 0)
	  mmss = document.getElementById('starttime').value.substr(colonzero_s);
      document.getElementById('duration').value = ''+duration+' hour'+s;
      document.getElementById('starttime').value = ''+starthour+mmss;
    }
  for (var h = 0; h < 24; h++)
    {
      var bg = '#ccc';
      if (h >= starthour && h <= endhour)
	bg = '#00f';
      document.getElementById('h_'+h).style.background = bg;
    }
}
function update_cal()
{
  if (document.getElementById(datewas))
    {
      if (document.getElementById(datewas).style.background.indexOf(', 0,') > 0)
	{
	  document.getElementById(datewas).style.background = '#fff';
	  document.getElementById(datewas).style.color = '#000';
	}
    }
  datewas = document.getElementById('startdate').value;
  if (document.getElementById(datewas))
    {
      document.getElementById(datewas).style.background = '#000';
      document.getElementById(datewas).style.color = '#fff';
    }
  starthour = -1;
  endhour = -1;
  var colonzero_s = document.getElementById('starttime').value.indexOf(':');
  var colonzero_d = document.getElementById('duration').value.indexOf(' hour');
  if (colonzero_s > 0 && colonzero_d > 0)
    {
      var h_s = document.getElementById('starttime').value.substr(0,colonzero_s);
      var h_d = document.getElementById('duration').value.substr(0,colonzero_d);
      h_s = parseInt(h_s);
      h_d = parseInt(h_d);
      if (h_s >= 0 && h_d >= 0)
	{
	  starthour = h_s;
	  endhour = h_s + h_d - 1;
	}
    }
  update();
}
function mouseover(x)
{
  for (var n=0; n<24; n++)
    {
      document.getElementById('s_'+n).style.background = '#fff';
      document.getElementById('e_'+n).style.background = '#fff';
    }
  document.getElementById(x).style.background = '#ccc';
}
function mouseout(x)
{
  document.getElementById(x).style.background = '#fff';
}

// --></script>
</head>
<body onload="update(); update_cal();">

<img align="right" width="275" height="121" src="http://cjly.net/cjly%20raver.GIF" />

<h2>recent sounds</h2>
<p>heard recently on <a href="http://cjly.org/">Kootenay Cooperative Radio - CJLY</a><br />nelson bc 93.5fm<br />crawford bay bc 96.5fm<br />new denver bc 107.5fm</p>

<?php echo $subtitle; ?>

<p>see also: <a href="http://kootenaycoopradio.com/?/schedule/">schedule</a> and <a href="http://kootenaycoopradio.com/live">listen live</a></p>

<?php

include 'init.php';
include 'inspect.php';

?>

<form action="download.php" method="GET">

<table cellpadding="0" cellspacing="10" class="cal" align="left"><tr><?php

$thismonth = date ('n');
$thisyear = date ('Y');

for ($m=1-$maxmonths; $m<1; $m++)
{
  $year = $thisyear;
  $month = $thismonth + $m;
  while ($month < 1)
    {
      $year--;
      $month = (($month + 11) % 12) + 1;
    }
  $cal = `cal -h $month $year`;
  $nothingthismonth = 1;
  for ($day=1; $day<=31; $day++)
    {
      if ($day > 28 && !preg_match ("/\\b$day\\b/", $cal))
	continue;
      $timestamp = mktime (22, 59, 0, $month, $day, $year);
      if ($timestamp < $earliest)
	continue;
      $timestamp = mktime (1, 1, 0, $month, $day, $year);
      if ($timestamp > $latest)
	continue;
      $month2 = $month > 9 ? $month : "0$month";
      $day2 = $day > 9 ? $day : "0$day";
      $cal = preg_replace("/( +|\\n)($day)( |\\n)/", "<span class=\"okdate\">\\1<a id=\"$year-$month2-$day2\" href=\"#$year-$month2-$day2\" onclick=\"javascript:selectdate('$year-$month2-$day2'); update_cal();\"><b>\\2</b></a></span>\\3", $cal);
      $nothingthismonth = 0;
    }
  if ($nothingthismonth)
    continue;
  echo "<td valign=\"top\"><pre>";
  echo $cal;
  echo "</pre></td>\n";
}

?></tr></table>

<table border="0">
<tr>
<td valign="middle" align="right">Choose a date:</td>
<td valign="middle" align="left"><input type="text" name="startdate" id="startdate" value="<?php echo strftime("%Y-%m-%d",time()-7200); ?>" onkeypress="update_cal();" onchange="update_cal();"></td>
</tr>
<tr>
<td valign="middle" align="right">Start time:</td>
<td valign="middle" align="left"><input type="text" name="starttime" id="starttime" value="<?php echo strftime("%H:00",time()-7200); ?>" onkeypress="update_cal();" onchange="update_cal();"></td>
</tr>
<tr>
<td valign="middle" align="right"></td>
<td valign="middle" align="left" style="font-size: 8pt;">Time now is <?php echo strftime("%H:%M %Z"); ?></td>
</tr>
<tr>
<td valign="middle" align="right">Duration:</td>
<td valign="middle" align="left"><input type="text" name="duration" value="1 hour" id="duration" onkeypress="update_cal();" onchange="update_cal();"></td>
</tr>
<tr>
<td valign="middle" align="right"></td>
<td valign="middle" align="left" style="font-size: 8pt;">Perhaps "90 minutes" or "1:30"</td>
</tr>
<tr>
<td valign="middle" align="right"></td>
<td valign="middle" align="left"><input type="submit" name="action" value="Download"></td>
</tr>
<tr>
<td valign="middle" align="right"></td>
<td valign="middle" align="left"><input type="submit" name="action" value="Open in music player"></td>
</tr>
<tr>
<td valign="middle" align="right"></td>
<td valign="middle" align="left"><input type="submit" name="action" value="Play in browser"></td>
</tr>
</table>

<br clear="all" />
&nbsp;<br />

<table cellspacing="0" cellpadding="0" border="0" class="timechooser">
<?php
$label = array ("start&nbsp;time", "", "end&nbsp;time");
for ($row=0; $row<3; $row++)
{
  echo "<tr>\n";
  echo "<td>".$label[$row]."</td>\n";
  for ($h=0; $h<24; $h++)
    {
      if ($row == 0)
	echo "<td id=\"s_$h\" onclick=\"javascript:selectstarttime($h);\" onmouseover=\"javascript:mouseover('s_$h');\" onmouseout=\"javascript:mouseout('s_$h');\">$h:</td>";
      else if ($row == 1)
	echo "<td id=\"h_$h\" onclick=\"javascript:selectstarttime($h);\">&nbsp;</td>";
      else
	echo "<td id=\"e_$h\" onclick=\"javascript:selectendtime($h);\" onmouseover=\"javascript:mouseover('e_$h');\" onmouseout=\"javascript:mouseout('e_$h');\">$h:</td>";
    }
  echo "</tr>\n";
}
?>
</table>
</form>

<br clear="all" />

<hr noshade />

<p><small>rustic ol' web app by tom clegg &bull; report problems to <?= $support_email ?> &bull; <?php
$starttime = explode (" ", $starttime);
$starttime = $starttime[1] + $starttime[0];
$endtime = explode (" ", microtime());
$endtime = $endtime[1] + $endtime[0];
$exectime = $endtime - $starttime;
printf ("%d ms", $exectime*1000);
?> &bull; source code <a href="http://svn.tomclegg.net/svn/kcr/trunk/recent/public_html/">available</a></small></p>

</body>
</html>
