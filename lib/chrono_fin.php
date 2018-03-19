<?php  
$mtime = microtime();  
$mtime = explode(" ",$mtime);  
$mtime = $mtime[1] + $mtime[0];  
$endtime = $mtime;  
$totaltime = ($endtime - $starttime);  
echo '<font style="font-size:10px;">Page générée en ',number_format($totaltime,4,',',''),' s</font>';  
?>  